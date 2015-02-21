<?php
date_default_timezone_set('Asia/Tokyo');
require 'vendor/autoload.php';
require './config.php';

//
session_start();
if (DEBUG) {
  $app = new \Slim\Slim(array(
      'debug' => true,
      'log.writer' => new \Slim\Logger\DateTimeFileWriter(array(
                          'path' => './logs',
                          'name_format' => 'Y-m-d',
                          'message_format' => '%label% - %date% - %message%'
                      )),
      'log.enabled' => true,
      'log.level' => \Slim\Log::DEBUG,
      'view' => new \Slim\Views\Smarty()
  ));
}
else {
  $app = new \Slim\Slim(array(
      'debug' => false,
      'view' => new \Slim\Views\Smarty()
  ));
}
$app->setName(APP_NAME);

$lang = $app->request->params('lang');
if(!$lang) {
    $lang = 'ja';
}

// http://wsf.mot.or.jp/yujakudo/website-admin/use-shared-ssl-of-sakura/
if( isset($_SERVER['HTTP_X_SAKURA_FORWARDED_FOR']) ) {
    $_SERVER['HTTPS'] = 'on';
    $_ENV['HTTPS'] = 'on';
}

// for Smarty.
$view = $app->view();
$view->setTemplatesDirectory(dirname(__FILE__) . '/templates');
$view->parserCompileDirectory = dirname(__FILE__) . '/compiled';
$view->parserCacheDirectory = dirname(__FILE__) . '/cache';

// Database
$existDb = file_exists(DB_PATH);
ORM::configure('sqlite:' . DB_PATH);
$db = ORM::get_db();
// SQLite Likeの対応

$models = array(
    'MsTranslatorCacheModel' => new \Model\MsTranslatorCacheModel($app, $db),
    'KeyValueModel' => new \Model\KeyValueModel($app, $db),
    'TranslationLogModel' => new \Model\TranslationLogModel($app, $db),
    'ApiCacheModel' => new \Model\ApiCacheModel($app, $db)
);

if (!$existDb) {
    $models['MsTranslatorCacheModel']->setup();
    $models['KeyValueModel']->setup();
    $models['TranslationLogModel']->setup();
    $models['ApiCacheModel']->setup();
}

$jsonCtrl = new \MyLib\JsonCtrl(DATA_DIR);
$trans = new \MyLib\MsTranslator(MS_AZURE_KEY, $models['MsTranslatorCacheModel'], $lang);
$responsTrans = new \MyLib\ResponseTranslator($trans);
$teapotCtrl = new \MyLib\TeapotCtrl(TEAPOT_SPARQL_ENDPOINT, TEAPOT_PLACE_ENDPOINT);
$kokudoCtrl = new \MyLib\KokudoCtrl(KOKUDO_ENDPOINT);

if (DEBUG) {
    if ( isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on' ) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }
} else {
    $protocol = 'https://';
}

$config = array(
    'blackListAccount' => $GLOBALS[blackListAccount],
    'PYTHON_PATH' => PYTHON_PATH,
    'PHP_PATH' => PHP_PATH
);

$self_url = $protocol.$_SERVER['HTTP_HOST'] . '/' . $app->getName();
$modules = array(
    'MsTranslator' => $trans,
    'JsonCtrl' => $jsonCtrl,
    'TwitterCtrl' => new \MyLib\TwitterCtrl(TW_CONSUMER_KEY, TW_CONSUMER_SECRET,$self_url . '/twitter_login_callback' ),
    'Config' => $config,
    'ResponseTranslator' => $responsTrans,
    'TeapotCtrl' => $teapotCtrl,
    'KokudoCtrl' => $kokudoCtrl
);


function checkBlackListUser($user) {
    $list = $GLOBALS[blackListAccount];
    if (in_array($user, $list)) {
        return true;
    }
    return false;
}

function redirectUrl($path) {
    $app = \Slim\Slim::getInstance();
    $app->redirect($path);
}

// route middleware for simple API authentication
$authenticateTwitter = function ( $twCtrl, $redirect, $lang ) {
    return function () use ( $twCtrl, $lang) {
        $sts = $twCtrl->getStatus();
        if( $sts !== \MyLib\TwitterCtrl::STATUS_AUTHORIZED ) {
            $authurl = $twCtrl->getAuthorizeUrl();
            $app = \Slim\Slim::getInstance();
            $app->flash('error', 'Login required');
            if($redirect) {
                redirectUrl('/' . $app->getName() . '/login?lang=' . $lang);
            } else {
                $app->halt(401,'Unauthorized');
            }
            return;
        } else {
            if (checkBlackListUser($_SESSION['twitter_user'])) {
               $app->halt(401, 'permission error:' . $_SESSION['twitter_user']);
            }
        }
    };
};

$langInfo = $jsonCtrl->getTranslationInfo();
if (!$langInfo[$lang]) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Not support language.';
    exit();
}

//////////////////////////////////////////////////////////////////////////
// 以下JSON取得用のController
//////////////////////////////////////////////////////////////////////////
/**
 * 避難所情報の取得
 */
$app->get('/json/get_shelter', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\GetShelterController($app, $modules, $models);
    $ctrl->route();
});


/**
 * 避難所の建物情報の取得
 */
$app->get('/json/get_shelter_building', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\GetShelterBuildingController($app, $modules, $models);
    $ctrl->route();
});

/**
 * 病院情報の取得
 */
$app->get('/json/get_hospital', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\GetHospitalController($app, $modules, $models);
    $ctrl->route();
});


/**
 * 浸水想定区域の情報を取得する
 */
$app->get('/json/get_expected_flood_area', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\GetExpectedFloodArea($app, $modules, $models);
    $ctrl->route();
});


/**
 * 土砂災害危険場所データ
 */
$app->get('/json/get_sediment_disaster_hazard_area', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\GetSedimentDisasterHazardArea($app, $modules, $models);
    $ctrl->route();
});


/**
 * 翻訳情報を検索して取得する
 */
$app->get('/json/get_translation', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\GetTranslationController($app, $modules, $models);
    $ctrl->route();
});


/**
 * 翻訳を変更
 */
$app->post('/json/set_translation', 
           $authenticateTwitter($modules['TwitterCtrl'], false, $lang), 
           function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\SetTranslationController($app, $modules, $models);
    $ctrl->route();
});

$app->get('/json/get_translation_log', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Json\GetTranslationLogController($app, $modules, $models);
    $ctrl->route();
});




//////////////////////////////////////////////////////////////////////////
// 以下ページ用のコントローラ
//////////////////////////////////////////////////////////////////////////
/**
 * 翻訳修正画面修正
 */
$app->get('/page/translation',  function() use ($app, $modules, $models, $lang) {
    $twCtrl = $modules['TwitterCtrl'];
    $sts = $twCtrl->getStatus();
    
    if( $sts == \MyLib\TwitterCtrl::STATUS_AUTHORIZED ) {
        // 認証済み
        if (checkBlackListUser($_SESSION['twitter_user'])) {
           $app->halt(401,'permission error:' . $_SESSION['twitter_user']);
        }

        $ctrl = new \Controller\Page\TranslationController($app, $modules, $models);
        $ctrl->route();
    } else {
        $_SESSION['callback_url'] = $_SERVER['REQUEST_URI'];
        redirectUrl('/' . $app->getName() . '/login?lang=' . $lang );
    }
});

$app->get('/page/translation_log', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Page\TranslationLogController($app, $modules, $models);
    $ctrl->route();
});


$app->get('/', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Page\StartController($app, $modules, $models);
    $ctrl->route();
});

$app->get('/logout', function() use ($app, $modules, $models, $lang) {
    session_destroy();
    session_unset();
    redirectUrl('/' . $app->getName() . '?lang=' . $lang);
});

$app->get('/login', function() use ($app, $modules, $models, $lang) {
    $twCtrl = $modules['TwitterCtrl'];
    $sts = $twCtrl->getStatus();
    if( $sts == \MyLib\TwitterCtrl::STATUS_AUTHORIZED ) {
        if (checkBlackListUser($_SESSION['twitter_user'])) {
           $app->halt(401,'permission error:' . $_SESSION['twitter_user']);
        }

        redirectUrl('/'. $app->getName() .'/page/translation?lang=' . $lang );
    } else {
        $callback = $_SESSION['callback_url'];
        session_regenerate_id(true);
        $_SESSION['callback_url'] = $callback;

        $authurl = $twCtrl->getAuthorizeUrl();
        if ($authurl) {
            redirectUrl($authurl);
        } else {
            $app->halt(500, "Not found twitter authorize url.");
        }
    }
});

$app->get('/twitter_login_callback', function() use ($app, $modules, $models, $lang) {
    $twCtrl = $modules['TwitterCtrl'];
    $sts = $twCtrl->getStatus();
    
    if( $sts == \MyLib\TwitterCtrl::STATUS_REQUEST_ACCESS_TOKEN ) {
        if( $twCtrl->requesAccessToken() ) {
            $rep=$twCtrl->requestVerify();
            if ($rep != null)
            {
                $_SESSION['twitter_user'] = $rep->screen_name;
            } else {
                $twCtrl->reset();
            }
        }
        $sts = $twCtrl->getStatus();
    }

    if( $sts == \MyLib\TwitterCtrl::STATUS_AUTHORIZED ) {
        // 認証済み
        if (checkBlackListUser($_SESSION['twitter_user'])) {
           $app->halt(401,'permission error:' . $_SESSION['twitter_user']);
        }
        if (isset($_SESSION['callback_url'])) {
          redirectUrl($_SESSION['callback_url']);
        }
    }

    redirectUrl('/' . $app->getName() . '/' );
});


/**
 * 病院マップ
 */
$app->get('/page/hospital_map', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Page\HospitalMapController($app, $modules, $models);
    $ctrl->route();
});


/**
 * 災害マップ
 */
$app->get('/page/disaster_map', function() use ($app, $modules, $models, $lang) {
    $ctrl = new \Controller\Page\DisasterMapController($app, $modules, $models);
    $ctrl->route();
});


//////////////////////////////////////////////////////////////////////////
//
//////////////////////////////////////////////////////////////////////////
$app->hook('slim.after', function () use ($app, $modules, $models, $lang) {
    // 最後にキャッシュを保存しとく.
    $modules['MsTranslator']->updateCacheDb();
});

$app->run();


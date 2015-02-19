<?php
require dirname(__FILE__) . '/../vendor/autoload.php';
require dirname(__FILE__) . '/../config.php';
date_default_timezone_set('Asia/Tokyo');
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
$app->setName(APP_NAME);

$view = $app->view();
$view->setTemplatesDirectory(dirname(__FILE__) . '/../templates');
$view->parserCompileDirectory = dirname(__FILE__) . '/../compiled';
$view->parserCacheDirectory = dirname(__FILE__) . '/../cache';


function updateTran($app, $models,$jsonCtrl ,$lang) {
  echo $lang . "......\n";
  $trans = new \MyLib\MsTranslator(MS_AZURE_KEY, $models['MsTranslatorCacheModel'], $lang);
  $responsTrans = new \MyLib\ResponseTranslator($trans);
  $teapotCtrl = new \MyLib\TeapotCtrl('http://teapot-api.bodic.org/api/v1/sparql', 'http://teapot-api.bodic.org/api/v1/places');

  $modules = array(
    'MsTranslator' => $trans,
    'JsonCtrl' => $jsonCtrl,
    'TwitterCtrl' => new \MyLib\TwitterCtrl(TW_CONSUMER_KEY, TW_CONSUMER_SECRET,$self_url . '/twitter_login_callback' ),
    'ResponseTranslator' => $responsTrans,
    'TeapotCtrl' => $teapotCtrl
  );

  $ctrl = new \Controller\Json\GetShelterController($app, $modules, $models);
  $ctrl->route();
  $trans->updateCacheDb();

  echo $lang . "GetHospitalController......\n";
  $param = [
    "こう門科",
    "アレルギー科",
    "リウマチ科",
    "リハビリテーション科",
    "内科",
    "呼吸器外科",
    "呼吸器科",
    "外科",
    "婦人科",
    "小児外科",
    "小児歯科",
    "小児科",
    "形成外科",
    "循環器科",
    "心療内科",
    "心臓血管外科",
    "性病科",
    "放射線科",
    "整形外科",
    "歯科",
    "歯科口腔外科",
    "気管食道科",
    "泌尿器科",
    "消化器科",
    "産婦人科",
    "産科",
    "皮膚泌尿器科",
    "皮膚科",
    "眼科",
    "矯正歯科",
    "神経内科",
    "神経科",
    "精神科",
    "美容外科",
    "耳鼻咽喉科",
    "胃腸科",
    "脳神経外科",
    "麻酔科",
  ];
  $ctrl = new \Controller\Json\GetHospitalController($app, $modules, $models);
  $ctrl->getHospital($param);

  $trans->updateCacheDb();
}

// Database
$existDb = file_exists(DB_PATH);
ORM::configure('sqlite:' . DB_PATH);
$db = ORM::get_db();

$models = array(
    'MsTranslatorCacheModel' => new \Model\MsTranslatorCacheModel($app, $db),
    'KeyValueModel' => new \Model\KeyValueModel($app, $db),
    'TranslationLogModel' => new \Model\TranslationLogModel($app, $db),
    'ApiCacheModel' => new \Model\ApiCacheModel($app, $db)
);

$models['MsTranslatorCacheModel']->setup();
$models['KeyValueModel']->setup();
$models['TranslationLogModel']->setup();
$models['ApiCacheModel']->setup();

$jsonCtrl = new \MyLib\JsonCtrl(DATA_DIR);




$lang = $argv[1] ;
try {
  $models['ApiCacheModel']->clearContents();
  if($lang) {
    updateTran($app, $models,$jsonCtrl ,$lang);
  } else {
    $models['KeyValueModel']->set('UPDATE_LANG_CACHE', time());
    $tranInfo = $jsonCtrl->getTranslationInfo();
    foreach ($tranInfo as $key => $item) {
      updateTran($app, $models, $jsonCtrl ,$key);
    }
    $models['KeyValueModel']->set('UPDATE_LANG_CACHE_END', time()); 
  }
  $models['ApiCacheModel']->vucuum();
} catch (Exception $e) {
    echo '例外: ',  $e->getMessage(), "\n";
} 


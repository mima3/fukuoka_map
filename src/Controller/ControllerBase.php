<?php
namespace Controller;

abstract class ControllerBase
{
    protected $app;
    protected $modules;
    protected $models;
    public function __construct($app, $modules, $models)
    {
        $this->app = $app;
        $this->modules = $modules;
        $this->models = $models;
        $this->lang = $this->app->request->params('lang');
        if (!$this->lang) {
            $this->lang = 'ja';
        }
    }

    abstract public function route();

    protected function sendJsonData($retcode, $errormsg, $contents)
    {
        $this->app->contentType('Content-Type: application/json;charset=utf-8');
        $data = array(
            'resultCode' => $retcode,
            'errorMsg' => $errormsg,
            'contents' => $contents
        );
        print_r(json_encode($data));
    }

    protected function removeSymbol($value)
    {
        return preg_replace('/[][}{)(!"#$%&\'~|\*+,\/@.\^<>`;:?_=\\\\-]/i', '', $value);
    }

    protected static function getName($path)
    {
        if ($path) {
            return substr($path, strrpos($path, '.')+1);
        } else {
            return $path;
        }
    }

    /**
     * Headerのレンダリング用のデータ取得
     */
    protected function getHeaderTempalteData()
    {
        $tran = $this->modules['MsTranslator'];
        $headLabel = array(
          'selectLang' =>  $tran->translator('表示する言語を選択してください。'),
          'contact' =>  $tran->translator('連絡先'),
          'translation' =>  $tran->translator(
              'Twitterのアカウントでログインをして、テキストの修正を行います。'
          ),
          'start' =>  $tran->translator('スタートページ'),
          'disaster_map' => $tran->translator('災害マップ'),
          'hospital_map' =>  $tran->translator('病院マップ'),
          'translation_log' =>  $tran->translator('テキスト変更履歴'),
          'login' =>  $tran->translator('ログイン'),
          'logout' =>  $tran->translator('ログアウト')
        );
        $transInfo = $this->modules['JsonCtrl']->getTranslationInfo();
        $ret = array(
          'langList' => $transInfo,
          'lang' => $this->lang,
          'rtl' => $transInfo[$this->lang]->rtl,
          'user' => $_SESSION['twitter_user'],
          'headLabel' => $headLabel
        );
        return $ret;
    }

    /**
     * 任意のページへのリンクを作成する
     * この関数は自動でlangパラメータを付与する
     * @param string path "/' . $this->app->getName() . '/"以下のパスを指定する。
     */
    protected function createPageUrl($path, $query)
    {
        $url = '/' . $this->app->getName() . '/page/' . $path;
        if ($query) {
            $url = $url . '?' . $query . '&lang=' . $this->lang;
        } else {
            $url = $url . '?lang=' . $this->lang;
        }

        return $url;
    }

    /**
     * モバイルかどうかのチェック
     */
    protected function isMobile()
    {
        $isMobile = $this->app->request->params('mobile');
        if (!$isMobile) {
            $delect = new \Mobile_Detect;
            $isMobile = $delect->isMobile();
        }
        return $isMobile;
    }
}

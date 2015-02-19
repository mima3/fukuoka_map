<?php
namespace Controller\Page;

/**
 * スタートページ
 * アプリケーションと、各機能の解説。
 */
class StartController extends \Controller\ControllerBase
{
    public function route()
    {
        $tran = $this->modules['MsTranslator'];
        $tempData = array(
            'appName' => $this->app->getName(),
            'label'=>$label
        );
        $tempData += $this->getHeaderTempalteData();
        $this->app->render('start.tpl', $tempData);
    }
}

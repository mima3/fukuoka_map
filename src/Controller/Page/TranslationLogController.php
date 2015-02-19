<?php
namespace Controller\Page;

/**
 * 翻訳ログを確認するページ
 */
class TranslationLogController extends \Controller\ControllerBase
{
    public function route()
    {
        $tempData = array(
            'appName' => $this->app->getName()
        );

        $tempData += $this->getHeaderTempalteData();
        $this->app->render('translation_log.tpl', $tempData);
    }
}

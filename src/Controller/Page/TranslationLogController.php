<?php
namespace Controller\Page;

/**
 * �|�󃍃O���m�F����y�[�W
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

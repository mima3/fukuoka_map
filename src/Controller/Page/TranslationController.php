<?php
namespace Controller\Page;

/**
 * �|����C������y�[�W
 */
class TranslationController extends \Controller\ControllerBase
{
    public function route()
    {
        $tempData = array(
            'appName' => $this->app->getName()
        );

        $tempData += $this->getHeaderTempalteData();
        $this->app->render('translation.tpl', $tempData);
    }
}

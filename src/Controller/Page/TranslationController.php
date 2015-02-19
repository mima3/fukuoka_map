<?php
namespace Controller\Page;

/**
 * –|–ó‚ðC³‚·‚éƒy[ƒW
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

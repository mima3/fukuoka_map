<?php
namespace Controller\Page;

/**
 * 病院の検索
 */
class TeapotSparqlController extends \Controller\ControllerBase
{
    public function route()
    {
        $label = array();
        $tempData = array(
            'appName' => $this->app->getName(),
            'label'=>$label
        );
        $tempData += $this->getHeaderTempalteData();
        $this->app->render('teapot_sparql.tpl', $tempData);
    }
}

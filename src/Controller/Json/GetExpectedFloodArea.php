<?php
namespace Controller\Json;

/**
 * 浸水想定区域を取得する
 */
class GetExpectedFloodArea extends \Controller\ControllerBase
{
    public function route()
    {
        $swlat = 33.42294614050342 ;  //$this->app->request->params('swlat');
        $swlng = 130.02156319824212; //$this->app->request->params('swlng');
        $nelat = 33.88989773419436 ;  //$this->app->request->params('nelat');
        $nelng = 130.57087960449212; //$this->app->request->params('nelng');
        
        $db = $this->models['ApiCacheModel'];
        $key = 'GetExpectedFloodArea_Fukuoka_' . $swlat .'_'.$swlng .'_'.$nelat .'_'.$nelng;
        $ret = $db->getContents($key);
        if (!$ret) {
            $kokudoCtrl = $this->modules['KokudoCtrl'];
            $ret = $kokudoCtrl->getExpectedFloodArea($swlat, $swlng, $nelat, $nelng);
            if ($ret['resultCode'] == \MyLib\TeapotCtrl::RESULT_CODE_OK) {
                $ret += array('updated'=>time());
                $db->setContents($key, $ret);
            }
        }
        $this->sendJsonData($ret['resultCode'], $ret['errorMsg'], $ret['contents']);
        return;
    }
}

<?php
namespace Controller\Json;

/**
 * 浸水想定区域を取得する
 */
class GetExpectedFloodArea extends \Controller\ControllerBase
{
    public function route()
    {
        $swlat = $this->app->request->params('swlat');
        $swlng = $this->app->request->params('swlng');
        $nelat = $this->app->request->params('nelat');
        $nelng = $this->app->request->params('nelng');
        if (abs($swlat - $nelat) >= 0.5 || abs($swlng - $nelng) >= 0.5) {
            $this->sendJsonData(3, 'out of range.', null);
            return;
        }
        
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

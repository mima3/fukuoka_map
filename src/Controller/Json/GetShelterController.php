<?php
namespace Controller\Json;

/**
 * 避難所の一覧を取得する
 */
class GetShelterController extends \Controller\ControllerBase
{
    public function route()
    {
        $db = $this->models['ApiCacheModel'];
        $key = 'GetShelter_Fukuoka';
        $ret = $db->getContents($key);
        if (!$ret) {
            $query = new \MyLib\TeapotQuery($this->modules['TeapotCtrl']);
            $query->columns(array('?s', '?p', '?o'))
                ->distinct()
                ->where('?s', '<http://teapot.bodic.org/predicate/避難所情報>', '?x')
                ->where('?s', '?p', '?o')
                ->filter('!isBlank(?o)')
                ->union()
                    ->where('?s', '<http://teapot.bodic.org/predicate/避難所情報>', '?x')
                    ->where('?s', '?p', '?y')
                    ->where('?y', '?p', '?o')
                    ->filter('isBlank(?y)')
                ->orderby('?s ?p');
            $ret = $query->execute();
            if ($ret['resultCode'] == \MyLib\TeapotCtrl::RESULT_CODE_OK) {
                $ret += array('updated'=>time());
                $db->setContents($key, $ret);
            }
        }
        
        if ($ret['resultCode'] == \MyLib\TeapotCtrl::RESULT_CODE_OK) {
            $resTrans = $this->modules['ResponseTranslator'];
            $ret = $resTrans->translateTeapotSparql($ret);
        }

        $this->sendJsonData($ret['resultCode'], $ret['errorMsg'], $ret['contents']);
        return;
    }
}

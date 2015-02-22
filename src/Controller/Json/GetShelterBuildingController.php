<?php
namespace Controller\Json;

/**
 * 避難所の建物一覧を取得する
 */
class GetShelterBuildingController extends \Controller\ControllerBase
{
    public function route()
    {
        $shelter_type = array();
        if ($this->app->request->params('shelter_type')) {
            $shelter_type = $this->app->request->params('shelter_type');
        }
        $ret = $this->getShelterBuilding($shelter_type);
        $this->sendJsonData($ret['resultCode'], $ret['errorMsg'], $ret['contents']);
        return;
    }

    public function getShelterBuilding($shelter_type)
    {
        $db = $this->models['ApiCacheModel'];
        $key = 'GetShelterBuilding_Fukuoka';
        $ret = $db->getContents($key);
        if (!$ret) {
            $shlFilter = '';
            $i = 0;
            foreach ($shelter_type as $s) {
                if ($i != 0) {
                    $shlFilter .= ' || ';
                }
                $shlFilter .= ' ?x = "' .$s . '"';
                ++$i;
            }

            $query = new \MyLib\TeapotQuery($this->modules['TeapotCtrl']);
            $query->columns(array('?facility', '?building', '?p', '?o'))
                ->distinct()
                ->where('?facility', '<http://teapot.bodic.org/predicate/避難所情報>', '?a')
                ->where('?building', '<http://teapot.bodic.org/predicate/containedIn>', '?facility')
                ->where('?building', '<http://teapot.bodic.org/predicate/種別>', '?c')
                ->where('?facility', '<http://teapot.bodic.org/predicate/避難所情報>', '?x')
                ->where('?building', '?p', '?o')
                ->filter($shlFilter)
                ->filter('?c ="建物"')
                ->filter('
                   ?p = <http://www.w3.org/2000/01/rdf-schema#label> ||
                   ?p = <http://teapot.bodic.org/predicate/構造> ||
                   ?p = <http://teapot.bodic.org/predicate/地上階数> ||
                   ?p = <http://teapot.bodic.org/predicate/地下階数> ||
                   ?p = <http://teapot.bodic.org/predicate/延床面積>
                ')
                ->orderby('?facility ?building');
            $ret = $query->executeSpilit(5000);
            if ($ret['resultCode'] == \MyLib\TeapotCtrl::RESULT_CODE_OK) {
                $ret += array('updated'=>time());
                $db->setContents($key, $ret);
            }
        }
        
        if ($ret['resultCode'] == \MyLib\TeapotCtrl::RESULT_CODE_OK) {
            $resTrans = $this->modules['ResponseTranslator'];
            $ret = $resTrans->translateTeapotSparql($ret);
        }
        return $ret;
    }
}

<?php
namespace Controller\Json;

/**
 * 避難所の一覧を取得する
 */
class GetShelterController extends \Controller\ControllerBase
{
    public function route()
    {
        $shelter_type = array();
        if ($this->app->request->params('shelter_type')) {
            $shelter_type = $this->app->request->params('shelter_type');
        }
        $ret = $this->getShelter($shelter_type);
        $this->sendJsonData($ret['resultCode'], $ret['errorMsg'], $ret['contents']);
        return;
    }

    public function getShelter($shelter_type)
    {
        $db = $this->models['ApiCacheModel'];
        $key = 'GetShelter_Fukuoka' . implode('_', $shelter_type);
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
            $query->columns(array('?s', '?p', '?o'))
                ->distinct()
                ->where('?s', '<http://teapot.bodic.org/predicate/避難所情報>', '?x')
                ->where('?s', '?p', '?o')
                ->filter('!isBlank(?o)')
                ->filter($shlFilter)
                ->filter('
                   ?p = <http://www.w3.org/2000/01/rdf-schema#label> ||
                   ?p = <http://teapot.bodic.org/predicate/種別> ||
                   ?p = <http://teapot.bodic.org/predicate/避難所情報> ||
                   ?p = <http://teapot.bodic.org/predicate/緯度> ||
                   ?p = <http://teapot.bodic.org/predicate/経度> ||
                   ?p = <http://teapot.bodic.org/predicate/種別> ||
                   ?p = <http://teapot.bodic.org/predicate/郵便番号> ||
                   ?p = <http://teapot.bodic.org/predicate/addressClean>')
                ->orderby('?s ?p');
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

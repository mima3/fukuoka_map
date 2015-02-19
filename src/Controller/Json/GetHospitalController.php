<?php
namespace Controller\Json;

/**
 * 病院を取得する
 */
class GetHospitalController extends \Controller\ControllerBase
{
    public function route()
    {
        $medical_subjects = [];
        if ($this->app->request->params('medical_subjects')) {
            $medical_subjects = $this->app->request->params('medical_subjects');
        }

        $ret = $this->getHospital($medical_subjects);

        $this->sendJsonData($ret['resultCode'], $ret['errorMsg'], $ret['contents']);
        return;
    }

    public function getHospital($medical_subjects) {
        $db = $this->models['ApiCacheModel'];
        $key = 'GetHospitalController_Fukuoka_' . implode('_', $medical_subjects);
        $ret = $db->getContents($key);
        if (!$ret) {
            $ret = array(
                'resultCode' => 0,
                'errorMsg' => null,
                'contents' => null
            );
            $subjFilter = '';
            $i = 0;
            foreach($medical_subjects as $subj) {
                if ($i != 0) {
                    $subjFilter .= ' || ';
                }
                $subjFilter .= ' ?x = "' .$subj . '"';
                ++$i;
            }
            $query = new \MyLib\TeapotQuery($this->modules['TeapotCtrl']);
            $query->columns(['?s', '?p', '?o'])
                ->distinct()
                ->where('?s', '<http://teapot.bodic.org/predicate/診療科目>', '?x')
                ->where('?s', '?p', '?o')
                ->filter($subjFilter)
                ->filter('!isBlank(?o)')
                ->filter('
                   ?p = <http://www.w3.org/2000/01/rdf-schema#label> ||
                   ?p = <http://teapot.bodic.org/predicate/診療科目> ||
                   ?p = <http://teapot.bodic.org/predicate/電話番号> ||
                   ?p = <http://teapot.bodic.org/predicate/緯度> ||
                   ?p = <http://teapot.bodic.org/predicate/経度> ||
                   ?p = <http://teapot.bodic.org/predicate/種別> ||
                   ?p = <http://teapot.bodic.org/predicate/郵便番号> ||
                   ?p = <http://teapot.bodic.org/predicate/病床数合計> ||
                   ?p = <http://teapot.bodic.org/predicate/addressClean>')
                ->orderby('?s ?p ?o');
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

        return $ret;
    }
}

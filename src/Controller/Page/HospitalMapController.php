<?php
namespace Controller\Page;

/**
 * 病院の検索
 */
class HospitalMapController extends \Controller\ControllerBase
{
    public function route()
    {
        $tran = $this->modules['MsTranslator'];
        $db = $this->models['ApiCacheModel'];

        // 診療科目
        $key = 'MedicalSubject_Fukuoka';
        $ret = $db->getContents($key);
        if (!$ret) {
            $query = new \MyLib\TeapotQuery($this->modules['TeapotCtrl']);
            $query->columns(array('?o'))
                ->distinct()
                ->where('?s', '<http://teapot.bodic.org/predicate/診療科目>', '?o')
                ->orderby('?o');
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
        $medicalSubjects = array();
        $bindings = $ret['contents']->results->bindings;
        foreach ($bindings as $b) {
            $medicalSubjects += array($b->o->value => $b->o->translate_value);
        }

        $lang = $this->app->request->params('lang');
        $langInfo = $this->modules['JsonCtrl']->getTranslationInfo();
        $gmaplang ='ja';
        if ($lang) {
            if ($langInfo[$lang]) {
                $gmaplang = $langInfo[$lang]->gmapCode;
            }
        }

        $label = array(
            'title' => $tran->translator('病院マップ'),
            'curpos' => $tran->translator('現在地'),
            'medical_subject' => $tran->translator('診療科目'),
            'search' => $tran->translator('検索'),
            'start_pos' => $tran->translator('開始位置'),
            'center' => $tran->translator('画面中央'),
            'name' => $tran->translator('名称'),
            'kind' => $tran->translator('種別'),
            'postcode' => $tran->translator('郵便番号'),
            'name' => $tran->translator('名称'),
            'address' => $tran->translator('住所'),
            'phoneno' => $tran->translator('電話番号'),
            'bedcount' => $tran->translator('病床数合計'),
            'distance' => $tran->translator('距離'),
            'route_search' => $tran->translator('ルート検索'),
            'data_ref' => $tran->translator('このページは下記のデータから作成されています。'),
            'teapot' => $tran->translator('公共施設等情報のオープンデータ実証 開発者サイト')
        );
        $tempData = array(
            'appName' => $this->app->getName(),
            'medicalSubjects' => $medicalSubjects,
            'gmaplang'=>$gmaplang,
            'label'=>$label
        );
        $tempData += $this->getHeaderTempalteData();
        $this->app->render('hospital_map.tpl', $tempData);
    }
}

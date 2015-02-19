<?php
namespace Controller\Page;

/**
 * 病院の検索
 */
class DisasterMapController extends \Controller\ControllerBase
{
    public function route()
    {
        $tran = $this->modules['MsTranslator'];

        $lang = $this->app->request->params('lang');
        $langInfo = $this->modules['JsonCtrl']->getTranslationInfo();
        $gmaplang ='ja';
        if ($lang) {
            if ($langInfo[$lang]) {
                $gmaplang = $langInfo[$lang]->gmapCode;
            }
        }

        $label = array(
            'title' => $tran->translator('災害マップ'),
            'curpos' => $tran->translator('現在地'),
            'no_data' => $tran->translator('非表示'),
            'disaster_data' => $tran->translator('災害情報'),
            'flood_data' => $tran->translator('浸水想定区域のデータ'),
            'gust_data' => $tran->translator('竜巻等の突風データ'),
            'sediment_data' => $tran->translator('土砂災害危険箇所データ'),
            'search' => $tran->translator('検索'),
            'center' => $tran->translator('開始位置を画面中央へ'),
            'route_search' => $tran->translator('ルート検索')
        );
        $tempData = array(
            'appName' => $this->app->getName(),
            'gmaplang'=>$gmaplang, 
            'label'=>$label
        );
        $tempData += $this->getHeaderTempalteData();
        $this->app->render('disaster_map.tpl', $tempData);
    }
}

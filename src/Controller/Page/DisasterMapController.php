<?php
namespace Controller\Page;

/**
 * 避難所の検索画面
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

        $javaScriptMsg = array(
          'waterDepth21'=>$tran->translator('0～0.5ｍ未満'),
          'waterDepth22'=>$tran->translator('0.5～1.0ｍ未満'),
          'waterDepth23'=>$tran->translator('1.0～2.0ｍ未満'),
          'waterDepth24'=>$tran->translator('2.0～3.0ｍ未満'),
          'waterDepth25'=>$tran->translator('3.0～4.0ｍ未満'),
          'waterDepth26'=>$tran->translator('4.0～5.0ｍ未満'),
          'waterDepth27'=>$tran->translator('5.0ｍ以上'),

          'sedimentType1'=>$tran->translator('土石流危険渓流'),
          'sedimentType2'=>$tran->translator('土石流危険区域'),
          'sedimentType5'=>$tran->translator('急傾斜地崩壊危険箇所'),
          'sedimentType6'=>$tran->translator('急傾斜地崩壊危険区域'),
          'sedimentType7'=>$tran->translator('地すべり危険箇所'),
          'sedimentType8'=>$tran->translator('地すべり危険区域'),
          'sedimentType9'=>$tran->translator('地すべり氾濫区域'),
          'sedimentType10'=>$tran->translator('地すべり堪水域'),
          'sedimentType11'=>$tran->translator('雪崩危険箇所')
        );

        $shelterType = array(
          '収容避難所' => array('title'=>$tran->translator('収容避難所'), image=>'/' . $this->app->getName() . '/img/shelter_home.png'),
          '一時避難所' => array('title'=>$tran->translator('一時避難所'), image=>'/' . $this->app->getName() . '/img/shelter_temp.png'),
          '地区避難場所' => array('title'=>$tran->translator('地区避難場所'), image=>'/' . $this->app->getName() . '/img/shelter_area.png'),
          '広域避難場所' => array('title'=>$tran->translator('広域避難場所'), image=>'/' . $this->app->getName() . '/img/shelter_area.png')
        );
        $label = array(
            'title' => $tran->translator('災害マップ'),
            'building_name' => $tran->translator('建物名称'),
            'structure' => $tran->translator('構造'),
            'ground_floor' => $tran->translator('地上階数'),
            'basement_floor' => $tran->translator('地下階数'),
            'floor_area' => $tran->translator('延床面積'),
            'shelter_type' => $tran->translator('避難所種類'),
            'curpos' => $tran->translator('現在地'),
            'no_data' => $tran->translator('非表示'),
            'disaster_data' => $tran->translator('災害情報'),
            'flood_data' => $tran->translator('浸水想定区域のデータ'),
            'gust_data' => $tran->translator('竜巻等の突風データ'),
            'sediment_data' => $tran->translator('土砂災害危険箇所データ'),
            'search' => $tran->translator('検索'),
            'start_pos' => $tran->translator('開始位置'),
            'center' => $tran->translator('画面中央'),
            'route_search' => $tran->translator('ルート検索'),
            'data_ref' => $tran->translator('このページは下記のデータから作成されています。'),
            'kokudosuji' => $tran->translator('国土数値情報'),
            'teapot' => $tran->translator('共施設等情報のオープンデータ実証 開発者サイト')
        );
        $tempData = array(
            'appName' => $this->app->getName(),
            'gmaplang'=>$gmaplang, 
            'shelterType' => $shelterType,
            'javaScriptMsg' => $javaScriptMsg,
            'label'=>$label
        );
        $tempData += $this->getHeaderTempalteData();
        $this->app->render('disaster_map.tpl', $tempData);
    }
}

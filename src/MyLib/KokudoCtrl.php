<?php
namespace MyLib;

/**
 * 国土数値情報を取得するAPIを実行する
 */
class KokudoCtrl extends \MyLib\ApiCtrlBase
{
    private $endPoint;

    /**
     * コンストラクタ
     * @param string  $endPoint  実行用のエンドポイント
     */
    public function __construct($endPoint)
    {
        $this->endPoint = $endPoint;
        parent::__construct();
    }

    /**
     * 浸水想定区域のデータの取得
     */
    public function getExpectedFloodArea($swlat, $swlng, $nelat, $nelng)
    {
        $param = array(
            'swlat' => $swlat,
            'swlng' => $swlng,
            'nelat' => $nelat,
            'nelng' => $nelng
        );
        return parent::get($this->endPoint . '/json/get_expected_flood_area_by_geometry', $param, 0);
    }

    /**
     * 土砂災害危険箇所データの取得
     */
    public function getSedimentDisasterHazardArea($swlat, $swlng, $nelat, $nelng)
    {
        $param = array(
            'swlat' => $swlat,
            'swlng' => $swlng,
            'nelat' => $nelat,
            'nelng' => $nelng
        );
        return parent::get($this->endPoint . '/json/get_sediment_disaster_hazard_area_surface_by_geometry', $param, 0);
    }
}

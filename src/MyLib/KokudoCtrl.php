<?php
namespace MyLib;

/**
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

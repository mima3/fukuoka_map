<?php
namespace MyLib;

/**
 */
class TeapotCtrl extends \MyLib\ApiCtrlBase
{
    private $endPointSprql;
    private $endPointPos;


    /**
     * コンストラクタ
     * @param string  $endPointSprql SPARQL実行用のエンドポイント
     * @param string  $endPointPos   位置情報取得用のエンドポイント
     */
    public function __construct($endPointSprql, $endPointPos)
    {
        $this->endPointSprql = $endPointSprql;
        $this->endPointPos = $endPointPos;
        parent::__construct();
    }

    public function execute($query)
    {
        $param = array('query' => $query);
        return parent::post($this->endPointSprql, $param, 0);
    }
}

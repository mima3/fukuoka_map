<?php
namespace Model;

/**
 * モデルのベースクラス
 */
abstract class ModelBase
{
    protected $db;
    protected $app;
    public function __construct($app, $db)
    {
        $this->app = $app;
        $this->db = $db;
    }

    public function __destruct()
    {
        $this->app = null;
        $this->db = null;
    }

    /**
     * VUCUUMの実行
     */
    public function vucuum()
    {
        $this->db->exec(
            "VACUUM;"
        );
    }

    abstract public function setup();
}

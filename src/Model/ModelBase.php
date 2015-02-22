<?php
namespace Model;

/**
 * ���f���̃x�[�X�N���X
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
     * VUCUUM�̎��s
     */
    public function vucuum()
    {
        $this->db->exec(
            "VACUUM;"
        );
    }

    abstract public function setup();
}

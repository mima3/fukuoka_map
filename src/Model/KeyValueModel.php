<?php
namespace Model;

/**
 * キーバリューの情報を記録<br>
 */
class KeyValueModel extends \Model\ModelBase
{
    /**
     * データベースの設定を行う.
     */
    public function setup()
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS key_value (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key TEXT UNIQUE,
                value TEXT,
                updated TIMESTAMP
            );"
        );
        $this->db->exec(
            "CREATE INDEX IF NOT EXISTS key_value_index ON  key_value(key);"
        );
    }

    /**
     */
    public function get($key)
    {
        $ret = \ORM::for_table('key_value')
            ->where_equal('key', $key)
            ->find_one();

        return $ret->value;
    }

    public function set($key, $value)
    {
        $ret = \ORM::for_table('key_value')
            ->where_equal('key', $key)
            ->find_one();

        if ($ret) {
            $ret->value = $value;
            $ret->updated = time();
            $ret->save();
        } else {
            $row = \ORM::for_table('key_value')->create();
            $row->key = $key;
            $row->value = $value;
            $row->updated = time();
            $row->save();
        }
    }
}

<?php
namespace Model;

/**
 * APIのキャッシュを記録するモデル<br>
 */
class ApiCacheModel extends \Model\ModelBase
{
    /**
     * データベースの設定を行う.
     */
    public function setup()
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS apicache (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key TEXT UNIQUE,
                contents TEXT,
                updated TIMESTAMP DEFAULT (DATETIME('now','localtime'))
            );"
        );
        $this->db->exec(
            "CREATE INDEX IF NOT EXISTS apicache_name_key_index ON  apicache(key);"
        );
    }

    /**
     * キャッシュが登録されているか確認をする
     * @param  string  $key キャッシュのキー
     * @return キャッシュの内容.ない場合はnullとなる.
     */
    public function getContents($key)
    {
        $ret = \ORM::for_table('apicache')
            ->where_equal('key', $key)
            ->find_one();

        return (array) (json_decode($ret->contents));
    }

    /**
     * キャッシュを登録する
     * @param string $key      キャッシュのキー
     * @param string $contents キャッシュの内容
     */
    public function setContents($key, $contents)
    {
        \ORM::for_table('apicache')
            ->where_equal('key', $key)
            ->delete_many();
        $apicache = \ORM::for_table('apicache')->create();
        $apicache->key = $key;
        $apicache->contents = json_encode($contents);
        $apicache->updated = $contents['updated'];
        $apicache->save();
    }

    /**
     * APIのキャッシュをすべて削除する
     */
    public function clearContents()
    {
        \ORM::for_table('apicache')
            ->delete_many();
    }
}

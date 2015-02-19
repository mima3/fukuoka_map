<?php
namespace Model;

/**
 * Microsoft Translatorの結果をキャッシュするモデル<br>
 */
class MsTranslatorCacheModel extends \Model\ModelBase
{
    /**
     * データベースの設定を行う.
     */
    public function setup()
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS translator_cache (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              lang TEXT,
              src TEXT,
              result TEXT,
              updated TIMESTAMP DEFAULT (DATETIME('now','localtime')),
              author TEXT,
              UNIQUE(lang, src)
            );"
        );
        $this->db->exec(
            "CREATE INDEX IF NOT EXISTS translator_cache_lang_index ON  translator_cache(lang);"
        );
        $this->db->exec(
            "CREATE INDEX IF NOT EXISTS translator_cache_src_index ON  translator_cache(lang,src);"
        );
    }

    /**
     * 特定の言語のキャッシュを取得する
     * @param  string                                                 $lang 言語
     * @return キャッシュの内容.ない場合はnullとなる.
     */
    public function getCache($lang)
    {
        $ret = \ORM::for_table('translator_cache')
            ->where_equal('lang', $lang)
            ->find_array();
        $pairs = array();
        foreach ($ret as $r) {
            $pairs += array($r['src']=>$r['result']);
        }

        return $pairs;
    }

    /**
     * キャッシュを登録する
     * @param string $lang  言語
     * @param array  $pairs srcとresultのペアの一覧
     */
    public function addCache($lang, $pairs)
    {
        $this->db->beginTransaction();
        $updated = time();

        foreach ($pairs as $src => $result) {
            $row = \ORM::for_table('translator_cache')->create();
            $row->lang = $lang;
            $row->src = $src;
            $row->result = $result;
            $row->updated = $updated;
            $row->save();
        }

        $this->db->commit();
    }
    public function deleteCache($lang)
    {
        \ORM::for_table('translator_cache')
            ->where_equal('lang', $lang)
            ->delete_many();
    }

    private function createCond($id, $lang, $src, $result, $author)
    {
        $cond = \ORM::for_table('translator_cache');
        if ($id) {
            $cond = $cond->where_equal('id', $id);
        }
        if ($lang) {
            $cond = $cond->where_equal('lang', $lang);
        }
        if ($src) {
            $cond = $cond->where_like('src', '%' . $src .'%');
        }
        if ($result) {
            $cond = $cond->where_like('result', '%' . $result . '%');
        }
        if ($author) {
            $cond = $cond->where_like('author', '%' . $author . '%');
        }

        return $cond;
    }

    /**
     * 特定の要件による検索を行う<BR>
     * 複数指定された場合はAND検索となる
     * @param  int                                                    $offset 取得開始位置
     * @param  int                                                    $limit  取得数上限
     * @param  int                                                    $id     ID
     * @param  string                                                 $lang   言語
     * @param  string                                                 $src    翻訳元
     * @param  string                                                 $result 翻訳結果
     * @param string $author 修正を行ったユーザ名
     * @return キャッシュの内容.ない場合はnullとなる.
     */
    public function search($offset, $limit, $id, $lang, $src, $result, $author)
    {
        $res = new \stdClass();
        $res->rows = $this->createCond($id, $lang, $src, $result, $author)->
                            limit($limit)->
                            offset($offset)->
                            find_array();

        $res->records = $this->createCond($id, $lang, $src, $result, $author)->count();

        return $res;
    }

    /**
     * 翻訳内容の更新を行う
     * @param int    $id     対象のID
     * @param string $result 検索結果
     * @param string $author 修正を行ったユーザ名
     * @param int $updated 更新日時
     */
    public function update($id, $result, $author, $updated)
    {
        $ret = \ORM::for_table('translator_cache')
            ->where_equal('id', $id)
            ->find_one();
        $ret->result = $result;
        $ret->updated = $updated;
        $ret->author =$author;
        $ret->save();
    }
}

<?php
namespace Model;

/**
 * 東京メトロAPIの運行情報を記録するモデル<br>
 */
class TranslationLogModel extends \Model\ModelBase
{
    /**
     * データベースの設定を行う.
     */
    public function setup()
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS translation_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                author TEXT,
                targetId INTEGER,
                previous TEXT,
                after TEXT,
                updated TIMESTAMP
            );"
        );
        $this->db->exec(
            "CREATE INDEX IF NOT EXISTS ttranslation_log_author_index ON  translation_log(author);"
        );
    }

    private function createCond($author, $src, $lang)
    {
        $cond = \ORM::for_table('translation_log');
        $cond = $cond->table_alias('t1')
                ->select('t1.*')
                ->select(array('t2.lang','t2.src'));
        $cond = $cond->join('translator_cache', 't2.id = t1.targetId', 't2');
        if ($author) {
            $cond = $cond->where_equal('author', $author);
        }
        if ($lang) {
            $cond = $cond->where_equal('t2.lang', $lang);
        }
        if ($src) {
            $cond = $cond->where_like('t2.src', '%' . $src .'%');
        }
        return $cond;
    }
    /**
     * 翻訳情報の変更履歴を検索する
     * @param  string $offset  取得開始位置
     * @param  string $limit   取得件数
     * @param  string $author  作者
     * @param  string $src     翻訳元文字
     * @param  string $lang    言語
     * @return .
     */
    public function search($offset, $limit, $author, $src, $lang)
    {
        $res = new \stdClass();
        $res->rows = $this->createCond($author, $src, $lang)
            ->limit($limit)
            ->offset($offset)
            ->order_by_desc('updated')
            ->find_many();
        $res->records = $this->createCond($author, $src, $lang)->count();
        return $res;
    }

    /**
     * ログを登録
     * @param string $author     作者
     * @param string $targetId   変更対象
     * @param int    $previous   変更前
     * @param int    $after      変更後
     * @param int    $updated    実行日時のタイムスタンプ
     */
    public function append($author, $targetId, $previous, $after, $updated)
    {
        $row = \ORM::for_table('translation_log')->create();
        $row->author = $author;
        $row->targetId = $targetId;
        $row->previous = $previous;
        $row->after = $after;
        $row->updated = $updated;
        $row->save();
    }
}

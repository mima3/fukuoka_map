<?php
namespace MyLib;

/**
 * SPARQLのWHERE文を構築するためのクラス
 */
class TeapotWhere
{
    private $parent;
    private $lstWhere = array();
    private $lstFilter = array();

    /**
     * コンストラクタ
     * @param[in] $parent TeapotQuery
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    /**
     * where区
     * @param[in] $subject 主語
     * @param[in] $predicate 述語
     * @param[in] $obj 目的語
     * @retval $this
     */
    public function where($subject, $predicate, $obj)
    {
        $s = sprintf('%s %s %s .', $subject, $predicate, $obj);
        array_push($this->lstWhere, $s);
        return $this;
    }

    /**
     * filter区
     * @param[in] $condition 条件文
     * @retval $this
     */
    public function filter($condition)
    {
        $s = sprintf(' FILTER (%s) ', $condition);
        array_push($this->lstFilter, $s);
        return $this;
    }

    /**
     * 正規表現を用いたfilter区
     * @param[in] $col 対象の列
     * @param[in] $match 正規表現でマッチする条件
     * @param[in] $option オプション
     * @retval $this
     */
    public function filterRegex($col, $match, $option)
    {
        $s = sprintf(' FILTER regex(%s,"%s","%s") ', $col, $match, $option);
        array_push($this->lstFilter, $s);
        return $this;
    }

    /**
     * SPARQL実行
     * @retval 結果セット
     */
    public function execute()
    {
        return $this->parent->execute();
    }

    /**
     * limit区
     * @param[in] $d limit
     * @retval TeapotQuery
     */
    public function limit($d)
    {
        return $this->parent->limit($d);
    }

    /**
     * offset区
     * @param[in] $d offset
     * @retval TeapotQuery
     */
    public function offset($d)
    {
        return $this->parent->offset($d);
    }

    /**
     * orderby区
     * @param[in] $d orderby
     * @retval TeapotQuery
     */
    public function orderby($d)
    {
        return $this->parent->orderby($d);
    }

    /**
     * union区
     * @retval TeapotQueryが新たに作成したTeapotWhere
     */
    public function union()
    {
        return $this->parent->union();
    }

    /**
     * SQL文の構築
     * @retval SQL文
     */
    public function sql()
    {
        $s = '{';
        foreach ($this->lstWhere as $w) {
            $s .= $w;
        }
        foreach ($this->lstFilter as $f) {
            $s .= $f;
        }
        $s .= '}';
        return $s;
    }
}

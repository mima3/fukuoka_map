<?php
namespace MyLib;

/**
 * SPARQLのクエリーを構築するクラス
 */
class TeapotQuery
{
    private $api;
    private $strCol = '*';
    private $lstWhere = array();
    private $limit;
    private $distinct=false;
    private $offset;
    private $orderby;
    private $prefixes = array(
      'geo' => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
      'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
      'rdfs'=> 'http://www.w3.org/2000/01/rdf-schema#',
      'xsd' => 'http://www.w3.org/2001/XMLSchema#',
      'schema' => 'http://schema.org/',
      'foaf' => 'http://xmlns.com/foaf/0.1/',
      'tpp' => 'http://teapot.bodic.org/predicate/',
      'tpf' => 'http://teapot.bodic.org/facility/',
      'tpe' => 'http://teapot.bodic.org/equipement/',
      'tpo' => 'http://teapot.bodic.org/organization/',
      'tpt' => 'http://teapot.bodic.org/type/',
      'tpd' => 'http://teapot.bodic.org/dataset/',
      'tps' => 'http://teapot.bodic.org/stats/'
    );

    /**
     * コンストラクタ
     * @param[in] $ctrl TeapotCtrl
     */
    public function __construct($ctrl)
    {
        $this->api = $ctrl;
    }

    /**
     * 列を指定
     * @param[in] $cols 列の配列
     * @retval this
     */
    public function columns($cols)
    {
        if (!$cols) {
            $this->strCol = '*';
            return $this;
        }
        $this->strCol = implode(' ', $cols);
        return $this;
    }

    /**
     * where区
     * @param[in] $subject 主語
     * @param[in] $predicate 述語
     * @param[in] $obj 目的語
     * @retval TeapotWhere
     */
    public function where($subject, $predicate, $obj)
    {
        if (count($this->lstWhere) == 0) {
            array_push($this->lstWhere, new \MyLib\TeapotWhere($this));
        }
        return $this->lstWhere[0]->where($subject, $predicate, $obj);
    }

    /**
     * union区
     * @retval TeapotWhere
     */
    public function union()
    {
        $obj = new \MyLib\TeapotWhere($this);
        array_push($this->lstWhere, $obj);
        return $obj;
    }

    /**
     * distinct区
     * @retval this
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * limit区
     * @param[in] @limit limit
     * @retval this
     */
    public function limit($d)
    {
        $this->limit = $d;
        return $this;
    }

    /**
     * offset区
     * @param[in] @offset offset
     * @retval this
     */
    public function offset($d)
    {
        $this->offset = $d;
        return $this;
    }

    /**
     * orderby区
     * @param[in] @offset orderby
     * @retval this
     */
    public function orderby($d)
    {
        $this->orderby = $d;
        return $this;
    }

    /**
     * SQL文の構築
     * @retval SQL文
     */
    public function sql()
    {
        $sql = '';
        foreach ($this->prefixes as $key => $value) {
            $sql .= sprintf("PREFIX %s: <%s>\n", $key, $value);
        }
        if ($this->distinct) {
            $sql .= sprintf('SELECT DISTINCT %s ', $this->strCol);
        } else {
            $sql .= sprintf('SELECT %s ', $this->strCol);
        }
        foreach ($this->lstWhere as $i => $item) {
            if ($i ==0) {
                $sql .= ' WHERE {';
            } else {
                $sql .= ' UNION ';
            }
            $sql .= $item->sql();
        }
        $sql .= '}';

        if (isset($this->orderby)) {
            $sql .= sprintf(' ORDER BY %s ', $this->orderby);
        }
        if (isset($this->limit)) {
            $sql .= sprintf(' LIMIT %d ', $this->limit);
        }
        if (isset($this->offset)) {
            $sql .= sprintf(' OFFSET %d ', $this->offset);
        }

        return $sql;
    }

    /**
     * SPARQL実行
     * @retval 結果セット
     */
    public function execute()
    {
        $sql = $this->sql();
        $this->strCol = '*';
        $this->lstWhere = array();
        $this->limit = null;
        $this->distinct=false;
        $this->offset = null;
        $this->orderby = null;
        return $this->api->execute($sql);
    }

    /**
     * SPARQLを分割して実行する
     * これはTeapotの2097152 bytes of stringの制限を回避する目的に使用する。
     * @retval 結果セット
     */
    public function executeSpilit($limit)
    {
        $count = 0;
        $this->limit($limit);
        $binding = array();
        while (true) {
            $this->offset($limit * $count);
            $sql = $this->sql();
            $ret = $this->api->execute($sql);
            if ($ret['resultCode'] != \MyLib\TeapotCtrl::RESULT_CODE_OK) {
                break;
            }
            if (count($ret['contents']->results->bindings) == 0) {
                $ret['contents']->results->bindings = $binding;
                break;
            }
            $binding = array_merge($binding, $ret['contents']->results->bindings);
            $count += 1;
        }
        $this->strCol = '*';
        $this->lstWhere = array();
        $this->limit = null;
        $this->distinct=false;
        $this->offset = null;
        $this->orderby = null;
        return $ret;
    }
}

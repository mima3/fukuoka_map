<?php
namespace MyLib;

/**
 */
class TeapotQuery
{
    private $api;
    private $strCol = '*';
    private $lstWhere = array();
    private $limit;
    private $distinct=False;
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
     * @param string $ctrl TeapotCtrl
     */
    public function __construct($ctrl) {
        $this->api = $ctrl;
    }

    function columns($cols) {
        if (!$cols) {
            $this->strCol = '*';
            return $this;
        }
        $this->strCol = implode(' ', $cols);
        return $this;
    }

    function where($subject, $predicate, $obj) {
        if(count($this->lstWhere) == 0) {
            array_push($this->lstWhere, new \MyLib\TeapotWhere($this));
        }
        return $this->lstWhere[0]->where($subject, $predicate, $obj);
    }

    function union() {
        $obj = new \MyLib\TeapotWhere($this);
        array_push($this->lstWhere, $obj);
        return $obj;
    }

    function distinct() {
        $this->distinct = True;
        return $this;
    }

    function limit($d) {
        $this->limit = $d;
        return $this;
    }

    function offset($d) {
        $this->offset = $d;
        return $this;
    }

    function orderby($d) {
        $this->orderby = $d;
        return $this;
    }

    function sql() {
        $sql = '';
        foreach ($this->prefixes as $key=>$value) {
            $sql .= sprintf("PREFIX %s: <%s>\n", $key, $value);
        }
        if ($this->distinct) {
            $sql .= sprintf('SELECT DISTINCT %s ', $this->strCol);
        } else {
            $sql .= sprintf('SELECT %s ', $this->strCol);
        }
        foreach ($this->lstWhere as $i=>$item) {
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
        if (isset($this->_offset)) {
            $sql .= sprintf(' OFFSET %d ', $this->offset);
        }

        return $sql;
    }
    function execute() {
        $sql = $this->sql();
        $this->strCol = '*';
        $this->lstWhere = array();
        $this->limit = NULL;
        $this->distinct=False;
        $this->offset = NULL;
        $this->orderby = NULL;
        return $this->api->execute($sql);
    }

}

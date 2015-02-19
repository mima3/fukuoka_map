<?php
namespace MyLib;

/**
 */
class TeapotWhere
{
    private $parent;
    private $lstWhere = [];
    private $lstFilter = [];
    public function __construct($parent) {
      $this->parent = $parent;
    }
    public function where($subject, $predicate, $obj) {
        $s = sprintf('%s %s %s .', $subject, $predicate, $obj);
        array_push($this->lstWhere, $s);
        return $this;
    }

    public function filter($condition) {
        $s = sprintf(' FILTER (%s) ', $condition);
        array_push($this->lstFilter, $s);
        return $this;
    }

    public function filterRegex($col, $match, $option) {
        $s = sprintf(' FILTER regex(%s,"%s","%s") ', $col, $match , $option);
        array_push($this->lstFilter, $s);
        return $this;
    }

    public function execute() {
      return $this->parent->execute();
    }

    public function limit($d) {
      return $this->parent->limit($d);
    }

    public function offset($d) {
      return $this->parent->offset($d);
    }

    public function orderby($d) {
      return $this->parent->orderby($d);
    }

    public function union() {
      return $this->parent->union();
    }

    public function sql() {
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

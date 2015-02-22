<?php
namespace MyLib;

/**
 * SPARQL��WHERE�����\�z���邽�߂̃N���X
 */
class TeapotWhere
{
    private $parent;
    private $lstWhere = array();
    private $lstFilter = array();

    /**
     * �R���X�g���N�^
     * @param[in] $parent TeapotQuery
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    /**
     * where��
     * @param[in] $subject ���
     * @param[in] $predicate �q��
     * @param[in] $obj �ړI��
     * @retval $this
     */
    public function where($subject, $predicate, $obj)
    {
        $s = sprintf('%s %s %s .', $subject, $predicate, $obj);
        array_push($this->lstWhere, $s);
        return $this;
    }

    /**
     * filter��
     * @param[in] $condition ������
     * @retval $this
     */
    public function filter($condition)
    {
        $s = sprintf(' FILTER (%s) ', $condition);
        array_push($this->lstFilter, $s);
        return $this;
    }

    /**
     * ���K�\����p����filter��
     * @param[in] $col �Ώۂ̗�
     * @param[in] $match ���K�\���Ń}�b�`�������
     * @param[in] $option �I�v�V����
     * @retval $this
     */
    public function filterRegex($col, $match, $option)
    {
        $s = sprintf(' FILTER regex(%s,"%s","%s") ', $col, $match, $option);
        array_push($this->lstFilter, $s);
        return $this;
    }

    /**
     * SPARQL���s
     * @retval ���ʃZ�b�g
     */
    public function execute()
    {
        return $this->parent->execute();
    }

    /**
     * limit��
     * @param[in] $d limit
     * @retval TeapotQuery
     */
    public function limit($d)
    {
        return $this->parent->limit($d);
    }

    /**
     * offset��
     * @param[in] $d offset
     * @retval TeapotQuery
     */
    public function offset($d)
    {
        return $this->parent->offset($d);
    }

    /**
     * orderby��
     * @param[in] $d orderby
     * @retval TeapotQuery
     */
    public function orderby($d)
    {
        return $this->parent->orderby($d);
    }

    /**
     * union��
     * @retval TeapotQuery���V���ɍ쐬����TeapotWhere
     */
    public function union()
    {
        return $this->parent->union();
    }

    /**
     * SQL���̍\�z
     * @retval SQL��
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

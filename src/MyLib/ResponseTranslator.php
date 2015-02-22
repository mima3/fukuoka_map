<?php
namespace MyLib;

/**
 * teapotの応答を翻訳する.
 */
class ResponseTranslator
{
    private $trans;

    /**
     * コンストラクタ
     * @param MsTranslator  $tran MsTranslator
     */
    public function __construct($trans)
    {
        $this->trans = $trans;
    }

    /**
     * teapotのSPARQLの結果セットを翻訳する
     * @param[in] $res 結果セット
     * @retval 翻訳済みの結果セット
     */
    public function translateTeapotSparql($res)
    {
        $vars = $res['contents']->head->vars;
        $bindings = $res['contents']->results->bindings;
        foreach ($bindings as $b) {
            foreach ($vars as $v) {
                $obj = $b->$v;
                if ($obj->type == 'literal') {
                    $obj->translate_value = $this->trans->translator($obj->value);
                }
            }
        }
        return $res;
    }
}

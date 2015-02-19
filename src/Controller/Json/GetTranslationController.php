<?php
namespace Controller\Json;

/**
 * 翻訳用テキストを検索して取得する
 */
class GetTranslationController extends \Controller\ControllerBase
{
    public function route()
    {
        $model = $this->models['MsTranslatorCacheModel'];

        $limit = 20;
        if ($this->app->request->params('rows')) {
            $limit = $this->app->request->params('rows');
        }
        $offset = 0;
        if ($this->app->request->params('page')) {
            $offset = (($this->app->request->params('page') - 1) * $limit);
        }
        $filters = json_decode($this->app->request->params('filters'));
        $lang =null;
        $src = null;
        $result = null;
        if ($filters) {
            foreach ($filters->rules as $rule) {
                if ($rule->field == 'lang') {
                    $lang = $rule->data;
                }
                if ($rule->field == 'src') {
                    $src = htmlspecialchars($rule->data);
                }
                if ($rule->field == 'result') {
                    $result = htmlspecialchars($rule->data);
                }
                if ($rule->field == 'author') {
                    $author = htmlspecialchars($rule->data);
                }
            }
        }
        $ret = $model->search($offset, $limit, null, $lang, $src, $result, $author);

        header('Content-Type: text/javascript; charset=utf-8');
        $responce = new \stdClass();
        $responce->page = $this->app->request->params('page');
        $responce->total = ceil($ret->records/$limit);
        $responce->records = $ret->records;
        $i = 0;
        foreach ($ret->rows as $row) {
            $responce->rows[$i]['id']=$row['id'];
            $responce->rows[$i]['cell']=array($row['id'],
                                              $row['lang'],
                                              $row['src'],
                                              $row['result'],
                                              date("Y/m/d H:i:s", $row['updated']),
                                              $row['author']);
            $i = $i + 1;
        }
        print_r(json_encode($responce));

        return;
    }
}

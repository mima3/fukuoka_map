<?php
date_default_timezone_set('Asia/Tokyo');
require 'vendor/autoload.php';
require './config.php';

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-07 at 18:11:53.
 */
class TeapotQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    function testExecute()
    {
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
        ini_set('xdebug.var_display_max_depth', -1);
        $q = new \MyLib\TeapotQuery(new \MyLib\TeapotCtrl('http://teapot-api.bodic.org/api/v1/sparql', 'http://teapot-api.bodic.org/api/v1/places'));
        $ret = $q->columns(['?s', '?p', '?o'])
          ->distinct()
          ->where('?s', '?p', '?o')
          ->filter('!isBlank(?s)')
          ->limit(1)
          ->orderby('?s')
          ->execute();
        $this->assertEquals(
            '{"resultCode":0,"errorMsg":null,"contents":{"head":{"vars":["s","p","o"]},"results":{"bindings":[{"s":{"type":"uri","value":"http:\/\/teapot.bodic.org\/dataset\/aedLocation"},"p":{"type":"uri","value":"http:\/\/www.w3.org\/1999\/02\/22-rdf-syntax-ns#type"},"o":{"type":"uri","value":"http:\/\/teapot.bodic.org\/type\/dataset"}}]}}}',
            json_encode($ret),
            '正常の場合'
        );
    }

    function createQueryTooLarge() {
        $query = new \MyLib\TeapotQuery(new \MyLib\TeapotCtrl('http://teapot-api.bodic.org/api/v1/sparql', 'http://teapot-api.bodic.org/api/v1/places'));
        $medical_subjects = [
          "こう門科",
          "アレルギー科",
          "リウマチ科",
          "リハビリテーション科",
          "内科",
          "呼吸器外科",
          "呼吸器科",
          "外科",
          "婦人科",
          "小児外科",
          "小児歯科",
          "小児科",
          "形成外科",
          "循環器科",
          "心療内科",
          "心臓血管外科",
          "性病科",
          "放射線科",
          "整形外科",
          "歯科",
          "歯科口腔外科",
          "気管食道科",
          "泌尿器科",
          "消化器科",
          "産婦人科",
          "産科"
        ];
        $subjFilter = '';
        $i = 0;
        foreach($medical_subjects as $subj) {
            if ($i != 0) {
                $subjFilter .= ' || ';
            }
            $subjFilter .= ' ?x = "' .$subj . '"';
            ++$i;
        }
        $query->columns(array('?s', '?p', '?o'))
            ->distinct()
            ->where('?s', '<http://teapot.bodic.org/predicate/診療科目>', '?x')
            ->where('?s', '?p', '?o')
            ->filter($subjFilter)
            ->filter('!isBlank(?o)')
            ->filter('
               ?p = <http://www.w3.org/2000/01/rdf-schema#label> ||
               ?p = <http://teapot.bodic.org/predicate/診療科目> ||
               ?p = <http://teapot.bodic.org/predicate/電話番号> ||
               ?p = <http://teapot.bodic.org/predicate/緯度> ||
               ?p = <http://teapot.bodic.org/predicate/経度> ||
               ?p = <http://teapot.bodic.org/predicate/種別> ||
               ?p = <http://teapot.bodic.org/predicate/郵便番号> ||
               ?p = <http://teapot.bodic.org/predicate/病床数合計> ||
               ?p = <http://teapot.bodic.org/predicate/addressClean>')
            ->orderby('?s ?p ?o');
        return $query;
    }
    function testExecuteSpilit()
    {
        $query = $this->createQueryTooLarge();
        $ret = $query->execute();
        $this->assertEquals(\MyLib\TeapotCtrl::RESULT_CODE_ERR_SERVER, $ret['resultCode'], 'TooLarge');

        $query = $this->createQueryTooLarge();
        $ret = $query->executeSpilit(5000);
        $this->assertEquals(\MyLib\TeapotCtrl::RESULT_CODE_OK, $ret['resultCode'], 'OK');

        $query = $query->where('<http://teapot.bodic.org/facility/さくら病院〒814_0142（医療法人社団江頭会さくら病院）>', '?p', '?o')
                       ->filter('!isBlank(?o)')
                       ->orderby('?p ?o');
        $ret_single = $query->execute();

        $query = $query->where('<http://teapot.bodic.org/facility/さくら病院〒814_0142（医療法人社団江頭会さくら病院）>', '?p', '?o')
                       ->filter('!isBlank(?o)')
                       ->orderby('?p ?o');
        $ret_spilit = $query->executeSpilit(5);

        $this->assertEquals(json_encode($ret_single), json_encode($ret_spilit), '分割時と一括時が同じ結果になること');
    }
}

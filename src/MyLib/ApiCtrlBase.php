<?php
namespace MyLib;

/**
 */
class ApiCtrlBase
{
    private $client;
    /** 結果コード：正常終了 */
    const RESULT_CODE_OK = 0;

    /** 結果コード：APIの異常 */
    const RESULT_CODE_ERR_SERVER = 1;
    const RESULT_CODE_ERR_API = 2;

    /** APIの最大リトライ数 */
    const MAX_TRY_COUNT = 3;

    /** APIのリトライ間隔 */
    const WAIT_COUNT = 100000; // 1ms

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->client = new \HTTP_Client();
    }

    /**
     * APIを実行してレスポンスの取得
     * もし、503エラーの場合は、WAIT_COUNTマイクロ秒後に
     * MAX_TRY_COUNT回までリトライする。
     * @param  string $url      対象のURL
     * @param  array  $param    パラメータの連想配列
     * @param  int    $trycount 現在の試行回数
     * @return array  レスポンスの結果
     */
    protected function get($url, $param, $trycount)
    {
        $code = $this->client->get($url, $param);
        return $this->checkResponce($url, $param, $trycount, $code, $this->get);
    }
    protected function post($url, $param, $trycount)
    {
    
        $code = $this->client->post($url, $param);
        return $this->checkResponce($url, $param, $trycount, $code, $this->post);
    }

    /**
     * APIの応答を解析
     * MAX_TRY_COUNT回までリトライする。
     * @param  string $url      対象のURL
     * @param  array  $param    パラメータの連想配列
     * @param  int    $trycount 現在の試行回数
     * @param  int    $code     応答コード
     * @param  function $fnc    再実行用の関数
     * @return array  レスポンスの結果
     */
    private function checkResponce($url, $param, $trycount, $code, $func)
    {
        $res = $this->client->currentResponse();
        $body = null;
        if ($res) {
            if (isset($res['body'])) {
                $body = $res['body'];
            }
        }
        $ret = null;
        if ($code != 200) {
            // リトライ処理.
            if ($code == 503 and $trycount < ApiCtrlBase::MAX_TRY_COUNT) {
                // for memory leak
                $this->client->reset();

                usleep(($trycount + 1) * ApiCtrlBase::WAIT_COUNT);

                return $func($url, $param, $trycount + 1);
            }

            $msg = sprintf('ResponceCode: %d Message:%s', $code, $body);
            $ret = array('resultCode' => ApiCtrlBase::RESULT_CODE_ERR_SERVER,
                                     'errorMsg' => $msg,
                                     'contents' => null);
        } else {
            $json = json_decode($body);
            if ($json) {
                $ret = array('resultCode' => ApiCtrlBase::RESULT_CODE_OK,
                                         'errorMsg' => null,
                                         'contents' => $json);
            } else {
                $ret = array('resultCode' => ApiCtrlBase::RESULT_CODE_ERR_API,
                                         'errorMsg' => $body,
                                         'contents' => null);
            }
        }

        // for memory leak
        $this->client->reset();

        return $ret;
    }
}

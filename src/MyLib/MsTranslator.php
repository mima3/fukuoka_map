<?php
namespace MyLib;

/**
 */
class MsTranslator
{
    private $apiKey;
    private $model;
    private $lang;
    private $cache;
    private $needUpdate;
    private $addedlist;
    const BASE_LANG='ja';

    /**
     * コンストラクタ
     * @param string                        $apiKey apiKey
     * @param \Model\MsTranslatorCacheModel $model  キャッシュ用のモデル
     */
    public function __construct($apiKey, $model, $lang)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        if ($lang) {
            $this->lang = $lang;
        } else {
            $this->lang = \MyLib\MsTranslator::BASE_LANG;
        }
        $this->cache = $this->model->getCache($this->lang);
        $this->needUpdate = false;
        $this->addedlist = array();
    }

    public function translator($src)
    {
        if (!$src) {
            return $src;
        }
        if(preg_match("/^[!-~]+$/", $src)){
            return $src;
        }
        if (ctype_alnum($src)) {
            return $src;
        }
        if (\MyLib\MsTranslator::BASE_LANG===$this->lang) {
            return $src;
        }
        if (isset($this->cache[$src])) {
            return $this->cache[$src];
        }
        $ret = $this->doApi($src);
        if ($ret) {
            $this->needUpdate = true;
            $this->cache[$src] = $ret;
            array_push($this->addedlist, $src);

            return $ret;
        } else {
            return $src;
        }
    }

    public function needUpdate()
    {
        return $this->needUpdate;
    }

    public function updateCacheDb()
    {
        if (\MyLib\MsTranslator::BASE_LANG===$this->lang) {
            return;
        }
        if ($this->needUpdate()) {
            //$this->model->deleteCache($this->lang);
            $targets = array();
            foreach ($this->addedlist as $a) {
                $targets += array($a => $this->cache[$a]);
            }
            $this->model->addCache($this->lang, $targets);
            $this->needUpdate = false;
            $this->addedlist = array();
        }
    }

    private function doApi($src)
    {
        $ch = curl_init(
            'https://api.datamarket.azure.com/Bing/MicrosoftTranslator/v1/Translate?Text=%27' .
            urlencode($src).
            '%27&To=%27'.
            $this->lang.'%27'
        );
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey.':'.$this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);

        $errno = curl_errno($ch);
        if ($errno === 0) {
            $result = explode('<d:Text m:type="Edm.String">', $result);
            $result = explode('</d:Text>', $result[1]);
            $result = $result[0];

            return $result;
        } else {
            $error = curl_error($ch);

            return null;
        }
    }
}

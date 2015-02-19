<?php
namespace MyLib;

/**
 */
class JsonCtrl
{
    private $dataFolder;

    /**
     * コンストラクタ
     * @param string $dataFolder JSON情報の格納してあるフォルダ
     */
    public function __construct($dataFolder)
    {
        $this->dataFolder = $dataFolder;
        $this->cache = array();
    }

    public function getTranslationInfo()
    {
        return $this->readTypeJson('getTranslationInfo', $this->dataFolder . '/TranslationInfo.json');
    }

    private function readTypeJson($key, $path)
    {
        if ($this->cache[$key]) {
            return $this->cache[$key];
        }
        $handle = fopen($path, 'r');
        $ret = fread($handle, filesize($path));
        fclose($handle);
        $data = (array) json_decode($ret);
        $this->cache += array($key=>$data);
        return $data;
    }
}

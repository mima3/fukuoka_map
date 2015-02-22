<?php
namespace Controller\Page;

/**
 * スタートページ
 * アプリケーションと、各機能の解説。
 */
class StartController extends \Controller\ControllerBase
{
    public function route()
    {
        $tran = $this->modules['MsTranslator'];
        
        $introductText = "";
        $introductText .= "このページでは、「公共施設等情報のオープンデータ実証 開発者サイト」が提供するデータをMicrosoft Translatorを使用して多言語対応します。\n";
        $introductText .= "しかしながら機械翻訳は完全ではないため、不適切なメッセージかもしれません。この場合、Twitterのアカウントにログインすることで、その不適切なメッセージを修正できます。\n";
        $introductText .= "今回は、現在提供されている福岡市のオープンデータにおいて、外国からの観光客でも利用する可能性があると思われる病院と避難所のデータを多言語化しています。\n";

        $hospitalMapText = "";
        $hospitalMapText .= "福岡市の提供する病院データを地図上に表示します。\n";
        $hospitalMapText .= "マーカーをクリックすることにより、病院の詳細を閲覧することができます。\n";
        $hospitalMapText .= "また、この際、ルート検索を行うことにより、開始地点からの所要時間と距離が取得できます。\n";

        $disasterMapText = "";
        $disasterMapText .= "福岡市の提供する避難所データを地図上に表示します。\n";
        $disasterMapText .= "マーカーをクリックすることにより、病院の詳細を閲覧することができます。\n";
        $disasterMapText .= "また、この際、ルート検索を行うことにより、開始地点からの所要時間と距離が取得できます。\n";
        $disasterMapText .= "この地図では国土数値情報が提供する、土砂災害危険個所データと浸水想定区域データを重ねて表示することが可能です。\n";

        $translatLogText = "翻訳情報の修正履歴を閲覧できます。";

        $label = array(
            'title' => $tran->translator('福岡市オープンデータ多言語化MOD'),
            'introductText' => nl2br($tran->translator($introductText)),
            'hospitalMapText' => nl2br($tran->translator($hospitalMapText)),
            'disasterMapText' => nl2br($tran->translator($disasterMapText)),
            'translatLogText' => nl2br($tran->translator($translatLogText)),
            'feature' => $tran->translator('機能'),
            'update_translation' => $tran->translator('テキスト修正'),
            'teapot' => $tran->translator('共施設等情報のオープンデータ実証 開発者サイト')
        );
        $tempData = array(
            'appName' => $this->app->getName(),
            'label'=>$label
        );
        $tempData += $this->getHeaderTempalteData();
        $this->app->render('start.tpl', $tempData);
    }
}

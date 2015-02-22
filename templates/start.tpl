<!--
 このテンプレートは/{$appName}/のレイアウトを記述します.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="{$lang}">
<head>
  <title>{$label['title']}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <link rel="stylesheet" href="/{$appName}/js/jquery.bxslider/jquery.bxslider.css">
  {include file='common_include.tpl'}
  <script type="text/javascript" src="/{$appName}/js/jquery.bxslider/jquery.bxslider.js"></script>
  <script type="text/javascript" src="/{$appName}/js/start.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
</head>
<body>
{include file='header.tpl'}
<div id="contents" {if $rtl}class="rtl"{/if}>
  <h1>福岡市オープンデータ多言語化MOD</h3>
  <p>このページでは、「公共施設等情報のオープンデータ実証 開発者サイト」が提供するデータをMicrosoft Translatorを使用して多言語対応します。</p>
  <p>しかしながら機械翻訳は完全ではないため、不適切なメッセージかもしれません。この場合、Twitterのアカウントにログインすることで、その不適切なメッセージを修正できます。</p>
  <p>今回は、現在提供されている福岡市のオープンデータにおいて、外国からの観光客でも利用する可能性があると思われる病院と避難所のデータを多言語化しています。</p>
  <h2>Link</h2>
  <h3>機能</h3>
  <h4><a href="/{$appName}/page/hospital_map?lang={$lang}">病院マップ</a></h4>
  <p>福岡市の提供する病院データを地図上に表示します。</p>
  <p>マーカーをクリックすることにより、病院の詳細を閲覧することができます。</p>
  <p>また、この際、ルート検索を行うことにより、開始地点からの所要時間と距離が取得できます。</p>
  <h4><a href="/{$appName}/page/disaster_map?lang={$lang}">災害マップ</a></h4>
  <p>福岡市の提供する避難所データを地図上に表示します。</p>
  <p>マーカーをクリックすることにより、病院の詳細を閲覧することができます。</p>
  <p>また、この際、ルート検索を行うことにより、開始地点からの所要時間と距離が取得できます。</p>
  <p>この地図では国土数値情報が提供する、土砂災害危険個所データと浸水想定区域データを重ねて表示することが可能です。</p>
  <h4><a href="/{$appName}/page/translation_log?lang={$lang}">テキスト変更履歴</a></h4>
  <p>翻訳情報の修正履歴を閲覧できます。</p>
  <h4><a href="/{$appName}/page/translation?lang={$lang}" >テキストの修正</a></h4>
  <p>Twitterのアカウントにログインして翻訳後のテキストを修正可能です。</p>
</div>
</body>
</html>

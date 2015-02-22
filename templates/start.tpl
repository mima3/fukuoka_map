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
  <h1>{$label['title']}</h3>
  {$label['introductText']}
  <h2>Link</h2>
  <p><a href="http://teapot.bodic.org/">{$label['teapot']}</a></p>
  <p><a href="https://datamarket.azure.com/dataset/1899a118-d202-492c-aa16-ba21c33c06cb">Microsoft Translator</a></p>
  <p><a href="https://github.com/mima3/fukuoka_map">GitHub</a></p>
  <h2>{$label['feature']}</h2>
  <h3><a href="/{$appName}/page/hospital_map?lang={$lang}">{$headLabel['hospital_map']}</a></h3>
  {$label['hospitalMapText']}
  <h3><a href="/{$appName}/page/disaster_map?lang={$lang}">{$headLabel['disaster_map']}</a></h3>
  {$label['disasterMapText']}
  <h3><a href="/{$appName}/page/translation_log?lang={$lang}">{$headLabel['translation_log']}</a></h3>
  {$label['translatLogText']}
  <h3><a href="/{$appName}/page/translation?lang={$lang}" >{$label['update_translation']}</a></h3>
  <p>{$headLabel['translation']}</p>
</div>
</body>
</html>

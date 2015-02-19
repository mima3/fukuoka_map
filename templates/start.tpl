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
  <h3>test</h3>
</div>
</body>
</html>

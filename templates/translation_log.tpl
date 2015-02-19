<!--
 このテンプレートは/{$appName}/translation_logのレイアウトを記述します.
 翻訳の変更履歴なので日本語のみを表示します。
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <link type="text/css" media="screen" href="/{$appName}/css/ui.jqgrid.css" rel="stylesheet" />
  {include file='common_include.tpl' themes='cupertino'}
  <link rel="stylesheet" href="/{$appName}/js/select2/select2.css" type="text/css" />
  <link rel="stylesheet" href="/{$appName}/css/tooltipster.css" type="text/css" />
  <link type="text/css" media="screen" href="/{$appName}/css/base.css" rel="stylesheet" />
  <script type="text/javascript" src="/{$appName}/js/jsGrid/jquery.jqGrid.src.js" ></script>
  <script type="text/javascript" src="/{$appName}/js/jsGrid/i18n/grid.locale-ja.js" ></script>
  <script type="text/javascript" src="/{$appName}/js/translation_log.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>

  <title>テキスト変更履歴</title>
</head>
<body>
{include file='header.tpl'}

<div id="contents">
  <h1>テキスト変更履歴</h1>
  <table id="transtb" class="scroll"></table>
  <div id="pagertb" ></div>
</div>
</body>
</html>

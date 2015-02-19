<!--
 このテンプレートは/{$appName}/translationのレイアウトを記述します.
 翻訳画面なので日本語のみを表示します。
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <link type="text/css" media="screen" href="/{$appName}/css/ui.jqgrid.css" rel="stylesheet" />
  {include file='common_include.tpl' themes='cupertino'}
  <script type="text/javascript" src="/{$appName}/js/jsGrid/jquery.jqGrid.src.js" ></script>
  <script type="text/javascript" src="/{$appName}/js/jsGrid/i18n/grid.locale-ja.js" ></script>
  <script type="text/javascript" src="/{$appName}/js/translation.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>

  <title>テキスト修正</title>
</head>
<body>
{include file='header.tpl'}

<div id="contents">
  <h1>テキスト修正</h1>
  <p>現在、「{$user}」でログイン中です。</p>
  <p>このアカウントで、ローカライズに使用している文字の修正を行えます。</p>
  <table id="transtb" class="scroll"></table>
  <div id="pagertb" ></div>
  <br>
  <h2>使用可能な言語</h2>
  <table class="normal">
    <thead>
      <th>コード</th>
      <th>言語名</th>
      <th>言語ファイル</th>
    </thead>
    <tbody>
    {foreach from=$langList key=key item=item}
      <tr>
        {if 'ja' eq $key}
        {else}
          <td>{$key}</td>
          <td>{$item->title}</td>
          <td><a href="/{$appName}/json/get_translation?{literal}rows=10000&page=1&filters={%22groupOp%22%3A%22AND%22%2C%22rules%22%3A[{%22field%22%3A%22lang%22%2C%22op%22%3A%22eq%22%2C%22data%22%3A%22{/literal}{$key}{literal}%22}]}{/literal}">Download</a></td>
        {/if}
      </tr>
    {/foreach}
    </tbody>
  </table>
</div>
</body>
</html>

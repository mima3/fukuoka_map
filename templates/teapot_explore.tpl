<!DOCTYPE html>
<html lang="en">
<head>
  <title>teapotのSPARQL実行</title>
  <meta charset="UTF-8">
  <link href="/{$appName}/js/jquery.dynatree/skin/ui.dynatree.css" rel="stylesheet" type="text/css" id="skinSheet">
  {include file='common_include.tpl'}
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&language={$gmaplang}"></script>
  <script type="text/javascript" src="/{$appName}/js/async/lib/async.js"></script>
  <script type="text/javascript" src="/{$appName}/js/teapot.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
  <script type="text/javascript" src="/{$appName}/js/jquery.dynatree/jquery.dynatree.min.js"></script>

  {literal}
  <!-- レスポンスを表示するテーブルのテンプレート -->
  <script id="tmplSpaqlRes" type="text/x-jsrender">
    <thead>
      {{for head.vars}}
        <th>{{:}}</th>
      {{/for}}
    </thead>
    <tbody>
      {{for results.bindings}}
        <tr>
        {{props :}}
          <td>{{>prop.value}} ({{>prop.type}})</td>
        {{/props}}
        </tr>
      {{/for}}
    </tbody>
  </script>

  <!-- マーカーのInfoWindowのテンプレート -->
  <script id="tmplInfoWindow" type="text/x-jsrender">
    <p>{{:data['http://www.w3.org/2000/01/rdf-schema#label'].value}}</p>
    <div id="legs_{{:prefix}}"></div>
    <button id="btn_{{:prefix}}">ルート検索</button>
  </script>
  {/literal}
<body>
{include file='header.tpl'}
<div id="contents">
  <h1>Teapot Explore</h1>
  <div>
    <p>このページでは公共施設等情報のオープンデータを取得します。</p>
    <a href="http://teapot.bodic.org/">公共施設等情報のオープンデータ実証 開発者サイト</a>
  </div>
  <div id="left_area" style="overflow: scroll;">
    <div id="classtree"></div>
  </div>
  <div id="main_area">
    <div id="map_canvas"></div>
  </div>
  <div id="bottom_area">
    <button id="btnQuery">Query</button>
    <div id="error_area"></div>
    <table class="normal" id="res"/>
  </div>
</div>

  <script type="text/javascript" src="/{$appName}/js/teapot_explore.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>

</div>
</body>
</html>

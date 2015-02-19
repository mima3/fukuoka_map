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
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&language={$gmaplang}"></script>
  <script type="text/javascript" src="/{$appName}/js/async/lib/async.js"></script>
  <script type="text/javascript" src="/{$appName}/js/teapot.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
  <script type="text/javascript" src="/{$appName}/js/disaster_map.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
  <!-- マーカーのInfoWindowのテンプレート -->
  <script id="tmplInfoWindow" type="text/x-jsrender">
  {literal}
    <p>{{:data['http://www.w3.org/2000/01/rdf-schema#label'].translate_value}}（{{:data['http://teapot.bodic.org/predicate/種別'].translate_value}}）</p>
    {{if data['building']}}
      <table class="normal">
        <thead>
          <th>建物名称</th>
          <th>構造</th>
          <th>地上階数</th>
          <th>地下階数</th>
          <th>延床面積</th>
        </thead>
        <tbody>
        {{props data['building']}}
          <tr>
            <td>{{>prop['http://www.w3.org/2000/01/rdf-schema#label'].translate_value}}</td>
            <td>{{>prop['http://teapot.bodic.org/predicate/構造'].translate_value}}</td>
            <td>{{>prop['http://teapot.bodic.org/predicate/地上階数'].translate_value}}</td>
            <td>{{>prop['http://teapot.bodic.org/predicate/地下階数'].translate_value}}</td>
            <td>{{>prop['http://teapot.bodic.org/predicate/延床面積'].translate_value}}</td>
          </tr>
        {{/props}}
        </tbody>
      </table>
    {{/if}}

    <button id="btn_{{:prefix}}">{/literal}{$label['route_search']}{literal}</button>
    <button id="btnClose_{{:prefix}}">Close</button>
  {/literal}
  </script>
</head>
<body>
{include file='header.tpl'}
<div id="contents" {if $rtl}class="rtl"{/if}>
  <h1>{$label['title']}</h1>
  <div>
    {$label['disaster_data']}:<select id="selDisasterData">
        <option value="no_data">{$label['no_data']}</option>
        <option value="flood_data">{$label['flood_data']}</option>
        <option value="gust_data">{$label['gust_data']}</option>
        <option value="sediment_data">{$label['sediment_data']}</option>
    </select>
    <button id="btnCurPos">{$label['curpos']}</button>
    <button id="btnCurPosCenter">{$label['center']}</button>
  </div>
  <div id="map_canvas"></div>
</div>
</body>
</html>

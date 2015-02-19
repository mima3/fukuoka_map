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
  <script type="text/javascript" src="/{$appName}/js/teapot.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
  <script type="text/javascript" src="/{$appName}/js/hospital_map.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
  <!-- マーカーのInfoWindowのテンプレート -->
  <script id="tmplInfoWindow" type="text/x-jsrender">
  {literal}
    <table class="normal infoWindow">
      <tr>
        <th>{/literal}{$label['name']}{literal}</th>
        <td>{{:data['http://www.w3.org/2000/01/rdf-schema#label']}}</td>
      </tr>
      <tr>
        <th>{/literal}{$label['kind']}{literal}</th>
        <td>{{:data['http://teapot.bodic.org/predicate/種別']}}</td>
      </tr>
      <tr>
        <th>{/literal}{$label['postcode']}{literal}</th>
        <td>{{:data['http://teapot.bodic.org/predicate/郵便番号']}}</td>
      </tr>
      <tr>
        <th>{/literal}{$label['address']}{literal}</th>
        <td>{{:data['http://teapot.bodic.org/predicate/addressClean']}}</td>
      </tr>
      <tr>
        <th>{/literal}{$label['phoneno']}{literal}</th>
        <td>092-{{:data['http://teapot.bodic.org/predicate/電話番号']}}</td>
      </tr>
      <tr>
        <th>{/literal}{$label['medical_subject']}{literal}</th>
        <td>
          {{for data['http://teapot.bodic.org/predicate/診療科目']}}
            {{:}} 
          {{/for}}
        </td>
      </tr>
      <tr>
        <th>{/literal}{$label['bedcount']}{literal}</th>
        <td>{{:data['http://teapot.bodic.org/predicate/病床数合計']}}</td>
      </tr>
      <tr>
        <th>{/literal}{$label['distance']}{literal}</th>
        <td id="legs_{{:prefix}}"></td>
      </tr>
    </table>
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
    {$label['medical_subject']}:<select id="selMedicalSubject" multiple="multiple">
        {foreach from=$medicalSubjects key=key item=item}
            <option value="{$key}">{$item}</option>
        {/foreach}
    </select>
    <button id="btnSearch">{$label['search']}</button>
    <button id="btnCurPos">{$label['curpos']}</button>
    <button id="btnCurPosCenter">{$label['center']}</button>
  </div>
  <div id="map_canvas"></div>
</div>
</body>
</html>

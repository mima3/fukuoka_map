<!DOCTYPE html>
<html lang="en">
<head>
  <title>teapotのSPARQL実行</title>
  <meta charset="UTF-8">
  {include file='common_include.tpl'}

  {literal}
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
  <script id="tmplPrefixesRes" type="text/x-jsrender">
    <thead>
      <th>name</th>
      <th>IRI</th>
      <th>Description</th>
    </thead>
    <tbody>
      {{for data}}
        <tr>
          <td>{{:name}}</td>
          <td>{{:iri}}</td>
          <td>{{:description}}</td>
        </tr>
      {{/for}}
    </tbody>
  </script>
  <script id="tmplVocabularies" type="text/x-jsrender">
    <thead>
      <th>IRI</th>
      <th>classification</th>
      <th>definitionType</th>
      <th>forType</th>
      <th>Description</th>
    </thead>
    <tbody>
      {{for data}}
        <tr>
          <td>{{:iri}}</td>
          <td>{{:classification}}</td>
          <td>{{:definitionType}}</td>
          <td>{{:forType}}</td>
          <td>{{:description}}</td>
        </tr>
      {{/for}}
    </tbody>
  </script>
  <script id="tmplHistory" type="text/x-jsrender">
    <option value="{{:}}">{{:}}</option>
  </script>
  {/literal}
<body>
{include file='header.tpl'}
<div id="contents">
  <h1>teapotのSPARQL実行</h1>
  <div>
    <p>このページでは公共施設等情報のオープンデータをSPARQLで取得します。</p>
    <a href="http://teapot.bodic.org/">公共施設等情報のオープンデータ実証 開発者サイト</a>
  </div>
  <div style="float:left;">
    <select id="lstHistory" style="width:300px;" size="20"></select>
  </div>
  <div id="main_area">
    <textarea id="sql" rows="20" cols="80">select distinct * where { ?v ?p ?o . } LIMIT 10</textarea>
  </div>
  <div id="bottom_area">
    <div id="error_area"></div>
    <p>
      <button id="runSPARQL">SPARQL実行</button>
      <button id="getPrefixes">名前空間閲覧</button>
      <button id="getVocabularies">語彙情報閲覧</button>
      
      <a href="http://teapot.bodic.org/voc_doc.html" target="_blank">語彙</a>
    </p>
    <table class="normal" id="res"/>
  </div>
  <script type="text/javascript" src="/{$appName}/js/teapot_sparql.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
</div>
</body>
</html>

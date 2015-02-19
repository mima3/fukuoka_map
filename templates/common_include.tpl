<!--
 このテンプレートは共通にインクルードする項目を記述します.
 
-->
  {if $themes}
    <link rel="stylesheet" href="/{$appName}/css/themes/{$themes}/jquery-ui.min.css" type="text/css" />
  {else}
    <link rel="stylesheet" href="/{$appName}/css/themes/pepper-grinder/jquery-ui.min.css" type="text/css" />
  {/if}
  <link rel="stylesheet" href="/{$appName}/js/select2/select2.css" type="text/css" />
  <link rel="stylesheet" href="/{$appName}/css/tooltipster.css" type="text/css" />
  <link rel="stylesheet" href="/{$appName}/js/jQuery.msgBox/styles/msgBoxLight.css" type="text/css">
  <link rel="stylesheet" href="/{$appName}/css/base.css" type="text/css" />
  <script type="text/javascript">
     function getAppName() {
       return '{$appName}';
     }
  </script>

  <script type="text/javascript" src="/{$appName}/js/jquery/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="/{$appName}/js/jquery/jquery-ui-1.10.4.min.js"></script>
  <script type="text/javascript" src="/{$appName}/js/select2/select2.min.js"></script>  
  <script type="text/javascript" src="/{$appName}/js/store/store.min.js"></script>
  <script type="text/javascript" src="/{$appName}/js/jquery/jquery.tooltipster.min.js"></script>
  <script type="text/javascript" src="/{$appName}/js/blockui/jquery.blockUI.js"></script>
  <script type="text/javascript" src="/{$appName}/js/jQuery.msgBox/scripts/jquery.msgBox.js"></script>
  <script type="text/javascript" src="/{$appName}/js/jquery/jsrender.min.js"></script>
  <script type="text/javascript" src="/{$appName}/js/util.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>
  <script type="text/javascript" src="/{$appName}/js/header.js?{($smarty.now|date_format:"%Y%m%d%H%M%S")}"></script>


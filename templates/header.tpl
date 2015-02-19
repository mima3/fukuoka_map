<!--
 このテンプレートは共通ヘッダーのレイアウトを記述します.
-->
<div id="header" >
  <!--言語選択用のセレクトボックス-->
  <div class="headItem {if $rtl}rtl{/if}">
    <img src="/{$appName}/img/world.png" class="tooltip" title="{$headLabel['selectLang']}" alt=""/>
    <select id="langSelect">
      {foreach from=$langList key=key item=item}
        {if $lang eq $key}
          <option value="{$key}" selected>{$item->title}</option>
        {else}
          <option value="{$key}" >{$item->title}</option>
        {/if}
      {/foreach}
    </select>
  </div>

  <!--スタートページへのリンク-->
  <div class="headItem {if $rtl}rtl{/if}">
    <a href="/{$appName}?lang={$lang}" >
      <img src="/{$appName}/img/home.png" class="tooltip btn_icon" title="{$headLabel['start']}" alt=""></img>
    </a>
  </div>

  <!--履歴へのリンク-->
  <div class="headItem submenu {if $rtl}rtl{/if}">
    <img src="/{$appName}/img/history.png" class="btn_icon" alt=""></img>
    <div class="submenuItem">
      <ul>
        <li><a href="/{$appName}/page/hospital_map?lang={$lang}">{$headLabel['hospital_map']}</a></li>
        <li><a href="/{$appName}/page/disaster_map?lang={$lang}">{$headLabel['disaster_map']}</a></li>
        <li><a href="/{$appName}/page/translation_log?lang={$lang}">{$headLabel['translation_log']}</a></li>
      </ul>
    </div>
  </div>

  <!--ローカライズ編集へのリンク-->
  <div class="headItem {if $rtl}rtl{/if}">
    <a href="/{$appName}/page/translation?lang={$lang}" >
      <img src="/{$appName}/img/edit.png" class="tooltip btn_icon" title="{$headLabel['translation']}" alt=""></img>
    </a>
  </div>


  <!-- 以降右からのメニュー============================= -->
  <div class="headerRightGroup" style="float:right;padding:0 10px;">
    <!-- ログイン -->
    <div class="rightItem {if $rtl}rtl{/if}" >
      {if $user}
        <a href="/{$appName}/logout?lang={$lang}">{$headLabel['logout']}</a>
      {else}
         <a href="/{$appName}/login?lang={$lang}">{$headLabel['login']}</a>
      {/if}
    </div>

    <!-- 連絡先 -->
    <div class="rightItem {if $rtl}rtl{/if}">
      <span>  {$headLabel['contact']}:</span><a href="https://twitter.com/mima_ita">mima_ita</a>
    </div>
  </div>
</div> 

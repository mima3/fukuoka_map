/**
 * ヘッダのスクリプト
 */
var header = (function() {
  var params = util.getQueryParam();
  var lang;
  var appStore;

  /**
   * 初期化処理
   */
  function _initialize() {
    appStore = store.get(getAppName());
    if (!appStore) {
      appStore = {};
    }

    // 言語情報の取得
    if (params['lang']) {
      lang = params['lang'];
    } else {
      // ローカルストレージからLANG取得
      lang = appStore.lang;
      if (lang) {
        var url = window.location;
        if (params.length == 0) {
          url = '?lang=' + lang;
        } else {
          url = '&lang=' + lang;
        }
        window.location = url;
      }
    }
    var submenu = $('#header').find('.submenu');
    submenu.hover(function() {
      var item = $(this).find('.submenuItem');
      item.slideDown(200);
    },function() {
      var item = $(this).find('.submenuItem');
      item.hide();
    });
    // ツールチップの表示
    $('#header').find('.tooltip').tooltipster({
      contentAsHTML: true,
      interactive: true
    });
    //

    /* 固定ヘッダはモバイルで安定しないので却下。
    // #を含むリンクの場合、固定ヘッダを考慮した位置に移動する
    // (外部リンク用）
    if (window.location.hash.match(/^#/)) {
        var href= window.location.hash;
        href = href.replace(/\./g,'\\.');
        href = href.replace(/\:/g,'\\:');
        var target = $(href == "#" || href == "" ? 'html' : href);
        var position = target.offset().top- ($('#header').height()+10); //ヘッダの高さ分位置をずらす
        $("html, body").animate({scrollTop:position}, 550, "swing");
    }

    // #を含むリンクの場合、固定ヘッダを考慮した位置に移動する
    // (内部リンク用）
    $('a[href^=#]').click(function(){
        var href= $(this).attr("href");
        href = href.replace(/\./g,'\\.');
        href = href.replace(/\:/g,'\\:');
        var target = $(href == "#" || href == "" ? 'html' : href);
        var position = target.offset().top - ($('#header').height()+10); //ヘッダの高さ分位置をずらす
        $("html, body").animate({scrollTop:position}, 550, "swing");
        return false;
    })
    */

    // 言語選択時の処理
    $('#langSelect').select2({
      width: 150 ,
      dropdownAutoWidth: true
    });

    $('#langSelect').change(function() {
      lang = $('#langSelect').val();
      appStore.lang = lang;
      // ローカルストレージの保存
      _saveAppStore();
      var url = window.location.protocol + '//' + window.location.host + window.location.pathname;
      var i = 0;
      for (var prop in params) {
        console.log(prop);
        if (prop == 'lang') {
          continue;
        }
        if (i == 0) {
          url += '?' + prop + '=' + params[prop];
        } else {
          url += '&' + prop + '=' + params[prop];
        }
        ++i;
      }
      if (i == 0) {
        url += '?lang=' + lang;
      } else {
        url += '&lang=' + lang;
      }
      window.location = url;
    }).keyup(function() {
      $(this).blur().focus();
    });
  }

  /**
   * 選択中の言語を取得
   */
  function _getLang() {
    return lang;
  }

  function _getAppStore() {
    return appStore;
  }

  function _saveAppStore() {
      try {
        store.set(getAppName(), appStore);
      } catch (e) {
        // 保存できなくても続行
      }
  }

  return {
      initialize: _initialize,
      getLang: _getLang,
      getAppStore: _getAppStore,
      saveAppStore: _saveAppStore
  };
})();

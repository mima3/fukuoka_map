$(function() {
  header.initialize();
  var appStore = header.getAppStore();
  if (!appStore.historySql) {
    appStore.historySql = [];
  }

  var historySql = appStore.historySql;
  var maxHistory = 30;

  function updateHistoryList() {
    $('#lstHistory').empty();
    var displist = [];
    for (var i = 0; i < historySql.length; ++i) {
      displist.push(util.escapeHTML(historySql[i]));
    }
    var html = $('#tmplHistory').render(displist);
    $('#lstHistory').html(html);
  }
  updateHistoryList();


  $('#lstHistory').change(function() {
    var ix = $('#lstHistory').prop("selectedIndex");
    var sql = historySql[ix];
    console.log(sql);
    if (!sql) {
      return;
    }
    $('#sql').val(sql);
  });


  /**
   * doSPARQLを実行する
   */
  $('#runSPARQL').button().click(function () {
    $('#error_area').empty();
    $('#res').empty();
    var sql = $('#sql').val();
    var ix = historySql.indexOf(sql);
    if (ix == -1) {
      historySql.splice(0, 0, sql);
    } else {
      historySql.splice(ix, 1);
      historySql.splice(0, 0, sql);
    }
    if (historySql.length > maxHistory) {
      historySql.splice(maxHistory, historySql.length - maxHistory);
    }
    header.saveAppStore();
    updateHistoryList();

    $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
    $.post(
      "https://teapot-api.bodic.org/api/v1/sparql",
      {
        query: sql
      },
      function (res) {
        $.unblockUI();
        console.log(res);
        var html = $('#tmplSpaqlRes').render(res);
        $('#res').append(html);
      },
      "json"
    ).error(function(e){
       $.unblockUI();
       console.log("Error: " , e);
       $('#error_area').text(e.responseText);
       $('#error_area').html(util.escapeHTML(e.responseText).replace(/\n/g, '<BR>'));
    });
  });

  /**
   * 名前空間閲覧
   */
  $('#getPrefixes').button().click(function () {
    $('#error_area').empty();
    $('#res').empty();
    var sql = $('#sql').val();
    $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
    $.get(
      "https://teapot-api.bodic.org/api/teapot/prefixes",
      {},
      function (res) {
        $.unblockUI();
        var param = {data:res['@prefixes']};
        var html = $('#tmplPrefixesRes').render(param);
        $('#res').append(html);
      },
      "json"
    ).error(function(e){
       $.unblockUI();
       console.log("Error: " , e);
       $('#error_area').text(e.responseText);
       $('#error_area').html(util.escapeHTML(e.responseText).replace(/\n/g, '<BR>'));
    });
  });

  /**
   * 語彙情報閲覧
   */
  $('#getVocabularies').button().click(function () {
    $('#error_area').empty();
    $('#res').empty();
    var sql = $('#sql').val();
    $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
    $.get(
      "https://teapot-api.bodic.org/api/teapot/vocabularies",
      {},
      function (res) {
        $.unblockUI();
        var param = {data:res['@vocabularies']};
        var html = $('#tmplVocabularies').render(param);
        $('#res').append(html);
      },
      "json"
    ).error(function(e){
       $.unblockUI();
       console.log("Error: " , e);
       $('#error_area').text(e.responseText);
       $('#error_area').html(util.escapeHTML(e.responseText).replace(/\n/g, '<BR>'));
    });
  });
});


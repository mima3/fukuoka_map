/**
 * 翻訳情報修正履歴画面用のスクリプト
 */
$(function() {
  jQuery(document).ready(function()
  {
    // ヘッダーの初期化
    header.initialize();

    var lastsel;
    jQuery('#transtb').jqGrid({
      url: '/' + getAppName() + '/json/get_translation_log',
      datatype: 'json',
      search: {
        groupOps: [{ op: 'AND'}]
      },
      colNames: ['更新日', '作成者', '言語', '翻訳元', '変更前', '変更後'],
      colModel: [
        {
          name: 'updated',
          search: false,
          width: 120,
          sortable: false
        },{
          name: 'author',
          sortable: false,
          width: 60,
          searchoptions: {
            sopt: ['eq']
          }
        },{
          name: 'lang',
          sortable: false,
          width: 30,
          searchoptions: {
            sopt: ['eq']
          }
        },{
          name: 'src',
          sortable: false,
          width: 200,
          searchoptions: {
            sopt: ['in']
          }
        },{
          name: 'previous',
          search: false,
          sortable: false,
          width: 200
        },{
          name: 'after',
          search: false,
          sortable: false,
          width: 200,
          search: false
        }
      ],
      multiselect: false,
      height: '100%',
      caption: '履歴',
      pager: '#pagertb'
    });
    jQuery('#transtb').jqGrid(
      'navGrid',
      '#pagertb',
      {del: false, add: false, edit: false},
      {},
      {},
      {},
      {
        multipleSearch: true,
        beforeShowSearch: function($form) {
          //http://stackoverflow.com/questions/6116402/remove-search-operator-and-or-in-multiplesearch-jqgrid
          var searchDialog = $form[0];
          if (!searchDialog) return true;
          var oldrReDraw = searchDialog.reDraw; // save the original reDraw method
          var doWhatWeNeed = function() {
            // hide the AND/OR operation selection
            $('select.opsel', searchDialog).hide();

            setTimeout(function() {
              // set fucus in the last input field
              $('input[type="text"]:last', searchDialog).focus();
            }, 50);
          };
          searchDialog.reDraw = function() {
              oldrReDraw.call(searchDialog);    // call the original reDraw method
              doWhatWeNeed();
          };
          doWhatWeNeed();
          return true;
        }
      }
    ).jqGrid('gridResize');
  });
});

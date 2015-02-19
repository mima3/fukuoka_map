/**
 * 翻訳情報修正画面用のスクリプト
 */
$(function() {
  jQuery(document).ready(function()
  {
    // ヘッダーの初期化
    header.initialize();

    var lastsel;
    jQuery('#transtb').jqGrid({
      url: '/' + getAppName() + '/json/get_translation',
      datatype: 'json',
      search: {
        groupOps: [{ op: 'AND'}]
      },
      colNames: ['ID', '言語', '翻訳元', '翻訳後', '更新日', '作成者'],
      colModel: [
        {
          name: 'id',
          search: false,
          width: 30,
          sortable: false
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
          width: 300,
          searchoptions: {
            sopt: ['in']
          }
        },{
          name: 'result',
          sortable: false,
          editable: true,
          edittype: 'textarea',
          width: 300,
          searchoptions: {
            sopt: ['in']
          }
        },{
          name: 'updated',
          sortable: false,
          width: 120,
          search: false
        },{
          name: 'author',
          sortable: false,
          width: 50,
          searchoptions: {
            sopt: ['eq']
          }
        }
      ],
      multiselect: false,
      height: '100%',
      caption: '翻訳内容',
      pager: '#pagertb'
    }).jqGrid('gridResize');
    jQuery('#transtb').jqGrid(
      'navGrid',
      '#pagertb',
      {del: false, add: false, edit: true},
      {
        reloadAfterSubmit: true,
        url: '/' + getAppName() + '/json/set_translation'
      },
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
    );
  });
});

/**
 * https://github.com/kmasaya/jQuery-mobile-anchor-js
 * これに、同一HTML中の別ページに遷移する機能を追加
 * <a href="#" data-page="#barrierfree" data-anchor="#hoge">
 * ex. barrierfreeページの#hogeに移動
 */
(function($){
  $.widget( "mobile.anchor", $.mobile.widget, {
  options: {
    debug: false,
    speed: null,
    duration: null,
    easing: null,
    offset: 0,

    setting: {
    "duration": {
      silent: 0,
      normal: 1200,
      slow: 2000,
      fast: 600
    },
    easing: [
      "linear"
    ]
    }
  },

  _create: function(){
    var self = this;
    var remote = false;
    var targetid = null;
    var movePage = false;
    $('div[data-role="page"]').on("pageshow",function(event) {
      if (movePage) {
        if ($(".ui-page-active "+targetid).size() > 0) {
           var target = $(".ui-page-active "+targetid).offset().top;
           console.log(target);
           self.scroll( target, remote);
           return false;
         } else {
           console.log(targetid + "... Not found");
         }
      }
      movePage = false;
    });
    $('a[data-anchor]').on( 'click', function(){
      var href = $(this).attr("href");
      targetPage = $(this).data("page");
      targetid = $(this).data("anchor");
      targetid = targetid.replace(/\:/g, '\\:');
      targetid = targetid.replace(/\./g, '\\.');
      console.log(targetid);
      if (targetPage) {
        movePage = true;
        $(':mobile-pagecontainer').pagecontainer('change', targetPage, { transition: 'slidedown'});
        return false;
      } else {
        if( href === "#"){
          var target = $(".ui-page-active "+targetid).offset().top;
          self.scroll( target, remote);
          return false;
        } else{
          remote = true;
          return true;
        }
      }
    });

    $("div").on( "pageshow", function(){
      if( remote){
        var target = $(".ui-page-active "+targetid).offset().top;
        self.scroll( target, remote);
      }

      remote = false;
    });
  },

  debug: function( debug_message){
    if( this.options.debug){
      console.log( debug_message);
    }
  },

  scroll: function( target, remote){
    var self = this;
    var current = $(window).scrollTop();
    var space;
    var speed = self.options.speed;
    var duration = self.options.duration;
    var easing = self.options.easing;
    var offset = self.options.offset;
    var mode;

    self.debug( "--- setting ---");
    self.debug( "speed : "+speed);
    self.debug( "duration : "+duration);
    self.debug( "easing : "+easing);
    self.debug( "offset : "+offset);
    self.debug( "remote : "+remote);
    self.debug( "------");

    self.debug( "target : "+target);
    self.debug( "current : "+current);

    if( speed !== null){
      space = speed;
    } else if( duration !== null){
      duration = ( isNaN( duration)) ? self.options.setting.duration[duration] : duration;
      self.debug( "duration : "+duration);
      space = Math.abs( parseInt( ( ( current - target) / duration) * 10));
    } else{
      space = 0;
    }

    self.debug( "space : "+space);
    if (parseInt(current) == parseInt(target)) {
      // 同じ場所なので動かさない
      return;
    }
    else if( current > target){
      mode = "up";
    } else{
      mode = "down";
    }

    self.debug( "mode : "+mode);

    if( space == 0){
      $.mobile.silentScroll( target - offset);
      return;
    }

    var timer = setInterval( function(){
      function stop(){
        $.mobile.silentScroll( target);
        clearInterval( timer);
      }
      if( mode === "up"){
        current -= space;
        if( target > current){ stop();}
      } else{
        current += space;
        if( target < current){ stop();}
      }

      $.mobile.silentScroll( current - offset);
    }, 10);
  }

  });
})(jQuery);
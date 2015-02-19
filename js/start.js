/**
 * スタート画面のスクリプト
 */
$(function() {
  $(document).ready(function() {
    header.initialize();
    $('.bxslider').bxSlider({
      mode: 'fade',
      captions: true
    });
  });
});

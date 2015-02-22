/**
 * スタート画面のスクリプト
 */
$(function() {
  $(document).ready(function() {
    header.initialize();
    var mapCtrl = new MapCtrl('map_canvas', 
      header.getAppStore(),
      function() {
        header.saveAppStore();
      }
    );

    var map = mapCtrl.getMapObj();
    var markerList = [];

    $('#selMedicalSubject').select2({
      width: 'resolve' ,
      dropdownAutoWidth: true
    });

    /**
     * Startマークを中央に
     */
    $('#btnCurPosCenter').button().click(function() {
      mapCtrl.moveStartMarkerToCenter();
    });

    /**
     * 現在位置にスタートマーカを移動する.
     */
    $('#btnCurPos').button().click(function() {
      mapCtrl.moveStartMarkerToCurPos();
    });

    function createMarker(key, obj) {
      var marker;
      if (obj['http://teapot.bodic.org/predicate/緯度'] &&
          obj['http://teapot.bodic.org/predicate/経度']) {
        var lat = obj['http://teapot.bodic.org/predicate/緯度'].value;
        var long = obj['http://teapot.bodic.org/predicate/経度'].value;
        var pos = new google.maps.LatLng(parseFloat(lat), parseFloat(long));
        marker = new google.maps.Marker({
          icon : '/' + getAppName() + '/img/hospital.png'
        });
        marker.setPosition(pos);
        // マーカーのクリックイベント
        google.maps.event.addListener(marker, "click", function() {
          var tmp = key.split('/');
          var prefix = 'route_' + tmp[tmp.length-1];
          console.log(obj);
          var c = $('#tmplInfoWindow').render({prefix:prefix, data:obj});

          var infowindow = new google.maps.InfoWindow({
            content: c
          });
          infowindow.open(map, marker);

          // infowindowが表示された場合に発火されるイベント
          google.maps.event.addListener(infowindow, 'domready', function(){
            $('#btnClose_' + prefix).button().click(function() {
              infowindow.close();
            });
            $('#btn_' + prefix).button().click(function() {
              console.log('click', key);
              mapCtrl.findRoute(pos, function(err, res) {
                if (err) {
                  $.msgBox({
                    title: "Error",
                    content: err,
                    type: "error",
                    buttons: [{ value: "Ok" }],
                  });
                  return;
                }
                $('#legs_' + prefix).empty();
                var legs = res.routes[0].legs[0];
                $('#legs_' + prefix).html('<p>' + legs.distance.text + ' '  + legs.duration.text + '</p>');
              });
            });
          });
        });
      }
      return marker;
    }

    // 病院の検索ボタン
    $('#btnSearch').button().click(function() {
      // マーカーの削除
      for (var i = 0; i < markerList.length; ++i) {
        markerList[i].setMap(null);
      }

      var sel = $('#selMedicalSubject').val();
      if (!sel) {
        return;
      }
      util.getJson(
        '/' + getAppName() + '/json/get_hospital',
        {
          lang: header.getLang(),
          medical_subjects: sel,
        },
        function(err, data) {
          console.log(err, data);
          if (err) {
            $.msgBox({
              title: "Error",
              content: err,
              type: "error",
              buttons: [{ value: "Ok" }],
            });
            return;
          }
          facilities = teapot.convertObject(data);
          for (var key in facilities) {
            var marker = createMarker(key, facilities[key]);
            if (marker) {
              marker.setMap(map);
              markerList.push(marker);
            } else {
              console.log('座標情報なし' , key);
            }
          }
        },
        function() {
          $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
        },
        function() {
          $.unblockUI();
        }
      );

    });
  });
});

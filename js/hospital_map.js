/**
 * スタート画面のスクリプト
 */
$(function() {
  $(document).ready(function() {
    header.initialize();
    var latlng = new google.maps.LatLng(33.6015669, 130.395785);
    var opts = {
      zoom: 11,
      center: latlng,
      //scrollwheel: false,
      //disableDoubleClickZoom: true,
      //scaleControl: false,
      //zoomControl : false,
      //streetViewControl : false,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"), opts);
    var markerList = [];
    var startMarker = new google.maps.Marker({
      position : new google.maps.LatLng(33.6015669, 130.395785),
      title: "Start",
      draggable : true,
      icon : '/' + getAppName() + '/img/start_marker.png'
    });
    startMarker.setMap(map);

    rendererOptions = {
      draggable: false,
      preserveViewport:false,
      markerOptions : {
        visible : false
      }
    };
    var directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
    var directionsService = new google.maps.DirectionsService();
    directionsDisplay.setMap(map);

    // GoogleMapの表示位置変更
    google.maps.event.addListener(map, 'bounds_changed', function() {
      /* 使いづらい
      var latlngBounds = map.getBounds();
      var swLatlng = latlngBounds.getSouthWest();
      var swlat = swLatlng.lat();
      var swlng = swLatlng.lng();
      var neLatlng = latlngBounds.getNorthEast();
      var nelat = neLatlng.lat();
      var nelng = neLatlng.lng();
      var mkLat = startMarker.position.lat();
      var mkLng = startMarker.position.lng();
      console.log(swlat , mkLat , nelat, swlng, mkLng, nelng);
      if (swlat <= mkLat && mkLat <=nelat &&
          swlng <= mkLng && mkLng <=nelng) {
      } else {
        // 範囲外
        startMarker.setPosition(latlngBounds.getCenter());
      }
      */
    });

    $('#selMedicalSubject').select2({
      width: '500px' ,
      dropdownAutoWidth: true
    });

    $('#btnCurPosCenter').button().click(function(){
      var latlngBounds = map.getBounds();
      startMarker.setPosition(latlngBounds.getCenter());
    });
    $('#btnCurPos').button().click(function(){
      // 現在地を取得する.
      if (!navigator.geolocation) {
        $.msgBox({
          title: "Error",
          content: 'Not support geolocation.',
          type: "error",
          buttons: [{ value: "Ok" }],
        });
        return;
      }
      navigator.geolocation.getCurrentPosition(
        function(position) {
          var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
          map.panTo(pos);
          startMarker.setPosition(pos);
        } , 
        function(error) {
          $.msgBox({
            title: "Error",
            content: error.message,
            type: "error",
            buttons: [{ value: "Ok" }],
          });
        }
      );
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
              var request = {
                origin: startMarker.position,
                destination: pos,
                travelMode: google.maps.DirectionsTravelMode.DRIVING,
                unitSystem: google.maps.DirectionsUnitSystem.METRIC,
                optimizeWaypoints: true,
                avoidHighways: false,
                avoidTolls: false
              };
              directionsService.route(request, function(response, status){
                console.log('directionsService', response, status);
                if (status != google.maps.DirectionsStatus.OK){
                  console.log(status);
                  $('#error_area').html(util.escapeHTML('directionService:'+ status).replace(/\n/g, '<BR>'));
                  return;
                }
                directionsDisplay.setDirections(response);
                $('#legs_' + prefix).empty();
                var legs = response.routes[0].legs[0];
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

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
    var dataFeatures = [];
    var dataMarkers = [];

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

    var waterDepthDict = {
      11: {name:'0～0.5ｍ未満', color:'#ccccff'},
      12: {name:'0.5～1.0ｍ未満', color:'#aaaaff'},
      13: {name:'1.0～2.0ｍ未満', color:'#8888ff'},
      14: {name:'2.0～5.0ｍ未満', color:'#4444ff'},
      15: {name:'5.0ｍ以上', color:'#0000ff'},

      21: {name:'0～0.5ｍ未満', color:'#ccccff'},
      22: {name:'0.5～1.0ｍ未満', color:'#aaaaff'},
      23: {name:'1.0～2.0ｍ未満', color:'#8888ff'},
      24: {name:'2.0～3.0ｍ未満', color:'#6666ff'},
      25: {name:'3.0～4.0ｍ未満', color:'#4444ff'},
      26: {name:'4.0～5.0ｍ未満', color:'#2222ff'},
      27: {name:'5.0ｍ以上', color:'#0000ff'}
    };
    var sedimentTypeDict = {
      1:{name:'土石流危険渓流', color:'#ff0000'},
      2:{name:'土石流危険区域', color:'#ff0000'},
      3:{name:'土石流危険渓流主流路', color:'#ff0000'},
      4:{name:'土石流危険渓流氾濫開始点', color:'#ff0000'},
      5:{name:'急傾斜地崩壊危険箇所', color:'#FFA500'},
      6:{name:'急傾斜地崩壊危険区域', color:'#FFA500'},
      7:{name:'地すべり危険箇所', color:'#A52A2A'},
      8:{name:'地すべり危険区域', color:'#A52A2A'},
      9:{name:'地すべり氾濫区域', color:'#A52A2A'},
      10:{name:'地すべり堪水域', color:'#FFA500'},
      11:{name:'雪崩危険箇所', color:'#0000ff'}
    };

    // GoogleMapの表示位置変更
    google.maps.event.addListener(map, 'bounds_changed', function() {
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
      /*
      if (swlat <= mkLat && mkLat <=nelat &&
          swlng <= mkLng && mkLng <=nelng) {
      } else {
        // 範囲外
        startMarker.setPosition(latlngBounds.getCenter());
      }
      */
    });

    /**
     * 地図をクリック
     */
    map.data.addListener('click', function(e) {
      console.log(e.feature);
      var val = $('#selDisasterData').val();
      if (!val) {
        return;
      }
      if (val == 'flood_data') {
        console.log(e.feature.getProperty('waterDepth'));
        return;
      }
      /*
      alert(areaType[e.feature.getProperty('hazardAreaType')].name + '\n' + e.feature.getProperty('remarks'));
      console.log(areaType[e.feature.getProperty('hazardAreaType')].name, e.feature.getProperty('remarks'));
      */
    });

    $('#selDisasterData').select2({
      width: '300px' ,
      dropdownAutoWidth: true
    });

    /**
     * 災害データの表示切替
     */
    $('#selDisasterData').change(function() {
      var val = $('#selDisasterData').val();
      for (var i = 0; i < dataFeatures.length; i++) {
        map.data.remove(dataFeatures[i]);
      }
      for (var i = 0; i < dataMarkers.length; ++i) {
        dataMarkers[i].setMap(null);
      }
      map.data.setStyle(null);
      dataFeatures = [];
      dataMarkers = [];
      var latlngBounds = map.getBounds();
      var swLatlng = latlngBounds.getSouthWest();
      var swlat = swLatlng.lat();
      var swlng = swLatlng.lng();
      var neLatlng = latlngBounds.getNorthEast();
      var nelat = neLatlng.lat();
      var nelng = neLatlng.lng();

      // 土砂災害用
      var styleSedimentFeature = function() {
        return function(feature) {
          return {
            strokeWeight : 0.2,
            strokeColor : sedimentTypeDict[feature.getProperty('hazardAreaType')].color,
            fillColor: sedimentTypeDict[feature.getProperty('hazardAreaType')].color,
            fillOpacity: 0.5
          };
        };
      };

      /**
       * 土砂災害危険個所（面）
       */
      function getSedimentDisasterHazardAreaSurface(callback) {
        util.getJson(
          '/' + getAppName() + '/json/get_sediment_disaster_hazard_area',
          {
            swlat : swlat,
            swlng : swlng,
            nelat : nelat,
            nelng : nelng,
            lang: header.getLang()
          },
          function(err, data) {
            console.log('getSedimentDisasterHazardAreaSurface', err, data);
            if (err) {
              callback(err, null);
              return;
            }
            dataFeatures = dataFeatures.concat(map.data.addGeoJson(data));
            map.data.setStyle(styleSedimentFeature());
            callback(null, null);
          },
          function() {
            // 実行前
          },
          function() {
            // 終了時
          }
        );
      }

      /**
       * 土砂災害危険個所（線）
       */
      function getSedimentDisasterHazardAreaLine(callback) {
        $.get(
          '/kokudo/json/get_sediment_disaster_hazard_area_line_by_geometry',
          {
            swlat : swlat,
            swlng : swlng,
            nelat : nelat,
            nelng : nelng,
            lang: header.getLang()
          },
          function (res) {
            console.log('getSedimentDisasterHazardAreaLine', res);
            dataFeatures = dataFeatures.concat(map.data.addGeoJson(res));
            map.data.setStyle(styleSedimentFeature());
            callback(null, null);
          },
          'json'
        ).error(function(e){
           callback(e.responseText, null);
        });
      }

      /**
       * 土砂災害危険個所（点）
       */
      function getSedimentDisasterHazardAreaPoint(callback) {
        $.get(
          '/kokudo/json/get_sediment_disaster_hazard_area_point_by_geometry',
          {
            swlat : swlat,
            swlng : swlng,
            nelat : nelat,
            nelng : nelng,
            lang: header.getLang()
          },
          function (res) {
            console.log('getSedimentDisasterHazardAreaPoint', res);
            res.features.forEach(function(feature) {
              var marker = new google.maps.Marker();
              marker.setPosition(
                new google.maps.LatLng(
                  feature.geometry.coordinates[1], feature.geometry.coordinates[0]
                )
              );
              marker.setMap(map);
              dataMarkers.push(marker);
              google.maps.event.addListener(marker, "click", function() {
                var infowindow = new google.maps.InfoWindow({
                  content: feature.properties.remarks + '<BR>' + sedimentTypeDict[feature.properties.hazardAreaType].name
                });
                infowindow.open(map, marker);
              });
            });
              
            //features = features.concat(map.data.addGeoJson(res));
            //map.data.setStyle(styleSedimentFeature());
            callback(null, null);
          },
          'json'
        ).error(function(e){
           callback(e.responseText, null);
        });
      }

      /**
       * 洪水想定区域の取得
       */
      function getExpectedFloodAarea(callback) {
        util.getJson(
          '/' + getAppName() + '/json/get_expected_flood_area',
          {
            swlat : swlat,
            swlng : swlng,
            nelat : nelat,
            nelng : nelng,
            lang: header.getLang()
          },
          function(err, data) {
            if (err) {
              callback(err, null);
              return;
            }
            dataFeatures = dataFeatures.concat(map.data.addGeoJson(data.geojson));
            attribute = data.attribute;

            var styleFeature = function() {
              return function(feature) {
                return {
                  strokeWeight : 0.2,
                  strokeColor : waterDepthDict[feature.getProperty('waterDepth')].color,
                  fillColor: waterDepthDict[feature.getProperty('waterDepth')].color,
                  fillOpacity: 0.5
                };
              };
            }
            map.data.setStyle(styleFeature());
            callback(null, null);
          },
          function() {
            // 実行前
          },
          function() {
            // 終了時
          }
        );
      }

      var tasksDict = {
        'flood_data' : [getExpectedFloodAarea],
        'gust_data' : null,
        'sediment_data' : [
          getSedimentDisasterHazardAreaSurface,
          //getSedimentDisasterHazardAreaLine,
          //getSedimentDisasterHazardAreaPoint
        ]
      };
      var tasks = tasksDict[val];
      if (!tasks) {
        return;
      }

      $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
      async.parallel(tasks, function(err, ret) {
        $.unblockUI();
        if (err) {
          $.msgBox({
            title: "Error",
            content: err,
            type: "error",
            buttons: [{ value: "Ok" }],
          });
          return;
        }
      });
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
    var iconDic = {
      '収容避難所' : '/' + getAppName() + '/img/shelter_home.png',
      '一時避難所' : '/' + getAppName() + '/img/shelter_temp.png',
      '地区避難場所' : '/' + getAppName() + '/img/shelter_area.png',
      '広域避難場所' : '/' + getAppName() + '/img/shelter_area.png'
    };
    function createMarker(key, obj) {
      var marker;
      if (obj['http://teapot.bodic.org/predicate/緯度'] &&
          obj['http://teapot.bodic.org/predicate/経度']) {
        var lat = obj['http://teapot.bodic.org/predicate/緯度'].value;
        var long = obj['http://teapot.bodic.org/predicate/経度'].value;
        var pos = new google.maps.LatLng(parseFloat(lat), parseFloat(long));
        var icon = iconDic[obj['http://teapot.bodic.org/predicate/避難所情報'].value];
        if (!icon) {
          console.log('notfoundicon', obj['http://teapot.bodic.org/predicate/避難所情報'].value);
        }
        marker = new google.maps.Marker({
          icon : icon
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
                  $.msgBox({
                    title: "Error",
                    content: 'directionService:'+ status,
                    type: "error",
                    buttons: [{ value: "Ok" }],
                  });
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

    /**
     * 避難所の情報を取得
     */
    function getShelter(callback) {
      util.getJson(
        '/' + getAppName() + '/json/get_shelter',
        {
          lang: header.getLang()
        },
        function(err, data) {
          callback(err, data);
        },
        function() {
          // 実行前
        },
        function() {
          // 終了時
        }
      );
    }
    var tasks = [
      getShelter
    ];
    $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
    async.parallel(tasks, function(err, res) {
      console.log(err, res);
      $.unblockUI();
      if (err) {
        $.msgBox({
          title: "Error",
          content: err,
          type: "error",
          buttons: [{ value: "Ok" }],
        });
        return;
      }
      facilities = teapot.convertObject(res[0]);
      for (var key in facilities) {
        var marker = createMarker(key, facilities[key]);
        if (marker) {
          marker.setMap(map);
          markerList.push(marker);
        } else {
          console.log('座標情報なし' , key);
        }
      }
    });
  });
});

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
    var dataFeatures = [];
    var facilities;


    var waterDepthDict = {
      11: {name:'0～0.5ｍ未満', strokeColor:'#ccccff', fillColor:'#ccccff'},
      12: {name:'0.5～1.0ｍ未満', strokeColor:'#aaaaff', fillColor:'#aaaaff'},
      13: {name:'1.0～2.0ｍ未満', strokeColor:'#8888ff', fillColor:'#8888ff'},
      14: {name:'2.0～5.0ｍ未満', strokeColor:'#4444ff', fillColor:'#4444ff'},
      15: {name:'5.0ｍ以上', strokeColor:'#0000ff', fillColor:'#0000ff'},

      21: {name:'0～0.5ｍ未満', strokeColor:'#ccccff', fillColor:'#ccccff'},
      22: {name:'0.5～1.0ｍ未満', strokeColor:'#aaaaff', fillColor:'#aaaaff'},
      23: {name:'1.0～2.0ｍ未満', strokeColor:'#8888ff', fillColor:'#8888ff'},
      24: {name:'2.0～3.0ｍ未満', strokeColor:'#6666ff', fillColor:'#6666ff'},
      25: {name:'3.0～4.0ｍ未満', strokeColor:'#4444ff', fillColor:'#4444ff'},
      26: {name:'4.0～5.0ｍ未満', strokeColor:'#2222ff', fillColor:'#2222ff'},
      27: {name:'5.0ｍ以上', strokeColor:'#0000ff', fillColor:'#0000ff'}
    };
    for (var k in waterDepthDict) {
      var t = $('#message').find('#waterDepth' + k).text();
      if (t) {
        var html = $('#tmplExample').render({color:waterDepthDict[k].fillColor, message:t});
        $('#example_flood_data').append(html);
      }
    }
    
    var sedimentTypeDict = {
      1:{name:'土石流危険渓流', strokeColor:'#000000', fillColor:'#ff8000'},
      2:{name:'土石流危険区域', strokeColor:'#000000', fillColor:'#ff00e4'},
      5:{name:'急傾斜地崩壊危険箇所', strokeColor:'#FFA500', fillColor:'#27104f'},
      6:{name:'急傾斜地崩壊危険区域', strokeColor:'#FFA500', fillColor:'#b491b2'},
      7:{name:'地すべり危険箇所', strokeColor:'#273c9c', fillColor:'#273c9c'},
      8:{name:'地すべり危険区域', strokeColor:'#b32e49', fillColor:'#b32e49'},
      9:{name:'地すべり氾濫区域', strokeColor:'#2dc8a9', fillColor:'#2dc8a9'},
      10:{name:'地すべり堪水域', strokeColor:'#35b24e', fillColor:'#35b24e'},
      11:{name:'雪崩危険箇所', strokeColor:'#000000', fillColor:'#e1e1e1'}
    };
    for (var k in sedimentTypeDict) {
      var t = $('#message').find('#sedimentType' + k).text();
      if (t) {
        var html = $('#tmplExample').render({color:sedimentTypeDict[k].fillColor, message:t});
        $('#example_sediment_data').append(html);
      }
    }

    /**
     * 地図をクリック
     */
    map.data.addListener('click', function(e) {
      var val = $('#selDisasterData').val();
      if (!val) {
        return;
      }
      if (val == 'flood_data') {
        console.log(e.feature.getProperty('waterDepth'));
        return;
      }
    });

    $('#selDisasterData').select2({
      width: 'resolve' ,
      dropdownAutoWidth: true
    });

    function formatSelShelterType(state) {
      if (!state.id) return state.text; // optgroup
      return "<img style='vertical-align: middle' class='flag' width='20' height='20' src='" + $(state.element[0]).attr('img') + "'/>" + state.text;
    }
    $('#selShelterType').select2({
      width: 'resolve' ,
      escapeMarkup: function(m) { return m; },
      formatResult: formatSelShelterType,
      formatSelection: formatSelShelterType,
      dropdownAutoWidth: true,
      
    });

    // 土砂災害用スタイルの関数
    var styleSedimentFeature = function() {
      return function(feature) {
        return {
          strokeWeight : 0.2,
          strokeColor : sedimentTypeDict[feature.getProperty('hazardAreaType')].strokeColor,
          fillColor: sedimentTypeDict[feature.getProperty('hazardAreaType')].fillColor,
          fillOpacity: 0.9
        };
      };
    };
    var styleExpectedFoodFeature = function() {
      return function(feature) {
        return {
          strokeWeight : 0.2,
          strokeColor : waterDepthDict[feature.getProperty('waterDepth')].strokeColor,
          fillColor: waterDepthDict[feature.getProperty('waterDepth')].fillColor,
          fillOpacity: 0.9
        };
      };
    };
    var dataFeatureCache = {
      'flood_data' : {cache:null, styleFnc:styleExpectedFoodFeature}, 
      'sediment_data' : {cache:null, styleFnc:styleSedimentFeature}
    };

    /**
     * 災害データの表示切替
     */
    $('#selDisasterData').change(function() {
      $('.example_area').hide();
      var val = $('#selDisasterData').val();
      $('#example_' + val).slideDown(200);
      for (var i = 0; i < dataFeatures.length; i++) {
        map.data.remove(dataFeatures[i]);
      }
      map.data.setStyle(null);
      dataFeatures = [];
      if (!dataFeatureCache[val]) {
        return;
      }

      if (dataFeatureCache[val].cache) {
        dataFeatures = dataFeatureCache[val].cache;
        map.data.setStyle(dataFeatureCache[val].styleFnc());
        for (var i = 0; i < dataFeatures.length; i++) {
          map.data.add(dataFeatures[i]);
        }
        return;
      }

      var latlngBounds = map.getBounds();
      var swLatlng = latlngBounds.getSouthWest();
      var swlat = swLatlng.lat();
      var swlng = swLatlng.lng();
      var neLatlng = latlngBounds.getNorthEast();
      var nelat = neLatlng.lat();
      var nelng = neLatlng.lng();

      /**
       * 土砂災害危険個所（面）
       */
      function createGetSedimentDisasterHazardAreaSurfaceFnc(swlat, swlng, nelat, nelng) {
        return function(callback) {
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
              dataFeatureCache[val].cache = dataFeatures;
              callback(null, null);
            },
            function() {
              // 実行前
            },
            function() {
              // 終了時
            }
          );
        };
      }

      /**
       * 洪水想定区域の取得
       */
      function createGetExpectedFloodAreaFnc(swlat, swlng, nelat, nelng) {
        return function(callback) {
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
              dataFeatureCache[val].cache = dataFeatures;

              map.data.setStyle(styleExpectedFoodFeature());
              callback(null, null);
            },
            function() {
              // 実行前
            },
            function() {
              // 終了時
            }
          );
        };
      }

      // 一気に大量のデータを実行するとサーバー側のメモリがパンクするので、こまめにわけてリクエストする.
      var tasksDict = {
        'flood_data' : [], //getExpectedFloodAarea],
        'gust_data' : [],
        'sediment_data' : []
      };
      var targetRange = [33.424957, 130.032166, 33.874405, 130.494919];
      var rangeDivCnt = 3;
      var perLat = (targetRange[2] - targetRange[0]) / rangeDivCnt;
      var perLng = (targetRange[3] - targetRange[1]) / rangeDivCnt;
      var postRange = [];
      var lat = targetRange[0];
      var lng = targetRange[1];
      for (var i = 0 ; i < rangeDivCnt; ++i) {
        lat = targetRange[0];
        for (var j = 0 ; j < rangeDivCnt; ++j) {
          var ix = j;
          if (lat <= nelat && nelat <= lat+perLat && lng <= nelng && nelng <= lng + perLng) {
            ix = 0;
          }
          tasksDict['flood_data'].splice(ix, 0,
            createGetExpectedFloodAreaFnc(lat, lng, lat+perLat, lng + perLng)
          )
          tasksDict['sediment_data'].splice(ix, 0,
            createGetSedimentDisasterHazardAreaSurfaceFnc(lat, lng, lat+perLat, lng + perLng)
          )
          lat = lat + perLat;
        }
        lng = lng + perLng;
      }
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

    /**
     * Startマークを中央に
     */
    $('#btnCurPosCenter').button().click(function(){
      mapCtrl.moveStartMarkerToCenter();
    });

    /**
     * 現在位置にスタートマーカを移動する.
     */
    $('#btnCurPos').button().click(function(){
      mapCtrl.moveStartMarkerToCurPos();
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

    /**
     * 避難所の情報を取得
     */
    function getShelter(callback) {
      var sel = $('#selShelterType').val();
      util.getJson(
        '/' + getAppName() + '/json/get_shelter',
        {
          shelter_type: sel,
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
    function getShelterBuilding(callback) {
      var sel = $('#selShelterType').val();
      util.getJson(
        '/' + getAppName() + '/json/get_shelter_building',
        {
          shelter_type: sel,
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
    function loadShelter() {
      for (var i = 0; i < markerList.length; ++i) {
        markerList[i].setMap(null);
      }
      markerList = [];

      var sel = $('#selShelterType').val();
      if (!sel) {
        return;
      }
      var tasks = [
        getShelter,
        getShelterBuilding
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
        var building = teapot.convertObject(res[1], null,  ['facility', 'building', 'p', 'o']);
        for (var k in building) {
          if (!facilities[k]) {
            continue;
          }
          for (var b in building[k]) {
            if (!facilities[k]['building']) {
              facilities[k]['building'] = {};
            }
            facilities[k]['building'][b] = building[k][b];
          }
        }

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
    }
    $('#selShelterType').change(function() {
      var val = $('#selShelterType').val();
      for (var i = 0; i < markerList.length; ++i) {
        markerList[i].setMap(null);
      }
      markerList = [];
      if (!val) {
        return;
      }
      for (var key in facilities) {
        if (val.indexOf(facilities[key]['http://teapot.bodic.org/predicate/避難所情報'].value) == -1) {
          continue;
        }
        var marker = createMarker(key, facilities[key]);
        if (marker) {
          marker.setMap(map);
          markerList.push(marker);
        } else {
          console.log('座標情報なし' , key);
        }
      }
    });
    loadShelter();
  });
});

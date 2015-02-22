var teapot_explore = (function() {
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

  /**
   * クラスツリーの描画
   */
  var selectedItems = [];

  $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
  teapot.buildClassesTree(function(err, res) {
    function createTree(node) {
      var ret = [];
      for (var key in node) {
        var dt = {
          key : key,
          title : res.classes[key]['http://www.w3.org/2000/01/rdf-schema#label']
        }
        if (Object.keys(node[key]).length) {
          dt.children = createTree(node[key]);
        }
        ret.push(dt);
      }
      return ret;
    }
    var treeData = createTree(res.tree['http://teapot.bodic.org/type/rootobject']);
    //$('#classtreeData').append(html);

    function updateSelected(select, node) {
      var ix = selectedItems.indexOf(node.data.key);
      if (node.childList) {
        for (var i = 0; i < node.childList.length; ++i) {
          updateSelected(select, node.childList[i]);
        }
      } 
      if (select) {
        if (ix == -1) {
          selectedItems.push(node.data.key);
        }
      } else {
        if (ix != -1) {
          selectedItems.splice(ix, 1);
        }
      }
    }

    /**
     * クエリーボタンの押下処理
     */
    var limit = 100;
    $('#btnQuery').button().click(function() {
      $('#res').empty();      var query = new teapot.Query();
      query = query.distinct();
      var offset = parseInt($('#txtPage').val()) * limit;
      var tasks = [];
      selectedItems.forEach(function(item) {
        tasks.push(function(callback) {
          query.columns(['?s', '?p', '?o']);
          query.where('?s', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>', '<' + item + '>')
               .where('?s', '?p', '?o')
               .filter('!isBlank(?o)')
               .filter('!isBlank(?s)')
               .union()
                 .where('?s', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>', '<' + item + '>')
                 .where('?s', '?p', '?a')
                 .where('?a', '?p', '?o')
                 .filter('isBlank(?a)')
                 .filter('!isBlank(?s)')
               .union()
                 .where('?x', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>', '<' + item + '>')
                 .where('?x', '?p', '?o')
                 .where('?s', '?p', '?x')
                 .filter('!isBlank(?o)')
                 .filter('isBlank(?x)')
               .union()
                 .where('?x', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>', '<' + item + '>')
                 .where('?x', '?p', '?a')
                 .where('?a', '?p', '?o')
                 .where('?x', '?p', '?a')
                 .where('?s', '?p', '?x')
                 .filter('isBlank(?a)')
                 .filter('!isBlank(?x)')
               .orderby('?s');
          query.executeSpilit(5000, function(err, res) {
            callback(err, res);
          });
        });
      });

      $.blockUI({ message: '<img src="/' + getAppName() + '/img/loading.gif" />' });
      $('#error_area').empty();
      async.parallel(tasks, function(err, res) {
        console.log(err, res);
        $.unblockUI();
        if (err) {
          $('#error_area').html(util.escapeHTML(err).replace(/\n/g, '<BR>'));
          return;
        }
        // マーカーの削除
        for (var i = 0; i < markerList.length; ++i) {
          markerList[i].setMap(null);
        }
        var objects = {};
        for (var i = 0; i < res.length; ++i) {
          console.log(res[i]);
          var html = $('#tmplSpaqlRes').render(res[i]);
          $('#res').append(html);
          objects = teapot.convertObject(res[i], objects);
        }
        function createMarker(key, obj) {
          if (obj['http://teapot.bodic.org/predicate/緯度'] &&
              obj['http://teapot.bodic.org/predicate/経度']) {
            var lat = obj['http://teapot.bodic.org/predicate/緯度'].value;
            var long = obj['http://teapot.bodic.org/predicate/経度'].value;
            var pos = new google.maps.LatLng(parseFloat(lat), parseFloat(long));
            var marker = new google.maps.Marker();
            marker.setPosition(pos);

            // マーカーのクリックイベント
            google.maps.event.addListener(marker, "click", function() {
              var tmp = key.split('/');
              var prefix = 'route_' + tmp[tmp.length-1];
              var c = $('#tmplInfoWindow').render({prefix:prefix, data:obj});

              var infowindow = new google.maps.InfoWindow({
                content: c
              });

              infowindow.open(map, marker);

              // infowindowが表示された場合に発火されるイベント
              google.maps.event.addListener(infowindow, 'domready', function(){
                console.log('show!');
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

            return marker;
          }
          return null;
        }
        for (var key in objects) {
          var marker = createMarker(key, objects[key]);
          if (marker) {
            marker.setMap(map);
            markerList.push(marker);
          } else {
            console.log('座標情報なし' , key);
          }
        }
      });
    });


    $('#classtree').dynatree({
      // using default options
      checkbox: true,
      children: treeData,
      selectMode: 3,
      onQuerySelect: function(select, node) {
        // 選択前に実行。falseを返すと選択をキャンセル
        return true;
      },
      onSelect: function(select, node) {
        console.log('onSelect', select, node);
        updateSelected(select, node);
      }
    });

    $.unblockUI();
  });
})();
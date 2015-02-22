/**
 * GoogleMapの共通処理
 */
var MapCtrl = (function() {
  var inst = function(divName, appStore, saveAppStoreFnc) {
    this._appStore = appStore;
    this._saveAppStoreFnc = saveAppStoreFnc;
    if (!this._appStore.mapLocate) {
      this._appStore.mapLocate = {
        lat: 33.6015669,
        lng: 130.395785,
        zoom: 11,
        startLat: 33.6015669,
        startLng: 130.395785
      };
    }
    var latlng = new google.maps.LatLng(this._appStore.mapLocate.lat, this._appStore.mapLocate.lng);
    var opts = {
      zoom: this._appStore.mapLocate.zoom,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    this._map = new google.maps.Map(document.getElementById(divName), opts);

    this._startMarker = new google.maps.Marker({
      position: new google.maps.LatLng(this._appStore.mapLocate.startLat, this._appStore.mapLocate.startLng),
      title: 'Start',
      draggable: true,
      icon: '/' + getAppName() + '/img/start_marker.png'
    });
    this._startMarker.setMap(this._map);

    rendererOptions = {
      draggable: false,
      preserveViewport: false,
      markerOptions: {
        visible: false
      }
    };
    this._directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
    this._directionsDisplay.setMap(this._map);

    this._directionsService = new google.maps.DirectionsService();
    var self = this;
    // GoogleMapの表示位置変更
    google.maps.event.addListener(this._map, 'bounds_changed', function() {
      var latlngBounds = self._map.getBounds();
      var center = latlngBounds.getCenter();
      if (self._appStore.mapLocate) {
        self._appStore.mapLocate.lat = center.lat();
        self._appStore.mapLocate.lng = center.lng();
        self._appStore.mapLocate.zoom = self._map.zoom;
        self._saveAppStoreFnc();
      }
    });

    // スタートマーカの位置変更
    google.maps.event.addListener(this._startMarker, 'position_changed', function() {
      if (self._appStore.mapLocate) {
        self._appStore.mapLocate.startLat = self._startMarker.position.lat();
        self._appStore.mapLocate.startLng = self._startMarker.position.lng();
        self._saveAppStoreFnc();
      }
    });
  };

  var p = inst.prototype;
  p.getMapObj = function(cols) {
    return this._map;
  };

  /**
   * スタートマーカーをマップ中央に持っていく
   */
  p.moveStartMarkerToCenter = function() {
    var latlngBounds = this._map.getBounds();
    var center = latlngBounds.getCenter();
    this._startMarker.setPosition(center);
  };

  /**
   * スタートマーカーを現在位置に持っていく
   */
  p.moveStartMarkerToCurPos = function() {
    if (!navigator.geolocation) {
      $.msgBox({
        title: 'Error',
        content: 'Not support geolocation.',
        type: 'error',
        buttons: [{value: 'Ok'}]
      });
      return;
    }
    var self = this;
    navigator.geolocation.getCurrentPosition(
      function(position) {
        var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        self._map.panTo(pos);
        self._startMarker.setPosition(pos);
      },
      function(error) {
        $.msgBox({
          title: 'Error',
          content: error.message,
          type: 'error',
          buttons: [{value: 'Ok'}]
        });
      }
    );
  };

  /**
   * 開始マークから指定の位置へのルートを検索する
   */
  p.findRoute = function(pos, callback) {
    var request = {
      origin: this._startMarker.position,
      destination: pos,
      travelMode: google.maps.DirectionsTravelMode.DRIVING,
      unitSystem: google.maps.DirectionsUnitSystem.METRIC,
      optimizeWaypoints: true,
      avoidHighways: false,
      avoidTolls: false
    };

    var self = this;
    this._directionsService.route(request, function(response, status) {
      if (status != google.maps.DirectionsStatus.OK) {
        callback(status, null);
        return;
      }
      self._directionsDisplay.setDirections(response);
      callback(null, response);
    });
  };

  return inst;
})();

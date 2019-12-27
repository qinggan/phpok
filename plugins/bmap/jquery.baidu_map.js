! function(t) {
	var n = function(t) {
		this.init(t)
	};
	n.prototype = {
		init: function(n) {
			var i = {
				id: "",
				title: {
					text: "",
					className: ""
				},
				content: {
					text: [],
					className: ""
				},
				point: {
					lng: 0,
					lat: 0
				},
				type: [],
				level: 15,
				zoom: !1
			};
			if (this.opts = t.extend({}, i, n), !this.opts.id) throw "id不能为空";
			this.renderMap()
		},
		renderMap: function() {
			var t = this.opts,
				n = t.id,
				i = t.level || 15,
				o = this.map = new BMap.Map(n),
				e = this.point = new BMap.Point(t.point.lng, t.point.lat);
			o.centerAndZoom(e, i), this.addControl()
		},
		addControl: function() {
			var t = this.opts,
				n = this.map,
				i = t.type,
				o = t.zoom;
			if (n.addControl(new BMap.NavigationControl), n.addControl(new BMap.ScaleControl), n.addControl(new BMap.OverviewMapControl), o && n.enableScrollWheelZoom(!0), i.length > 0) {
				var e = this.getMapType(i);
				n.addControl(new BMap.MapTypeControl({
					mapTypes: e
				}))
			}
			this.createMarker()
		},
		getMapType: function(n) {
			var i = [],
				o = {
					"地图": BMAP_NORMAL_MAP,
					"卫星": BMAP_SATELLITE_MAP,
					"三维": BMAP_PERSPECTIVE_MAP
				};
			return t.each(n, function(t, n) {
				n in o && i.push(o[n])
			}), i
		},
		createMarker: function() {
			var t = this,
				n = this.map,
				i = this.point,
				o = new BMap.Marker(i),
				e = this.opts.icon;
			e && e.url && (o = new BMap.Marker(i, {
				icon: new BMap.Icon(e.url, new BMap.Size(e.width, e.height))
			})), n.addOverlay(o), this.renderInfoWindow(), o.addEventListener("click", function() {
				t.openInfoWindow()
			})
		},
		renderInfoWindow: function() {
			this.map, this.point;
			var n = this.opts,
				i = n.content.text,
				o = "";
			i.length > 0 && t.each(i, function(t, i) {
				o += '<div class="' + n.content.className + '">' + i + "</div>"
			});
			this.infoWindow = new BMap.InfoWindow(o, {
				width: n.width || 0,
				height: n.height || 0,
				title: '<div class="' + n.title.className + '">' + n.title.text + "</div>"
			});
			this.openInfoWindow()
		},
		openInfoWindow: function() {
			var t = this.map,
				n = this.point,
				i = this.infoWindow;
			i.isOpen() || t.openInfoWindow(i, n)
		}
	}, window.BaiduMap = n
}(jQuery);
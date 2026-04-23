@extends('layouts.templatemap')

@section('title', 'Peta | ' . config('app.name'))

@push('styles')
    {{-- Leaflet.js core CSS – map tiles, markers, popups, controls --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- Leaflet.draw CSS – styles for the drawing toolbar and drawn shapes (polygons, lines, markers, etc.) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .navbar {
            z-index: 1000;
        }

        #map {
            width: 100%;
            flex: 1;
        }

        /* Premium Popup Styling */
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            padding: 5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .leaflet-popup-content {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 10px;
            width: 220px !important;
        }

        .popup-title {
            font-weight: 700;
            font-size: 16px;
            color: #1e293b;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }

        .popup-desc {
            color: #64748b;
            margin-bottom: 10px;
        }

        .popup-img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .popup-img:hover {
            transform: scale(1.02);
        }

        .popup-meta {
            font-size: 11px;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        .popup-meta i {
            margin-right: 4px;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-delete:hover {
            background-color: #dc2626;
            color: white;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
    <div id="map"></div>

    {{-- Hidden Delete Form --}}
    <form id="delete-form" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Bootstrap Modal --}}
    <div class="modal" tabindex="-1" id="modalInputPoint">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Le Point Input</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('points.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Hidden field populated by JS with the WKT geometry of the placed marker --}}
                    <input type="hidden" id="geometry_point" name="geometry_point">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Le Point Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter point name">
                        </div>
                        <div class="mb-3">
                            <label for="descriptions" class="form-label">Le Descriptions</label>
                            <textarea class="form-control" id="descriptions" name="descriptions" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="formFile" class="form-label">Image</label>
                            <input class="form-control" type="file" id="formFile" name="image"
                                onchange="document.getElementById('preview-image-point').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                        <div class="mb-3">
                            <img src="" alt="" id="preview-image-point" class="img-thumbnail img-fluid"
                                style="max-height: 200px;">
                        </div>
                        <div class="mb-3">
                            <label for="geometry_display" class="form-label">Geometry (WKT)</label>
                            <textarea class="form-control font-monospace" id="geometry_display" rows="2"
                                readonly></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bootstrap Modal – Polyline Input --}}
    <div class="modal" tabindex="-1" id="modalInputPolyline">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Le Polyline Input</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('polylines.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Hidden field populated by JS with the WKT geometry of the drawn polyline --}}
                    <input type="hidden" id="geometry_polyline" name="geometry_polyline">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="polyline_name" class="form-label">Le Polyline Name</label>
                            <input type="text" class="form-control" id="polyline_name" name="name"
                                placeholder="Enter polyline name">
                        </div>
                        <div class="mb-3">
                            <label for="polyline_descriptions" class="form-label">Le Descriptions</label>
                            <textarea class="form-control" id="polyline_descriptions" name="descriptions"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="formFilePolyline" class="form-label">Image</label>
                            <input class="form-control" type="file" id="formFilePolyline" name="image"
                                onchange="document.getElementById('preview-image-polyline').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                        <div class="mb-3">
                            <img src="" alt="" id="preview-image-polyline" class="img-thumbnail img-fluid"
                                style="max-height: 200px;">
                        </div>
                        <div class="mb-3">
                            <label for="polyline_geometry_display" class="form-label">Geometry (WKT)</label>
                            <textarea class="form-control font-monospace" id="polyline_geometry_display" rows="2"
                                readonly></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bootstrap Modal – Polygon Input --}}
    <div class="modal" tabindex="-1" id="modalInputPolygon">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Le Polygon Input</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('polygons.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Hidden field populated by JS with the WKT geometry of the drawn polygon --}}
                    <input type="hidden" id="geometry_polygon" name="geometry_polygon">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="polygon_name" class="form-label">Le Polygon Name</label>
                            <input type="text" class="form-control" id="polygon_name" name="name"
                                placeholder="Enter polygon name">
                        </div>
                        <div class="mb-3">
                            <label for="polygon_descriptions" class="form-label">Le Descriptions</label>
                            <textarea class="form-control" id="polygon_descriptions" name="descriptions"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="formFilePolygon" class="form-label">Image</label>
                            <input class="form-control" type="file" id="formFilePolygon" name="image"
                                onchange="document.getElementById('preview-image-polygon').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                        <div class="mb-3">
                            <img src="" alt="" id="preview-image-polygon" class="img-thumbnail img-fluid"
                                style="max-height: 200px;">
                        </div>
                        <div class="mb-3">
                            <label for="polygon_geometry_display" class="form-label">Geometry (WKT)</label>
                            <textarea class="form-control font-monospace" id="polygon_geometry_display" rows="2"
                                readonly></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Leaflet.js core library --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- Leaflet.draw – adds drawing controls (polygon, polyline, rectangle, circle, marker) to the map --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

    {{-- Terraformer WKT – converts GeoJSON geometry to Well-Known Text (WKT) format --}}
    <script src="https://unpkg.com/@terraformer/wkt"></script>

    <script>
        const map = L.map('map').setView([-8.6529, 116.3242], 10);

        // Global function for deletion
        window.deleteFeature = function(url) {
            if (confirm('Are you sure you want to delete this feature?')) {
                const form = document.getElementById('delete-form');
                form.action = url;
                form.submit();
            }
        };

        // Base Maps
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        });

        var esri_world_imagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });

        var google_streets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google'
        });

        // Current Base Map
        osm.addTo(map);

        /* Digitizing (Draw Items) */
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: {
                position: 'topleft',
                polyline: true,
                polygon: true,
                rectangle: true,
                circle: false,
                marker: true,
                circlemarker: false
            },
            edit: false
        });

        map.addControl(drawControl);

        map.on('draw:created', function (e) {
            var type = e.layerType,
                layer = e.layer;

            console.log(type);

            var drawnJSONObject = layer.toGeoJSON();
            var objectGeometry = Terraformer.geojsonToWKT(drawnJSONObject.geometry);

            console.log(drawnJSONObject);
            console.log(objectGeometry);

            if (type === 'polyline') {
                console.log("Create " + type);
                $('#geometry_polyline').val(objectGeometry);
                $('#polyline_geometry_display').val(objectGeometry);
                $('#modalInputPolyline').modal('show');

                //modal dismiss reload page
                $('#modalInputPolyline').on('hidden.bs.modal', function () {
                    location.reload();
                });
            } else if (type === 'polygon' || type === 'rectangle') {
                console.log("Create " + type);
                $('#geometry_polygon').val(objectGeometry);
                $('#polygon_geometry_display').val(objectGeometry);
                $('#modalInputPolygon').modal('show');

                //modal dismiss reload page
                $('#modalInputPolygon').on('hidden.bs.modal', function () {
                    location.reload();
                });
            } else if (type === 'marker') {
                console.log("Create " + type);
                // Populate both the hidden field (for submit) and the visible display field
                $('#geometry_point').val(objectGeometry);
                $('#geometry_display').val(objectGeometry);
                $('#modalInputPoint').modal('show');

                //modal dismiss reload page
                $('#modalInputPoint').on('hidden.bs.modal', function () {
                    location.reload();
                });
            } else {
                console.log('__undefined__');
            }

            drawnItems.addLayer(layer);
        });

        /* GeoJSON Points */
        var pointsLayer = L.geoJSON(null, {
            onEachFeature: function (feature, layer) {
                var popup_content = "<div class='popup-title'>" + feature.properties.name + "</div>" +
                    "<div class='popup-desc'>" + (feature.properties.description || 'No description') + "</div>";

                if (feature.properties.image) {
                    popup_content += "<img src='" + feature.properties.image + "' class='popup-img' onclick=\"window.open('" + feature.properties.image + "')\">";
                }

                popup_content += "<div class='popup-meta'>" +
                    "<div><i class='fa-solid fa-location-dot'></i> " + feature.geometry.coordinates[1].toFixed(5) + ", " + feature.geometry.coordinates[0].toFixed(5) + "</div>" +
                    "<div><i class='fa-solid fa-calendar-days'></i> " + feature.properties.created_at + "</div>" +
                    "</div>";

                popup_content += "<button class='btn-delete' onclick=\"deleteFeature('" +
                    "{{ route('points.destroy', ':id') }}".replace(':id', feature.properties.id) + "')\">" +
                    "<i class='fa-solid fa-trash'></i> Delete Point</button>";

                layer.on({
                    click: function (e) {
                        layer.bindPopup(popup_content).openPopup();
                    },
                    mouseover: function (e) {
                        layer.bindTooltip(feature.properties.name, {
                            direction: "left",
                            sticky: true,
                        });
                    },
                });
            },
        });

        /* GeoJSON Polylines */
        var polylinesLayer = L.geoJSON(null, {
            style: {
                color: 'blue',
                weight: 5,
                opacity: 0.7
            },
            onEachFeature: function (feature, layer) {
                var popup_content = "<div class='popup-title'>" + feature.properties.name + "</div>" +
                    "<div class='popup-desc'>" + (feature.properties.description || 'No description') + "</div>";

                if (feature.properties.image) {
                    popup_content += "<img src='" + feature.properties.image + "' class='popup-img' onclick=\"window.open('" + feature.properties.image + "')\">";
                }

                popup_content += "<div class='popup-meta'>" +
                    "<div><i class='fa-solid fa-calendar-days'></i> " + feature.properties.created_at + "</div>" +
                    "</div>";

                popup_content += "<button class='btn-delete' onclick=\"deleteFeature('" +
                    "{{ route('polylines.destroy', ':id') }}".replace(':id', feature.properties.id) + "')\">" +
                    "<i class='fa-solid fa-trash'></i> Delete Line</button>";

                layer.on({
                    click: function (e) {
                        layer.bindPopup(popup_content).openPopup();
                    },
                    mouseover: function (e) {
                        layer.bindTooltip(feature.properties.name, {
                            sticky: true,
                        });
                    },
                });
            },
        });

        /* GeoJSON Polygons */
        var polygonsLayer = L.geoJSON(null, {
            style: {
                color: 'orange',
                fillColor: 'orange',
                fillOpacity: 0.5,
                weight: 2
            },
            onEachFeature: function (feature, layer) {
                var popup_content = "<div class='popup-title'>" + feature.properties.name + "</div>" +
                    "<div class='popup-desc'>" + (feature.properties.description || 'No description') + "</div>";

                if (feature.properties.image) {
                    popup_content += "<img src='" + feature.properties.image + "' class='popup-img' onclick=\"window.open('" + feature.properties.image + "')\">";
                }

                popup_content += "<div class='popup-meta'>" +
                    "<div><i class='fa-solid fa-calendar-days'></i> " + feature.properties.created_at + "</div>" +
                    "</div>";

                popup_content += "<button class='btn-delete' onclick=\"deleteFeature('" +
                    "{{ route('polygons.destroy', ':id') }}".replace(':id', feature.properties.id) + "')\">" +
                    "<i class='fa-solid fa-trash'></i> Delete Area</button>";

                layer.on({
                    click: function (e) {
                        layer.bindPopup(popup_content).openPopup();
                    },
                    mouseover: function (e) {
                        layer.bindTooltip(feature.properties.name, {
                            sticky: true,
                        });
                    },
                });
            },
        });

        // Layer Control
        var baseMaps = {
            "OpenStreetMap": osm,
            "Esri Satellite": esri_world_imagery,
            "Google Streets": google_streets
        };

        var overlayMaps = {
            "Points": pointsLayer,
            "Polylines": polylinesLayer,
            "Polygons": polygonsLayer,
            "Digitizing (Draw Items)": drawnItems
        };

        L.control.layers(baseMaps, overlayMaps).addTo(map);

        // Load all features
        $.getJSON("{{ route('api.points') }}", function (response) {
            pointsLayer.addData(response.data);
            map.addLayer(pointsLayer);
        });

        $.getJSON("{{ route('api.polylines') }}", function (response) {
            polylinesLayer.addData(response.data);
            map.addLayer(polylinesLayer);
        });

        $.getJSON("{{ route('api.polygons') }}", function (response) {
            polygonsLayer.addData(response.data);
            map.addLayer(polygonsLayer);
        });
    </script>
@endpush

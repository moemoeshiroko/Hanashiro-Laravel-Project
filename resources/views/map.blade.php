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
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #map {
            width: 100%;
            flex: 1;
            z-index: 1;
        }

        /* Modern Leaflet Control Styling */
        .leaflet-control-zoom, .leaflet-control-layers, .leaflet-draw-toolbar {
            border: none !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            border-radius: 10px !important;
            overflow: hidden;
        }

        .leaflet-control-zoom a, .leaflet-control-layers-toggle, .leaflet-draw-draw-polyline, 
        .leaflet-draw-draw-polygon, .leaflet-draw-draw-rectangle, .leaflet-draw-draw-marker {
            background-color: white !important;
            color: #475569 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            transition: all 0.2s;
        }

        .leaflet-control-zoom a:hover {
            background-color: #f8fafc !important;
            color: var(--primary-accent) !important;
        }

        /* Premium Popup Styling */
        .leaflet-popup-content-wrapper {
            border-radius: 16px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }

        .popup-header {
            background: #f8fafc;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .popup-title {
            font-weight: 700;
            font-size: 18px;
            color: #0f172a;
            margin: 0;
        }

        .popup-body {
            padding: 15px;
        }

        .popup-desc {
            color: #475569;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .popup-img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .popup-img:hover {
            transform: scale(1.03);
        }

        .popup-footer {
            padding: 15px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .popup-meta {
            font-size: 11px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        .btn-delete-modern {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            text-align: center;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-delete-modern:hover {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }

        /* Modal Glassmorphism */
        .modal-content {
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 20px 25px;
        }

        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 15px 25px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            border-color: var(--primary-accent);
        }

        /* Layer Toggle Switch Customization */
        .form-check-input:checked {
            background-color: var(--primary-accent);
            border-color: var(--primary-accent);
        }

        .custom-marker i {
            filter: drop-shadow(0 2px 2px rgba(0,0,0,0.3));
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

    {{-- Hidden Delete All Form --}}
    <form id="delete-all-form" action="{{ route('map.delete-all') }}" method="POST" style="display: none;">
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
                                onchange="if(this.files[0].size > 2097152){ Swal.fire('Error', 'File size exceeds 2MB limit', 'error'); this.value=''; return; } document.getElementById('preview-image-point').src = window.URL.createObjectURL(this.files[0])">
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
                                onchange="if(this.files[0].size > 2097152){ Swal.fire('Error', 'File size exceeds 2MB limit', 'error'); this.value=''; return; } document.getElementById('preview-image-polyline').src = window.URL.createObjectURL(this.files[0])">
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
                                onchange="if(this.files[0].size > 2097152){ Swal.fire('Error', 'File size exceeds 2MB limit', 'error'); this.value=''; return; } document.getElementById('preview-image-polygon').src = window.URL.createObjectURL(this.files[0])">
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
    {{-- Settings Modal --}}
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel"><i class="fa-solid fa-sliders me-2"></i>Map Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold mb-3">Basemaps</h6>
                    <div class="list-group mb-4">
                        <label class="list-group-item d-flex align-items-center gap-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="basemapRadio" value="osm" checked>
                            <span>OpenStreetMap</span>
                        </label>
                        <label class="list-group-item d-flex align-items-center gap-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="basemapRadio" value="esri">
                            <span>Esri World Imagery</span>
                        </label>
                        <label class="list-group-item d-flex align-items-center gap-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="basemapRadio" value="google">
                            <span>Google Streets</span>
                        </label>
                    </div>

                    <h6 class="fw-bold mb-3">Overlay Layers</h6>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Points
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="togglePoints" checked>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Polylines
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="togglePolylines" checked>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Polygons
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="togglePolygons" checked>
                            </div>
                        </li>
                    </ul>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Danger Zone</h6>
                    <button class="btn btn-outline-danger w-100 py-2" onclick="deleteAllData()">
                        <i class="fa-solid fa-trash-can me-2"></i> Delete All Saved Data
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
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
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = url;
                    form.submit();
                }
            });
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
            pointToLayer: function(feature, latlng) {
                return L.marker(latlng, {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="background-color: #6366f1; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.2);"><i class="fa-solid fa-location-dot" style="color: white; transform: rotate(45deg); font-size: 14px;"></i></div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    })
                });
            },
            onEachFeature: function (feature, layer) {
                var popup_content = `<div class="popup-header"><h3 class="popup-title">${feature.properties.name}</h3></div><div class="popup-body">`;

                if (feature.properties.image) {
                    popup_content += `<img src="${feature.properties.image}" class="popup-img" onclick="window.open('${feature.properties.image}')">`;
                }

                popup_content += `<div class="popup-desc">${feature.properties.description || 'No description provided.'}</div></div>`;

                popup_content += `<div class="popup-footer"><div class="popup-meta"><span><i class="fa-solid fa-location-dot me-1"></i> ${feature.geometry.coordinates[1].toFixed(4)}, ${feature.geometry.coordinates[0].toFixed(4)}</span><span><i class="fa-solid fa-clock me-1"></i> ${new Date(feature.properties.created_at).toLocaleDateString()}</span></div>`;

                popup_content += `<button class="btn-delete-modern" onclick="deleteFeature('${"{{ route('points.destroy', ':id') }}".replace(':id', feature.properties.id)}')"><i class="fa-solid fa-trash-can"></i> Delete Point</button></div>`;

                layer.on({
                    click: function (e) {
                        layer.bindPopup(popup_content).openPopup();
                    },
                    mouseover: function (e) {
                        layer.bindTooltip(feature.properties.name, {
                            direction: "top",
                            sticky: true,
                        });
                    },
                });
            },
        });

        /* GeoJSON Polylines */
        var polylinesLayer = L.geoJSON(null, {
            style: {
                color: '#6366f1',
                weight: 4,
                opacity: 0.8
            },
            onEachFeature: function (feature, layer) {
                var popup_content = `<div class="popup-header"><h3 class="popup-title">${feature.properties.name}</h3></div><div class="popup-body">`;

                if (feature.properties.image) {
                    popup_content += `<img src="${feature.properties.image}" class="popup-img" onclick="window.open('${feature.properties.image}')">`;
                }

                popup_content += `<div class="popup-desc">${feature.properties.description || 'No description provided.'}</div></div>`;

                popup_content += `<div class="popup-footer"><div class="popup-meta"><span><i class="fa-solid fa-clock me-1"></i> ${new Date(feature.properties.created_at).toLocaleDateString()}</span></div>`;

                popup_content += `<button class="btn-delete-modern" onclick="deleteFeature('${"{{ route('polylines.destroy', ':id') }}".replace(':id', feature.properties.id)}')"><i class="fa-solid fa-trash-can"></i> Delete Line</button></div>`;

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
                color: '#f59e0b',
                fillColor: '#f59e0b',
                fillOpacity: 0.4,
                weight: 3
            },
            onEachFeature: function (feature, layer) {
                var popup_content = `<div class="popup-header"><h3 class="popup-title">${feature.properties.name}</h3></div><div class="popup-body">`;

                if (feature.properties.image) {
                    popup_content += `<img src="${feature.properties.image}" class="popup-img" onclick="window.open('${feature.properties.image}')">`;
                }

                popup_content += `<div class="popup-desc">${feature.properties.description || 'No description provided.'}</div></div>`;

                popup_content += `<div class="popup-footer"><div class="popup-meta"><span><i class="fa-solid fa-clock me-1"></i> ${new Date(feature.properties.created_at).toLocaleDateString()}</span></div>`;

                popup_content += `<button class="btn-delete-modern" onclick="deleteFeature('${"{{ route('polygons.destroy', ':id') }}".replace(':id', feature.properties.id)}')"><i class="fa-solid fa-trash-can"></i> Delete Area</button></div>`;

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

        // Settings Modal Logic
        $('input[name="basemapRadio"]').on('change', function() {
            const val = $(this).val();
            map.removeLayer(osm);
            map.removeLayer(esri_world_imagery);
            map.removeLayer(google_streets);

            if (val === 'osm') osm.addTo(map);
            else if (val === 'esri') esri_world_imagery.addTo(map);
            else if (val === 'google') google_streets.addTo(map);
        });

        $('#togglePoints').on('change', function() {
            if ($(this).is(':checked')) map.addLayer(pointsLayer);
            else map.removeLayer(pointsLayer);
        });

        $('#togglePolylines').on('change', function() {
            if ($(this).is(':checked')) map.addLayer(polylinesLayer);
            else map.removeLayer(polylinesLayer);
        });

        $('#togglePolygons').on('change', function() {
            if ($(this).is(':checked')) map.addLayer(polygonsLayer);
            else map.removeLayer(polygonsLayer);
        });

        // Delete All Data Logic
        window.deleteAllData = function() {
            Swal.fire({
                title: 'Are you absolutely sure?',
                text: "This will permanently delete ALL markers, lines, and polygons from the database. This action cannot be undone!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete everything!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Clearing Data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('delete-all-form').submit();
                        }
                    });
                }
            });
        };
    </script>
@endpush

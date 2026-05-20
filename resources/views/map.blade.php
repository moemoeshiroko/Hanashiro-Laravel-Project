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
            background-color: #0b0f19;
        }

        #map {
            width: 100%;
            flex: 1;
            z-index: 1;
            background-color: #0f172a !important;
            transition: filter 0.4s ease;
        }

        /* Dynamic Dark Map Tile Filter */
        .dark-map .leaflet-tile {
            filter: invert(100%) hue-rotate(180deg) brightness(85%) contrast(90%) !important;
        }

        /* Premium Glassmorphic Leaflet Controls */
        .leaflet-control-zoom, 
        .leaflet-control-layers, 
        .leaflet-draw-toolbar, 
        .leaflet-bar {
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.35) !important;
            border-radius: 12px !important;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
        }

        .leaflet-control-zoom a, 
        .leaflet-control-layers-toggle, 
        .leaflet-bar a,
        .leaflet-draw-toolbar a {
            background-color: transparent !important;
            color: #cbd5e1 !important;
            border: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        /* Invert leaflet-draw icons to be highly visible in dark mode */
        .leaflet-draw-toolbar a {
            filter: invert(1) brightness(2) !important;
        }

        .leaflet-control-zoom a:hover, 
        .leaflet-bar a:hover,
        .leaflet-draw-toolbar a:hover {
            background-color: rgba(99, 102, 241, 0.2) !important;
            color: #818cf8 !important;
        }

        /* Expanded Layers Control Customization */
        .leaflet-control-layers-expanded {
            background: rgba(15, 23, 42, 0.95) !important;
            color: #f8fafc !important;
            padding: 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 12px !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        .leaflet-control-layers-expanded label {
            margin-bottom: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .leaflet-control-layers-selector {
            accent-color: var(--primary-accent);
        }

        .leaflet-draw-actions a {
            background-color: #ef4444 !important;
            color: white !important;
            filter: none !important;
        }
        .leaflet-draw-actions a:hover {
            background-color: #dc2626 !important;
        }

        /* Premium Dark Glassmorphic Popup Card */
        .leaflet-popup-content-wrapper, .leaflet-popup-tip {
            background: rgba(15, 23, 42, 0.9) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.4) !important;
        }

        .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }

        .popup-header {
            background: rgba(30, 41, 59, 0.6);
            padding: 15px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .popup-title {
            font-weight: 700;
            font-size: 16px;
            color: #f8fafc;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .popup-body {
            padding: 16px;
        }

        .popup-desc {
            color: #cbd5e1;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .popup-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 14px;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), filter 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .popup-img:hover {
            transform: scale(1.02);
            filter: brightness(1.05);
        }

        .popup-footer {
            padding: 14px 16px;
            background: rgba(30, 41, 59, 0.4);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .popup-meta {
            font-size: 11px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        /* Leaflet popup close button overrides */
        .leaflet-popup-close-button {
            color: #94a3b8 !important;
            padding: 8px 10px 0 0 !important;
            transition: color 0.2s !important;
        }
        .leaflet-popup-close-button:hover {
            color: #f8fafc !important;
        }

        /* Overriding inline Bootstrap Buttons specifically in the Popup Footer */
        .popup-footer .btn {
            border-radius: 8px !important;
            font-weight: 600 !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border-width: 1px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px !important;
            padding: 8px 4px !important;
        }

        /* Focus & Edit: Premium Indigo/Violet */
        .popup-footer .btn-info {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
            color: #ffffff !important;
        }
        .popup-footer .btn-info:hover {
            background-color: #4338ca !important;
            border-color: #4338ca !important;
            box-shadow: 0 0 12px rgba(79, 70, 229, 0.4) !important;
        }

        /* Edit Info: Premium Amber/Orange */
        .popup-footer .btn-edit-feature {
            background-color: #f59e0b !important;
            border-color: #f59e0b !important;
            color: #ffffff !important;
        }
        .popup-footer .btn-edit-feature:hover {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
            box-shadow: 0 0 12px rgba(245, 158, 11, 0.4) !important;
        }

        /* Copy Link: Premium Glass Action */
        .popup-footer .btn-outline-primary {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #e2e8f0 !important;
        }
        .popup-footer .btn-outline-primary:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.05) !important;
        }

        /* Delete: Soft Red Glass, solid red on hover */
        .popup-footer .btn-danger {
            background-color: rgba(239, 68, 68, 0.15) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
            color: #fca5a5 !important;
        }
        .popup-footer .btn-danger:hover {
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.4) !important;
        }

        /* High-end Dark Glassmorphic Modals */
        .modal-content {
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.9) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            color: #f8fafc;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding: 20px 25px;
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 15px 25px;
        }

        .modal-title {
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #f8fafc;
        }

        .btn-close {
            filter: invert(1) grayscale(1) brightness(2);
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .btn-close:hover {
            opacity: 1;
        }

        /* Sleek Dark Form Fields */
        .form-control, .form-select {
            background-color: rgba(30, 41, 59, 0.5) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(30, 41, 59, 0.8) !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.25) !important;
            border-color: #818cf8 !important;
            outline: none;
        }

        .form-control[readonly] {
            background-color: rgba(15, 23, 42, 0.6) !important;
            color: #94a3b8 !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        /* Settings modal list group custom dark layout */
        .modal-content .list-group-item {
            background: rgba(30, 41, 59, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            color: #cbd5e1 !important;
            padding: 14px 18px !important;
            border-radius: 12px !important;
            margin-bottom: 8px !important;
            transition: all 0.2s ease !important;
        }

        .modal-content .list-group-item:hover {
            background: rgba(30, 41, 59, 0.7) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        .form-check-input {
            background-color: rgba(30, 41, 59, 0.8);
            border-color: rgba(255, 255, 255, 0.15);
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary-accent);
            border-color: var(--primary-accent);
        }

        .custom-marker i {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4));
        }

        /* High-end Dark Glassmorphic Focus Pill */
        .focus-indicator-pill {
            position: absolute;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(239, 68, 68, 0.25);
            box-shadow: 0 12px 32px 0 rgba(0, 0, 0, 0.4), 0 0 15px rgba(239, 68, 68, 0.15);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 12px 24px;
            border-radius: 50px;
            align-items: center;
            gap: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #f8fafc;
        }

        /* Modal/Alert Background Blur */
        body.modal-open #map,
        body.swal2-shown #map {
            filter: blur(6px) grayscale(20%);
        }

        /* SweetAlert2 Premium Dark Theme overrides */
        .swal2-popup {
            background: rgba(15, 23, 42, 0.95) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-radius: 24px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
        }
        .swal2-title {
            color: #f8fafc !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em !important;
        }
        .swal2-html-container {
            color: #94a3b8 !important;
            font-size: 14px !important;
        }
        .swal2-confirm {
            background-color: #ef4444 !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            padding: 10px 24px !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25) !important;
        }
        .swal2-cancel {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #cbd5e1 !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            padding: 10px 24px !important;
            transition: all 0.2s !important;
        }
        .swal2-cancel:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
        }
        .swal2-success-circular-line, .swal2-success-fix, .swal2-success-html-container {
            background-color: transparent !important;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
    <div id="map" class="dark-map"></div>

    {{-- Focus Mode Indicator --}}
    <div id="focus-indicator" class="focus-indicator-pill" style="display: none;">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-grow spinner-grow-sm text-danger" id="focus-spinner" role="status"></div>
            <span id="focus-mode-title" style="font-weight: 800; color: #ef4444; font-size: 14px; letter-spacing: 0.05em;">EDIT MODE IN PROGRESS!!</span>
            <span style="color: #94a3b8;">|</span>
            <span style="font-weight: 700; color: #f8fafc; font-size: 14px;"><span id="focus-layer-name" class="text-primary">Feature</span></span>
        </div>
        
        <div id="edit-actions" style="display: none; gap: 10px; border-left: 1px solid rgba(255, 255, 255, 0.15); padding-left: 15px;">
            <button class="btn btn-sm btn-success" id="save-vertices-btn" style="border-radius: 20px; padding: 6px 18px; font-weight: 600; font-size: 12px;">
                <i class="fa-solid fa-save me-1"></i> Save Changes
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="exitFocusMode()" style="border-radius: 20px; padding: 6px 18px; font-weight: 600; font-size: 12px;">
                <i class="fa-solid fa-xmark me-1"></i> Cancel
            </button>
        </div>

        <div id="default-focus-actions" style="display: flex; gap: 10px;">
            <button id="btn-show-all" class="btn btn-sm btn-dark" onclick="exitFocusMode()" style="border-radius: 20px; padding: 6px 18px; font-weight: 600; font-size: 12px;">
                <i class="fa-solid fa-eye me-1"></i> Show All Features
            </button>
            <button id="btn-cancel-focus" class="btn btn-sm btn-outline-danger" onclick="exitFocusMode()" style="border-radius: 20px; padding: 6px 18px; font-weight: 600; font-size: 12px;">
                <i class="fa-solid fa-xmark me-1"></i> Cancel
            </button>
        </div>
    </div>

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
                    <input type="hidden" id="method_point" name="_method" value="POST">
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
                    <input type="hidden" id="method_polyline" name="_method" value="POST">
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
                    <input type="hidden" id="method_polygon" name="_method" value="POST">
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

                    @auth
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Danger Zone</h6>
                    <button class="btn btn-outline-danger w-100 py-2" onclick="deleteAllData()">
                        <i class="fa-solid fa-trash-can me-2"></i> Delete All Saved Data
                    </button>
                    @endauth
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
        const isAuthenticated = @json(auth()->check());

        // Define world bounds to prevent wrapping and scrolling off
        const worldBounds = L.latLngBounds([-85.05, -180], [85.05, 180]);
        const map = L.map('map', {
            minZoom: 3, // Zoom 3 fits the single world map perfectly
            maxZoom: 19,
            maxBounds: worldBounds,
            maxBoundsViscosity: 1.0
        }).setView([-8.6529, 116.3242], 10);
        let currentFocus = null;

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

        // Share link function
        window.copyShareLink = function(type, id) {
            const url = window.location.origin + window.location.pathname + '#edit-' + type + '-' + id;
            navigator.clipboard.writeText(url).then(() => {
                Swal.fire({
                    title: 'Link Copied!',
                    text: 'Shareable link has been copied to clipboard.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        };

        // Handle Routing based on URL hash
        window.handleRouting = function() {
            const hash = window.location.hash;
            const focusType = @json($focusType);
            const focusId = @json($focusId);

            if (focusType && focusId) {
                enterFocusMode(focusType, focusId);
                return;
            }

            if (hash.startsWith('#edit-')) {
                const parts = hash.replace('#edit-', '').split('-');
                if (parts.length === 2) {
                    const type = parts[0];
                    const id = parts[1];
                    enterFocusMode(type, id);
                }
            }
        };

        // Enter Focus Mode
        window.enterFocusMode = function(type, id) {
            let targetLayer = null;
            
            drawnItems.eachLayer(layer => {
                if (layer.feature && layer.feature.properties && layer.feature.properties.id == id && layer.feature.properties.type == type) {
                    targetLayer = layer;
                    if (!map.hasLayer(layer)) map.addLayer(layer);
                } else {
                    map.removeLayer(layer);
                }
            });

            if (targetLayer) {
                currentFocus = { type, id, layer: targetLayer };
                
                if (targetLayer.getBounds) {
                    map.fitBounds(targetLayer.getBounds(), { padding: [100, 100] });
                } else if (targetLayer.getLatLng) {
                    map.setView(targetLayer.getLatLng(), 18);
                }

                $('#focus-layer-name').text(`${type} #${id}`);
                $('#focus-indicator').css('display', 'flex');
                $('#edit-actions').hide();
                $('#default-focus-actions').show();
                
                // Enable editing if authenticated
                if (isAuthenticated) {
                    $('#focus-mode-title').text('EDIT MODE IN PROGRESS!!').css('color', '#ef4444');
                    $('#focus-spinner').addClass('text-danger').removeClass('text-primary');

                    if (targetLayer.editing) {
                        targetLayer.editing.enable();
                    } else if (targetLayer instanceof L.Marker) {
                        targetLayer.dragging.enable();
                    }

                    // Listen for changes
                    const showSave = () => {
                        $('#edit-actions').css('display', 'flex');
                        $('#default-focus-actions').hide();
                    };

                    targetLayer.on('edit dragend', showSave);
                    map.on('draw:editvertex', showSave);
                } else {
                    $('#focus-mode-title').text('FEATURE FOCUS VIEW').css('color', '#6366f1');
                    $('#focus-spinner').removeClass('text-danger').addClass('text-primary');
                }

                targetLayer.openPopup();
                
                const newPath = `/${type.toLowerCase()}s/${id}`;
                if (window.location.pathname !== newPath) {
                    window.history.pushState({}, '', newPath);
                }
            }
        };

        // Exit Focus Mode
        window.exitFocusMode = function() {
            if (currentFocus && currentFocus.layer) {
                currentFocus.layer.off('edit dragend');
                map.off('draw:editvertex');
                if (currentFocus.layer.editing) currentFocus.layer.editing.disable();
                if (currentFocus.layer.dragging) currentFocus.layer.dragging.disable();
            }

            drawnItems.eachLayer(layer => {
                if (!map.hasLayer(layer)) map.addLayer(layer);
            });

            $('#focus-indicator').hide();
            currentFocus = null;
            window.history.pushState({}, '', "{{ route('map') }}");
            
            // Reload page to reset any unsaved local geometry changes
            location.reload();
        };

        // Save Vertices Logic
        $('#save-vertices-btn').on('click', function() {
            if (!currentFocus) return;

            const layer = currentFocus.layer;
            const type = currentFocus.type;
            const id = currentFocus.id;

            const drawnJSONObject = layer.toGeoJSON();
            const objectGeometry = Terraformer.geojsonToWKT(drawnJSONObject.geometry);

            let url = "";
            if (type === 'Point') url = "{{ route('points.update', ':id') }}";
            else if (type === 'Polyline') url = "{{ route('polylines.update', ':id') }}";
            else if (type === 'Polygon') url = "{{ route('polygons.update', ':id') }}";
            
            url = url.replace(':id', id);

            Swal.fire({
                title: 'Saving changes...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: url,
                type: 'PATCH',
                data: {
                    _token: "{{ csrf_token() }}",
                    [`geometry_${type.toLowerCase()}`]: objectGeometry
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Saved!',
                        text: 'Geometry updated successfully.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Success - exit focus mode (will reload)
                        window.exitFocusMode();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to save changes: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
                }
            });
        });

        // Listen for hash changes
        window.addEventListener('hashchange', handleRouting);

        // Edit Point function
        window.editPoint = function(data) {
            $('#modalInputPoint .modal-title').text('Edit Point: ' + (data.name || 'Untitled'));
            $('#modalInputPoint form').attr('action', "{{ route('points.update', ':id') }}".replace(':id', data.id));
            $('#method_point').val('PATCH');
            $('#name').val(data.name || '');
            $('#descriptions').val(data.description || '');
            $('#preview-image-point').attr('src', data.image || '');
            
            if (data.geometry) {
                $('#geometry_point').val(data.geometry);
                $('#geometry_display').val(data.geometry);
            } else {
                $('#geometry_point').val('');
                $('#geometry_display').val('Geometry will be preserved if not changed');
            }
            
            $('#modalInputPoint').modal('show');
        };

        // Edit Polyline function
        window.editPolyline = function(data) {
            $('#modalInputPolyline .modal-title').text('Edit Polyline: ' + (data.name || 'Untitled'));
            $('#modalInputPolyline form').attr('action', "{{ route('polylines.update', ':id') }}".replace(':id', data.id));
            $('#method_polyline').val('PATCH');
            $('#polyline_name').val(data.name || '');
            $('#polyline_descriptions').val(data.description || '');
            $('#preview-image-polyline').attr('src', data.image || '');
            
            if (data.geometry) {
                $('#geometry_polyline').val(data.geometry);
                $('#polyline_geometry_display').val(data.geometry);
            } else {
                $('#geometry_polyline').val('');
                $('#polyline_geometry_display').val('Geometry will be preserved if not changed');
            }

            $('#modalInputPolyline').modal('show');
        };

        // Edit Polygon function
        window.editPolygon = function(data) {
            $('#modalInputPolygon .modal-title').text('Edit Polygon: ' + (data.name || 'Untitled'));
            $('#modalInputPolygon form').attr('action', "{{ route('polygons.update', ':id') }}".replace(':id', data.id));
            $('#method_polygon').val('PATCH');
            $('#polygon_name').val(data.name || '');
            $('#polygon_descriptions').val(data.description || '');
            $('#preview-image-polygon').attr('src', data.image || '');
            
            if (data.geometry) {
                $('#geometry_polygon').val(data.geometry);
                $('#polygon_geometry_display').val(data.geometry);
            } else {
                $('#geometry_polygon').val('');
                $('#polygon_geometry_display').val('Geometry will be preserved if not changed');
            }

            $('#modalInputPolygon').modal('show');
        };

        // Base Maps
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
            noWrap: true,
            bounds: worldBounds
        });

        var esri_world_imagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
            noWrap: true,
            bounds: worldBounds
        });

        var google_streets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google',
            noWrap: true,
            bounds: worldBounds
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
            edit: {
                featureGroup: drawnItems,
                remove: true
            }
        });

        if (isAuthenticated) {
            map.addControl(drawControl);
        }

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
                $('#modalInputPolyline .modal-title').text('Le Polyline Input');
                $('#modalInputPolyline form').attr('action', "{{ route('polylines.store') }}");
                $('#method_polyline').val('POST');
                $('#polyline_name').val('');
                $('#polyline_descriptions').val('');
                $('#preview-image-polyline').attr('src', '');
                $('#geometry_polyline').val(objectGeometry);
                $('#polyline_geometry_display').val(objectGeometry);
                $('#modalInputPolyline').modal('show');

                //modal dismiss reload page
                $('#modalInputPolyline').on('hidden.bs.modal', function () {
                    location.reload();
                });
            } else if (type === 'polygon' || type === 'rectangle') {
                console.log("Create " + type);
                $('#modalInputPolygon .modal-title').text('Le Polygon Input');
                $('#modalInputPolygon form').attr('action', "{{ route('polygons.store') }}");
                $('#method_polygon').val('POST');
                $('#polygon_name').val('');
                $('#polygon_descriptions').val('');
                $('#preview-image-polygon').attr('src', '');
                $('#geometry_polygon').val(objectGeometry);
                $('#polygon_geometry_display').val(objectGeometry);
                $('#modalInputPolygon').modal('show');

                //modal dismiss reload page
                $('#modalInputPolygon').on('hidden.bs.modal', function () {
                    location.reload();
                });
            } else if (type === 'marker') {
                console.log("Create " + type);
                $('#modalInputPoint .modal-title').text('Le Point Input');
                $('#modalInputPoint form').attr('action', "{{ route('points.store') }}");
                $('#method_point').val('POST');
                $('#name').val('');
                $('#descriptions').val('');
                $('#preview-image-point').attr('src', '');
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

        map.on('draw:edited', function (e) {
            var layers = e.layers;
            layers.eachLayer(function (layer) {
                var feature = layer.feature;
                if (feature) {
                    var drawnJSONObject = layer.toGeoJSON();
                    var objectGeometry = Terraformer.geojsonToWKT(drawnJSONObject.geometry);
                    
                    if (feature.properties.type === 'Point') {
                        editPoint({...feature.properties, geometry: objectGeometry});
                        $('#geometry_point').val(objectGeometry);
                        $('#geometry_display').val(objectGeometry);
                    } else if (feature.properties.type === 'Polyline') {
                        editPolyline({...feature.properties, geometry: objectGeometry});
                        $('#geometry_polyline').val(objectGeometry);
                        $('#polyline_geometry_display').val(objectGeometry);
                    } else if (feature.properties.type === 'Polygon') {
                        editPolygon({...feature.properties, geometry: objectGeometry});
                        $('#geometry_polygon').val(objectGeometry);
                        $('#polygon_geometry_display').val(objectGeometry);
                    }
                }
            });
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
                var wkt = Terraformer.geojsonToWKT(feature.geometry);
                var popup_content = `<div class="popup-header"><h3 class="popup-title">${feature.properties.name}</h3></div><div class="popup-body">`;

                if (feature.properties.image) {
                    popup_content += `<img src="${feature.properties.image}" class="popup-img" onclick="window.open('${feature.properties.image}')">`;
                }

                popup_content += `<div class="popup-desc">${feature.properties.description || 'No description provided.'}</div></div>`;

                popup_content += `<div class="popup-footer"><div class="popup-meta"><span><i class="fa-solid fa-location-dot me-1"></i> ${feature.geometry.coordinates[1].toFixed(4)}, ${feature.geometry.coordinates[0].toFixed(4)}</span><span><i class="fa-solid fa-clock me-1"></i> ${new Date(feature.properties.created_at).toLocaleDateString()}</span></div>`;

                popup_content += `<div class="d-flex gap-2 mb-2">`;
                popup_content += `<button class="btn btn-sm btn-info text-white w-50" style="background-color: #0ea5e9; border-color: #0ea5e9; font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="enterFocusMode('Point', ${feature.properties.id})"><i class="fa-solid fa-vector-square me-1"></i> Focus & Edit</button>`;
                popup_content += `<button class="btn btn-sm btn-warning w-50 btn-edit-feature" style="font-size: 11px; padding: 8px 4px; font-weight: 600;" data-type="Point" data-id="${feature.properties.id}"><i class="fa-solid fa-edit me-1"></i> Edit Info</button>`;
                popup_content += `</div>`;
                popup_content += `<div class="d-flex gap-2">`;
                popup_content += `<button class="btn btn-sm btn-outline-primary w-50" style="font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="copyShareLink('Point', ${feature.properties.id})"><i class="fa-solid fa-share-nodes me-1"></i> Copy Link</button>`;
                popup_content += `<button class="btn btn-sm btn-danger w-50" style="background-color: #fee2e2; border-color: #fecaca; color: #dc2626; font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="deleteFeature('${"{{ route('points.destroy', ':id') }}".replace(':id', feature.properties.id)}')"><i class="fa-solid fa-trash-can me-1"></i> Delete</button>`;
                popup_content += `</div></div>`;

                layer.bindPopup(popup_content);
                layer.on({
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
                var wkt = Terraformer.geojsonToWKT(feature.geometry);
                var popup_content = `<div class="popup-header"><h3 class="popup-title">${feature.properties.name}</h3></div><div class="popup-body">`;

                if (feature.properties.image) {
                    popup_content += `<img src="${feature.properties.image}" class="popup-img" onclick="window.open('${feature.properties.image}')">`;
                }

                popup_content += `<div class="popup-desc">${feature.properties.description || 'No description provided.'}</div></div>`;

                popup_content += `<div class="popup-footer"><div class="popup-meta"><span><i class="fa-solid fa-clock me-1"></i> ${new Date(feature.properties.created_at).toLocaleDateString()}</span></div>`;

                popup_content += `<div class="d-flex gap-2 mb-2">`;
                popup_content += `<button class="btn btn-sm btn-info text-white w-50" style="background-color: #0ea5e9; border-color: #0ea5e9; font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="enterFocusMode('Polyline', ${feature.properties.id})"><i class="fa-solid fa-vector-square me-1"></i> Focus & Edit</button>`;
                popup_content += `<button class="btn btn-sm btn-warning w-50 btn-edit-feature" style="font-size: 11px; padding: 8px 4px; font-weight: 600;" data-type="Polyline" data-id="${feature.properties.id}"><i class="fa-solid fa-edit me-1"></i> Edit Info</button>`;
                popup_content += `</div>`;
                popup_content += `<div class="d-flex gap-2">`;
                popup_content += `<button class="btn btn-sm btn-outline-primary w-50" style="font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="copyShareLink('Polyline', ${feature.properties.id})"><i class="fa-solid fa-share-nodes me-1"></i> Copy Link</button>`;
                popup_content += `<button class="btn btn-sm btn-danger w-50" style="background-color: #fee2e2; border-color: #fecaca; color: #dc2626; font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="deleteFeature('${"{{ route('polylines.destroy', ':id') }}".replace(':id', feature.properties.id)}')"><i class="fa-solid fa-trash-can me-1"></i> Delete</button>`;
                popup_content += `</div></div>`;

                layer.bindPopup(popup_content);
                layer.on({
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
                var wkt = Terraformer.geojsonToWKT(feature.geometry);
                var popup_content = `<div class="popup-header"><h3 class="popup-title">${feature.properties.name}</h3></div><div class="popup-body">`;

                if (feature.properties.image) {
                    popup_content += `<img src="${feature.properties.image}" class="popup-img" onclick="window.open('${feature.properties.image}')">`;
                }

                popup_content += `<div class="popup-desc">${feature.properties.description || 'No description provided.'}</div></div>`;

                popup_content += `<div class="popup-footer"><div class="popup-meta"><span><i class="fa-solid fa-clock me-1"></i> ${new Date(feature.properties.created_at).toLocaleDateString()}</span></div>`;

                popup_content += `<div class="d-flex gap-2 mb-2">`;
                popup_content += `<button class="btn btn-sm btn-info text-white w-50" style="background-color: #0ea5e9; border-color: #0ea5e9; font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="enterFocusMode('Polygon', ${feature.properties.id})"><i class="fa-solid fa-vector-square me-1"></i> Focus & Edit</button>`;
                popup_content += `<button class="btn btn-sm btn-warning w-50 btn-edit-feature" style="font-size: 11px; padding: 8px 4px; font-weight: 600;" data-type="Polygon" data-id="${feature.properties.id}"><i class="fa-solid fa-edit me-1"></i> Edit Info</button>`;
                popup_content += `</div>`;
                popup_content += `<div class="d-flex gap-2">`;
                popup_content += `<button class="btn btn-sm btn-outline-primary w-50" style="font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="copyShareLink('Polygon', ${feature.properties.id})"><i class="fa-solid fa-share-nodes me-1"></i> Copy Link</button>`;
                popup_content += `<button class="btn btn-sm btn-danger w-50" style="background-color: #fee2e2; border-color: #fecaca; color: #dc2626; font-size: 11px; padding: 8px 4px; font-weight: 600;" onclick="deleteFeature('${"{{ route('polygons.destroy', ':id') }}".replace(':id', feature.properties.id)}')"><i class="fa-solid fa-trash-can me-1"></i> Delete</button>`;
                popup_content += `</div></div>`;

                layer.bindPopup(popup_content);
                layer.on({
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
            "All Features (Editable)": drawnItems
        };

        L.control.layers(baseMaps, overlayMaps).addTo(map);

        // Feature Store to keep data for editing
        const featureStore = {
            Point: {},
            Polyline: {},
            Polygon: {}
        };

        // Delegate edit button clicks
        $(document).on('click', '.btn-edit-feature', function() {
            const type = $(this).data('type');
            const id = $(this).data('id');
            const data = featureStore[type][id];
            
            // Update hash without triggering immediate scroll (optional)
            window.location.hash = `edit-${type}-${id}`;
            
            if (type === 'Point') editPoint(data);
            else if (type === 'Polyline') editPolyline(data);
            else if (type === 'Polygon') editPolygon(data);
        });

        const dataLoadingStatus = { points: false, polylines: false, polygons: false };
        function checkAllLoaded() {
            if (dataLoadingStatus.points && dataLoadingStatus.polylines && dataLoadingStatus.polygons) {
                handleRouting();
            }
        }

        $.getJSON("{{ route('api.points') }}", function (response) {
            L.geoJSON(response.data, {
                pointToLayer: pointsLayer.options.pointToLayer,
                onEachFeature: pointsLayer.options.onEachFeature
            }).eachLayer(l => {
                l.feature.properties.type = 'Point';
                drawnItems.addLayer(l);
            });
            response.data.forEach(f => {
                featureStore.Point[f.properties.id] = f.properties;
            });
            dataLoadingStatus.points = true;
            checkAllLoaded();
        });

        $.getJSON("{{ route('api.polylines') }}", function (response) {
            L.geoJSON(response.data, {
                style: polylinesLayer.options.style,
                onEachFeature: polylinesLayer.options.onEachFeature
            }).eachLayer(l => {
                l.feature.properties.type = 'Polyline';
                drawnItems.addLayer(l);
            });
            response.data.forEach(f => {
                featureStore.Polyline[f.properties.id] = f.properties;
            });
            dataLoadingStatus.polylines = true;
            checkAllLoaded();
        });

        $.getJSON("{{ route('api.polygons') }}", function (response) {
            L.geoJSON(response.data, {
                style: polygonsLayer.options.style,
                onEachFeature: polygonsLayer.options.onEachFeature
            }).eachLayer(l => {
                l.feature.properties.type = 'Polygon';
                drawnItems.addLayer(l);
            });
            response.data.forEach(f => {
                featureStore.Polygon[f.properties.id] = f.properties;
            });
            dataLoadingStatus.polygons = true;
            checkAllLoaded();
        });

        // Settings Modal Logic
        $('input[name="basemapRadio"]').on('change', function() {
            const val = $(this).val();
            map.removeLayer(osm);
            map.removeLayer(esri_world_imagery);
            map.removeLayer(google_streets);

            if (val === 'osm') {
                osm.addTo(map);
                $('#map').addClass('dark-map');
            } else if (val === 'esri') {
                esri_world_imagery.addTo(map);
                $('#map').removeClass('dark-map');
            } else if (val === 'google') {
                google_streets.addTo(map);
                $('#map').addClass('dark-map');
            }
        });

        $('#togglePoints').on('change', function() {
            const checked = $(this).is(':checked');
            drawnItems.eachLayer(function(l) {
                if (l.feature && l.feature.properties.type === 'Point') {
                    if (checked) map.addLayer(l); else map.removeLayer(l);
                }
            });
        });

        $('#togglePolylines').on('change', function() {
            const checked = $(this).is(':checked');
            drawnItems.eachLayer(function(l) {
                if (l.feature && l.feature.properties.type === 'Polyline') {
                    if (checked) map.addLayer(l); else map.removeLayer(l);
                }
            });
        });

        $('#togglePolygons').on('change', function() {
            const checked = $(this).is(':checked');
            drawnItems.eachLayer(function(l) {
                if (l.feature && l.feature.properties.type === 'Polygon') {
                    if (checked) map.addLayer(l); else map.removeLayer(l);
                }
            });
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

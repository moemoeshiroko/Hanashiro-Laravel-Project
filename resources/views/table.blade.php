@extends('layouts.templatemap')

@section('title', 'Feature Table - xShiro Map')

@push('styles')
<style>
    .table-container {
        padding: 40px 20px;
        min-height: calc(100vh - 60px);
        background-color: #0b0f19;
    }
    .feature-card {
        border-radius: 24px;
        overflow: hidden;
        margin-bottom: 30px;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.4) !important;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }
    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 48px 0 rgba(0, 0, 0, 0.5) !important;
    }
    .table-header-custom {
        padding: 22px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .table-header-custom h2 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }
    .feature-img-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 10px;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: transform 0.2s;
    }
    .feature-img-thumb:hover {
        transform: scale(1.05);
    }
    .badge-type {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    .badge-point { background: #6366f1; color: white; }
    .badge-polyline { background: #8b5cf6; color: white; }
    .badge-polygon { background: #f59e0b; color: white; }
    
    .btn-action {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
    }
    
    .btn-action.btn-outline-primary {
        background-color: rgba(255, 255, 255, 0.04) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #e2e8f0 !important;
    }
    .btn-action.btn-outline-primary:hover {
        background-color: rgba(99, 102, 241, 0.2) !important;
        border-color: #818cf8 !important;
        color: #818cf8 !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.2) !important;
    }
    
    .btn-action.btn-outline-danger {
        background-color: rgba(239, 68, 68, 0.1) !important;
        border-color: rgba(239, 68, 68, 0.2) !important;
        color: #fca5a5 !important;
    }
    .btn-action.btn-outline-danger:hover {
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.35) !important;
    }

    .table-custom thead th {
        background: rgba(30, 41, 59, 0.45) !important;
        color: #94a3b8 !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        border-bottom: 2px solid rgba(255, 255, 255, 0.06) !important;
        padding: 16px 28px;
    }
    .table-custom tbody td {
        padding: 20px 28px;
        vertical-align: middle;
        color: #cbd5e1;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    }
    
    .table-custom tbody tr:hover td {
        background-color: rgba(255, 255, 255, 0.01);
    }
</style>
@endpush

@section('content')
<div class="container table-container">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold text-slate-100" style="color: #f8fafc;">Project Assets</h1>
            <p class="text-muted">Manage all your map features in one place</p>
        </div>
        <a href="{{ route('map') }}" class="btn btn-dark px-4 py-2" style="border-radius: 12px; font-weight: 600;">
            <i class="fa-solid fa-map-location-dot me-2"></i> Back to Map
        </a>
    </div>

    {{-- Points Table --}}
    <div class="card feature-card glass-panel">
        <div class="table-header-custom" style="background: #6366f1;">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-location-dot fa-xl"></i>
                <h2>Points / Markers</h2>
            </div>
            <span class="badge bg-white text-primary rounded-pill px-3">{{ $points->count() }} Items</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Date Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($points as $point)
                    <tr>
                        <td>
                            @if($point->image)
                            <img src="{{ asset('storage/images/' . $point->image) }}" class="feature-img-thumb" onclick="Swal.fire({imageUrl: this.src, showConfirmButton: false})">
                            @else
                            <div class="feature-img-thumb d-flex align-items-center justify-content-center bg-slate-800" style="background: rgba(255, 255, 255, 0.05);">
                                <i class="fa-solid fa-image text-slate-500"></i>
                            </div>
                            @endif
                        </td>
                        <td><span class="fw-bold">{{ $point->name }}</span></td>
                        <td class="text-muted" style="max-width: 300px;">{{ Str::limit($point->description, 100) }}</td>
                        <td>{{ $point->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('points.focus', $point->id) }}" class="btn btn-action btn-outline-primary me-1" title="Focus on Map">
                                <i class="fa-solid fa-crosshairs"></i>
                            </a>
                            <form action="{{ route('points.destroy', $point->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-action btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No points found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Polylines Table --}}
    <div class="card feature-card glass-panel">
        <div class="table-header-custom" style="background: #8b5cf6;">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-route fa-xl"></i>
                <h2>Polylines / Paths</h2>
            </div>
            <span class="badge bg-white text-primary rounded-pill px-3">{{ $polylines->count() }} Items</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Date Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($polylines as $line)
                    <tr>
                        <td>
                            @if($line->image)
                            <img src="{{ asset('storage/images/' . $line->image) }}" class="feature-img-thumb" onclick="Swal.fire({imageUrl: this.src, showConfirmButton: false})">
                            @else
                            <div class="feature-img-thumb d-flex align-items-center justify-content-center bg-slate-800" style="background: rgba(255, 255, 255, 0.05);">
                                <i class="fa-solid fa-image text-slate-500"></i>
                            </div>
                            @endif
                        </td>
                        <td><span class="fw-bold">{{ $line->name }}</span></td>
                        <td class="text-muted" style="max-width: 300px;">{{ Str::limit($line->description, 100) }}</td>
                        <td>{{ $line->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('polylines.focus', $line->id) }}" class="btn btn-action btn-outline-primary me-1">
                                <i class="fa-solid fa-crosshairs"></i>
                            </a>
                            <form action="{{ route('polylines.destroy', $line->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-action btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No paths found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Polygons Table --}}
    <div class="card feature-card glass-panel">
        <div class="table-header-custom" style="background: #f59e0b;">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-draw-polygon fa-xl"></i>
                <h2>Polygons / Areas</h2>
            </div>
            <span class="badge bg-white text-primary rounded-pill px-3">{{ $polygons->count() }} Items</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Date Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($polygons as $poly)
                    <tr>
                        <td>
                            @if($poly->image)
                            <img src="{{ asset('storage/images/' . $poly->image) }}" class="feature-img-thumb" onclick="Swal.fire({imageUrl: this.src, showConfirmButton: false})">
                            @else
                            <div class="feature-img-thumb d-flex align-items-center justify-content-center bg-slate-800" style="background: rgba(255, 255, 255, 0.05);">
                                <i class="fa-solid fa-image text-slate-500"></i>
                            </div>
                            @endif
                        </td>
                        <td><span class="fw-bold">{{ $poly->name }}</span></td>
                        <td class="text-muted" style="max-width: 300px;">{{ Str::limit($poly->description, 100) }}</td>
                        <td>{{ $poly->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('polygons.focus', $poly->id) }}" class="btn btn-action btn-outline-primary me-1">
                                <i class="fa-solid fa-crosshairs"></i>
                            </a>
                            <form action="{{ route('polygons.destroy', $poly->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-action btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No areas found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

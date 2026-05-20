@props(['sbrand'])

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-2">
    <div class="container-fluid mx-lg-4">
        <a class="navbar-brand fw-bold fs-5" href="/"><i class="fa-solid fa-map-location-dot me-2 text-primary"></i>{{ $sbrand }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}" href="{{ route('welcome') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('map') ? 'active' : '' }}" href="{{ route('map') }}">Map</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0)" style="opacity: 0.45; cursor: not-allowed; color: #94a3b8 !important;" onclick="return false;">Table</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item me-lg-3 mb-2 mb-lg-0">
                    <span class="badge d-inline-flex align-items-center gap-1.5" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #fca5a5; font-size: 0.72rem; letter-spacing: 0.05em; font-weight: 600; padding: 6px 12px; border-radius: 8px;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.75rem;"></i> HNDH_0.2_INDV
                    </span>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
                            <i class="fa-solid fa-gear"></i> Settings
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@extends('layouts.templatemap')

@section('title', 'Welcome to Hanashiro Map')

@push('styles')
<style>
    /* Styling for the hero container and cards */
    .landing-hero {
        position: relative;
        padding: 80px 0;
        overflow: hidden;
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
    }
    
    .glow-sphere {
        position: absolute;
        border-radius: 50%;
        filter: blur(120px);
        z-index: 0;
        opacity: 0.12;
        pointer-events: none;
    }
    
    .glow-1 {
        width: 450px;
        height: 450px;
        background: var(--primary-accent);
        top: -150px;
        left: -150px;
    }
    
    .glow-2 {
        width: 350px;
        height: 350px;
        background: #8b5cf6;
        bottom: 50px;
        right: -100px;
    }
    
    .hero-card {
        border-radius: 28px;
        background: rgba(15, 23, 42, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        padding: 50px;
        box-shadow: 0 24px 50px 0 rgba(0, 0, 0, 0.5);
        z-index: 1;
        position: relative;
    }
    
    .feature-card {
        border-radius: 20px;
        background: rgba(30, 41, 59, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.06);
        padding: 35px 25px;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .feature-card:hover {
        transform: translateY(-6px);
        background: rgba(30, 41, 59, 0.65);
        border-color: rgba(99, 102, 241, 0.3);
        box-shadow: 0 15px 35px rgba(99, 102, 241, 0.15);
    }
    
    .icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        font-size: 1.65rem;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.3s ease;
    }
    
    .feature-card:hover .icon-wrapper {
        background: rgba(99, 102, 241, 0.2);
        border-color: rgba(99, 102, 241, 0.4);
        color: #818cf8 !important;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.25);
    }
    
    .cta-btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        color: white !important;
        font-weight: 600;
        padding: 14px 30px;
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.35);
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }
    
    .cta-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(99, 102, 241, 0.5);
        background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
    }
    
    .cta-btn-secondary {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #e2e8f0 !important;
        font-weight: 600;
        padding: 14px 30px;
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 12px;
        backdrop-filter: blur(8px);
        text-decoration: none;
    }
    
    .cta-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }
    
    .gradient-text {
        background: linear-gradient(135deg, #ffffff 40%, #a5b4fc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .stat-pill {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 14px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s ease;
    }
    
    .stat-pill:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    /* Auth Security Terminal Styles */
    .auth-terminal-card, .active-profile-card {
        padding: 40px 35px;
    }
    
    .terminal-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .nav-pills .nav-link {
        color: #94a3b8;
        background: transparent;
        border: 1px solid transparent;
    }
    
    .nav-pills .nav-link.active {
        background: rgba(99, 102, 241, 0.15) !important;
        border: 1px solid rgba(99, 102, 241, 0.35);
        color: #818cf8 !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    }
    
    .nav-pills .nav-link:hover:not(.active) {
        color: #cbd5e1;
        background: rgba(255, 255, 255, 0.03);
    }
    
    .auth-terminal-card .form-control {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #f8fafc !important;
        padding: 12px 16px;
        font-size: 0.9rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .auth-terminal-card .form-control:focus {
        background: rgba(15, 23, 42, 0.85);
        border-color: rgba(99, 102, 241, 0.5);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    
    .auth-terminal-card .form-control::placeholder {
        color: #475569;
    }
    
    .auth-terminal-card .input-group-text {
        background: rgba(30, 41, 59, 0.45);
        border-color: rgba(255, 255, 255, 0.08);
        color: #94a3b8;
        border-radius: 12px 0 0 12px;
    }
    
    .auth-terminal-card .input-group .form-control {
        border-radius: 0 12px 12px 0;
    }
    
    .alert-danger-glass {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #fca5a5;
        border-radius: 14px;
        padding: 15px;
    }
    
    .text-primary-link {
        color: #818cf8;
        transition: color 0.2s ease;
    }
    
    .text-primary-link:hover {
        color: #a5b4fc;
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .4; }
    }
    
    .profile-avatar .avatar-ring {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(99, 102, 241, 0.08);
        border: 2px dashed rgba(99, 102, 241, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 0 25px rgba(99, 102, 241, 0.15);
    }
</style>
@endpush

@section('content')
<div class="landing-hero">
    <!-- Ambient Background Glows -->
    <div class="glow-sphere glow-1"></div>
    <div class="glow-sphere glow-2"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row gy-5 align-items-center">
            
            <!-- Left Side: Information and Database Stats -->
            <div class="col-lg-7 text-start">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-4" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                    <i class="fa-solid fa-sparkles text-primary" style="font-size: 0.85rem;"></i>
                    @auth
                        <span class="text-success fw-bold" style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase;">EDITOR MODE ENABLED</span>
                    @else
                        <span class="text-primary fw-bold" style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase;">READ-ONLY GUEST MODE</span>
                    @endauth
                </div>
                
                <h1 class="display-4 fw-bold mb-4 tracking-tight" style="line-height: 1.15; color: #f8fafc;">
                    Explore and Organize <br>
                    <span class="gradient-text">Your Interactive World</span>
                </h1>
                
                <p class="lead text-muted mb-4" style="font-weight: 300; font-size: 1.08rem; line-height: 1.6; max-width: 620px;">
                    Welcome to Hanashiro Map. Draw points of interest, map paths with polylines, and delineate boundary zones with custom polygons. Keep your structural data organized with a clean asset manager dashboard.
                </p>
                
                <!-- Main Call To Actions -->
                <div class="d-flex flex-wrap gap-3 mb-5">
                    <a href="{{ route('map') }}" class="cta-btn-primary">
                        <i class="fa-solid fa-map-location-dot"></i>
                        @auth
                            <span>Enter Interactive Map (Full Edit)</span>
                        @else
                            <span>Enter Interactive Map (Guest View)</span>
                        @endauth
                    </a>
                    <a href="javascript:void(0)" class="cta-btn-secondary" style="opacity: 0.45; cursor: not-allowed;" onclick="return false;">
                        <i class="fa-solid fa-table"></i>
                        <span>Manage Asset Tables</span>
                    </a>
                </div>
                
                <!-- Database Live Statistics Bar -->
                <div class="row g-3">
                    <div class="col-sm-4 col-6">
                        <div class="stat-pill justify-content-start">
                            <i class="fa-solid fa-location-dot text-primary fa-lg"></i>
                            <div class="text-start">
                                <div class="fw-bold lh-1" style="font-size: 1.1rem; color: #f8fafc;">
                                    {{ \App\Models\Point::count() }}
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">Markers</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-6">
                        <div class="stat-pill justify-content-start">
                            <i class="fa-solid fa-route text-warning fa-lg"></i>
                            <div class="text-start">
                                <div class="fw-bold lh-1" style="font-size: 1.1rem; color: #f8fafc;">
                                    {{ \App\Models\Polyline::count() }}
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">Polylines</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-6">
                        <div class="stat-pill justify-content-start">
                            <i class="fa-solid fa-draw-polygon text-success fa-lg"></i>
                            <div class="text-start">
                                <div class="fw-bold lh-1" style="font-size: 1.1rem; color: #f8fafc;">
                                    {{ \App\Models\Polygon::count() }}
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">Polygons</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Beautiful Glassmorphic Auth Terminal or Profile card -->
            <div class="col-lg-5">
                @auth
                    <!-- Active Session Card -->
                    <div class="hero-card active-profile-card">
                        <div class="terminal-header d-flex align-items-center justify-content-between mb-4 pb-3" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="d-flex align-items-center gap-2">
                                <span class="terminal-dot bg-success animate-pulse" style="box-shadow: 0 0 8px #10b981;"></span>
                                <span class="text-success fw-mono" style="font-size: 0.75rem; font-family: monospace;">SESSION_ACTIVE</span>
                            </div>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #a7f3d0; font-size: 0.65rem; font-family: monospace;">PRIVILEGED</span>
                        </div>

                        <div class="profile-details text-center my-4">
                            <div class="profile-avatar mb-3 mx-auto">
                                <div class="avatar-ring">
                                    <i class="fa-solid fa-user-shield text-primary" style="font-size: 2.2rem;"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-1" style="color: #f8fafc;">{{ auth()->user()->name }}</h4>
                            <p class="text-muted" style="font-size: 0.88rem; font-family: monospace;">{{ auth()->user()->email }}</p>
                            
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mt-2 mb-4" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.15); color: #34d399; font-size: 0.78rem;">
                                <span class="d-inline-block bg-success rounded-circle" style="width: 6px; height: 6px; box-shadow: 0 0 8px #34d399;"></span>
                                Authorized Editor Active
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <a href="{{ route('dashboard') }}" class="cta-btn-primary w-100 justify-content-center py-3">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>Access Control Panel</span>
                            </a>
                            
                            <form method="POST" action="{{ route('logout') }}" class="w-100 m-0">
                                @csrf
                                <button type="submit" class="cta-btn-secondary w-100 justify-content-center py-3">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span>Terminate Session</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Authorization Security Terminal -->
                    <div class="hero-card auth-terminal-card">
                        <div class="terminal-header d-flex align-items-center justify-content-between mb-4 pb-3" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="d-flex align-items-center gap-2">
                                <span class="terminal-dot bg-danger"></span>
                                <span class="terminal-dot bg-warning"></span>
                                <span class="terminal-dot bg-success"></span>
                                <span class="ms-2 text-muted fw-mono" style="font-size: 0.75rem; font-family: monospace;">AUTH_CONTROL_CENTER</span>
                            </div>
                            <span class="badge" style="background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); color: #a5b4fc; font-size: 0.65rem; font-family: monospace;">SECURE_V2</span>
                        </div>

                        <!-- Tab Buttons for Switch -->
                        <div class="nav-tabs-wrapper mb-4">
                            <ul class="nav nav-pills nav-fill" role="tablist" style="background: rgba(15, 23, 42, 0.6); padding: 5px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-2.5" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel" type="button" role="tab" style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease;">
                                        <i class="fa-solid fa-lock-open me-1.5"></i> Sign In
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-2.5" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-panel" type="button" role="tab" style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease;">
                                        <i class="fa-solid fa-user-plus me-1.5"></i> Create Account
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Panels -->
                        <div class="tab-content">
                            <!-- Sign In Form -->
                            <div class="tab-pane fade show active" id="login-panel" role="tabpanel">
                                <!-- Validation Errors -->
                                @if ($errors->any() && !old('name'))
                                    <div class="alert alert-danger-glass mb-4 text-start" role="alert">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                            <div style="font-size: 0.82rem;">
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" class="text-start m-0">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="login-email" class="form-label text-muted fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                            <input type="email" name="email" id="login-email" class="form-control" placeholder="developer@hanashiro.com" value="{{ old('email') }}" required autofocus autocomplete="username">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="login-password" class="form-label text-muted fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Secret Key / Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                            <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" name="remember" id="login-remember" style="background-color: rgba(15, 23, 42, 0.6); border-color: rgba(255, 255, 255, 0.1);">
                                            <label class="form-check-label text-muted" for="login-remember" style="font-size: 0.82rem;">
                                                Keep authenticated
                                            </label>
                                        </div>
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="text-primary-link" style="font-size: 0.82rem; text-decoration: none;">Forgot key?</a>
                                        @endif
                                    </div>

                                    <button type="submit" class="cta-btn-primary w-100 justify-content-center py-3">
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                        <span>Authenticate Session</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Create Account Form -->
                            <div class="tab-pane fade" id="register-panel" role="tabpanel">
                                <!-- Validation Errors -->
                                @if ($errors->any() && old('name'))
                                    <div class="alert alert-danger-glass mb-4 text-start" role="alert">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                            <div style="font-size: 0.82rem;">
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('register') }}" class="text-start m-0">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="register-name" class="form-label text-muted fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Operator Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-user-gear"></i></span>
                                            <input type="text" name="name" id="register-name" class="form-control" placeholder="Hanashiro Member" value="{{ old('name') }}" required autofocus autocomplete="name">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="register-email" class="form-label text-muted fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Authorized Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                            <input type="email" name="email" id="register-email" class="form-control" placeholder="developer@hanashiro.com" value="{{ old('email') }}" required autocomplete="username">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="register-password" class="form-label text-muted fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Security Passphrase</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                            <input type="password" name="password" id="register-password" class="form-control" placeholder="Min. 8 characters" required autocomplete="new-password">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="register-confirm" class="form-label text-muted fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Confirm Passphrase</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
                                            <input type="password" name="password_confirmation" id="register-confirm" class="form-control" placeholder="Confirm secret key" required autocomplete="new-password">
                                        </div>
                                    </div>

                                    <button type="submit" class="cta-btn-primary w-100 justify-content-center py-3">
                                        <i class="fa-solid fa-id-card-clip"></i>
                                        <span>Register Authorized Editor</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
            
        </div>

        <!-- Core Features Section -->
        <div class="row g-4 text-start mt-5 pt-4" style="border-top: 1px solid rgba(255, 255, 255, 0.05);">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-wrapper text-primary">
                        <i class="fa-solid fa-map"></i>
                    </div>
                    <h4 class="fw-bold mb-3" style="color: #f8fafc;">Interactive Drafting</h4>
                    <p class="text-muted mb-0" style="font-size: 0.92rem; line-height: 1.6;">
                        Utilize Leaflet drawing toolkits to seamlessly drop custom pins, plot complex routes, and define geometric region boundaries with real-time feedback.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-wrapper text-warning">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <h4 class="fw-bold mb-3" style="color: #f8fafc;">Context & Attributes</h4>
                    <p class="text-muted mb-0" style="font-size: 0.92rem; line-height: 1.6;">
                        Attach unique descriptions, customized names, and full-resolution image attachments to each point, route or polygon for granular data representation.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-wrapper text-success">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <h4 class="fw-bold mb-3" style="color: #f8fafc;">Tabular Control</h4>
                    <p class="text-muted mb-0" style="font-size: 0.92rem; line-height: 1.6;">
                        Harness dedicated data sheets to search, filter, focus maps directly on selected shapes, or perform clean-slate purges in one click.
                    </p>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // If there are validation errors on register, switch to the register tab automatically
        @if ($errors->any() && old('name'))
            const registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
            registerTab.show();
        @endif
    });
</script>
@endpush


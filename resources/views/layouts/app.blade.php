<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CRM') — Fyrmosa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.0/dist/tabler-icons.min.css">
    <style>
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .navbar-brand-image {
            height: 2.25rem;
            width: auto;
            display: block;
        }

        .navbar-fyrmosa {
            background: #292929;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            min-height: 3.75rem;
            width: 100%;
            max-width: 100%;
            margin: 0;
            border-radius: 0;
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            box-sizing: border-box;
        }

        .navbar-fyrmosa::before {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 100vw;
            transform: translateX(-50%);
            background: #292929;
            z-index: -1;
            pointer-events: none;
        }

        .navbar-fyrmosa > .container-fluid {
            width: 100%;
            max-width: none;
            margin: 0;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .navbar-fyrmosa .navbar-brand {
            margin: 0;
            padding: 0.5rem 1.25rem 0.5rem 0;
        }

        .navbar-fyrmosa .nav-link {
            color: rgba(255, 255, 255, 0.78);
            font-weight: 500;
            padding: 0.55rem 0.85rem;
            border-radius: 0.4rem;
            gap: 0.4rem;
        }

        .navbar-fyrmosa .nav-link:hover,
        .navbar-fyrmosa .nav-link:focus {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }

        .navbar-fyrmosa .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }

        .navbar-fyrmosa .nav-link-icon {
            color: inherit;
            opacity: 0.9;
        }

        .navbar-fyrmosa .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .navbar-fyrmosa .navbar-toggler-icon {
            filter: invert(1);
        }

        .navbar-fyrmosa .navbar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-left: auto;
            padding-left: 1rem;
        }

        .navbar-fyrmosa .navbar-user-name {
            color: #fff;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .navbar-fyrmosa .btn-logout {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.35);
            background: transparent;
        }

        .navbar-fyrmosa .btn-logout:hover {
            color: #000;
            background: #fff;
            border-color: #fff;
        }

        .page {
            margin: 0;
            width: 100%;
            max-width: 100%;
        }

        .login-page {
            min-height: 100vh;
            background:
                linear-gradient(rgba(15, 23, 42, 0.72), rgba(15, 23, 42, 0.82)),
                url("{{ asset('images/background.jpg') }}") center / cover no-repeat fixed;
        }

        .login-logo {
            max-width: 16rem;
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    @auth
        <header class="navbar navbar-expand-md navbar-dark navbar-fyrmosa d-print-none">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Menú">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand mb-0">
                    <a href="{{ route('dashboard') }}">
                        <img class="navbar-brand-image" src="{{ asset('images/logoFyrmosa.webp') }}" alt="Fyrmosa CRM">
                    </a>
                </h1>
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="navbar-nav">
                        @if (auth()->user()->hasPermission(\App\Enums\Permission::DashboardView))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <span class="nav-link-icon"><i class="ti ti-layout-dashboard"></i></span>
                                    <span class="nav-link-title">Dashboard</span>
                                </a>
                            </li>
                        @endif
                        @can('viewAny', App\Models\Quotation::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}" href="{{ route('quotations.index') }}">
                                    <span class="nav-link-icon"><i class="ti ti-file-invoice"></i></span>
                                    <span class="nav-link-title">Cotizaciones</span>
                                </a>
                            </li>
                        @endcan
                        @can('viewAny', App\Models\Order::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                    <span class="nav-link-icon"><i class="ti ti-shopping-cart"></i></span>
                                    <span class="nav-link-title">Pedidos</span>
                                </a>
                            </li>
                        @endcan
                        @can('viewAny', App\Models\Client::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                                    <span class="nav-link-icon"><i class="ti ti-building"></i></span>
                                    <span class="nav-link-title">Clientes</span>
                                </a>
                            </li>
                        @endcan
                        @can('viewAny', App\Models\User::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <span class="nav-link-icon"><i class="ti ti-users"></i></span>
                                    <span class="nav-link-title">Usuarios</span>
                                </a>
                            </li>
                        @endcan
                        @can('viewAny', Spatie\Permission\Models\Role::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                    <span class="nav-link-icon"><i class="ti ti-lock"></i></span>
                                    <span class="nav-link-title">Roles</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                    <div class="navbar-user">
                        <span class="navbar-user-name">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-sm btn-logout" type="submit">Salir</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        <div class="page">
            <div class="page-wrapper">
                <div class="page-header d-print-none">
                    <div class="container-xl">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <h2 class="page-title">@yield('title')</h2>
                                @hasSection('subtitle')
                                    <div class="text-secondary mt-1">@yield('subtitle')</div>
                                @endif
                            </div>
                            <div class="col-auto ms-auto d-print-none">
                                <div class="btn-list">@yield('actions')</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="page-body">
                    <div class="container-xl">
                        @include('partials.alerts')
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="page page-center login-page">
            <div class="container container-tight py-4">
                @include('partials.alerts')
                @yield('content')
            </div>
        </div>
    @endauth
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
    @stack('scripts')
</body>
</html>

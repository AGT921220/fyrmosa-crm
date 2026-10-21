<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CRM') — Fyrmosa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.0/dist/tabler-icons.min.css">
    <style>
        .navbar-brand-image {
            height: 2rem;
            width: auto;
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
        <div class="page">
            <header class="navbar navbar-expand-md d-print-none">
                <div class="container-xl">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <h1 class="navbar-brand navbar-brand-autodark pe-3 mb-0">
                        <a href="{{ route('dashboard') }}">
                            <img class="navbar-brand-image" src="{{ asset('images/logoFyrmosa.webp') }}" alt="Fyrmosa CRM">
                        </a>
                    </h1>
                    <div class="collapse navbar-collapse" id="navbar-menu">
                        <ul class="navbar-nav">
                            @if (auth()->user()->hasPermission(\App\Enums\Permission::DashboardView))
                                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><span class="nav-link-icon"><i class="ti ti-layout-dashboard"></i></span><span class="nav-link-title">Dashboard</span></a></li>
                            @endif
                            @can('viewAny', App\Models\Quotation::class)
                                <li class="nav-item"><a class="nav-link" href="{{ route('quotations.index') }}"><span class="nav-link-icon"><i class="ti ti-file-invoice"></i></span><span class="nav-link-title">Cotizaciones</span></a></li>
                            @endcan
                            @can('viewAny', App\Models\Order::class)
                                <li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}"><span class="nav-link-icon"><i class="ti ti-shopping-cart"></i></span><span class="nav-link-title">Pedidos</span></a></li>
                            @endcan
                            @can('viewAny', App\Models\Client::class)
                                <li class="nav-item"><a class="nav-link" href="{{ route('clients.index') }}"><span class="nav-link-icon"><i class="ti ti-building"></i></span><span class="nav-link-title">Clientes</span></a></li>
                            @endcan
                            @can('viewAny', App\Models\User::class)
                                <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}"><span class="nav-link-icon"><i class="ti ti-users"></i></span><span class="nav-link-title">Usuarios</span></a></li>
                            @endcan
                            @can('viewAny', Spatie\Permission\Models\Role::class)
                                <li class="nav-item"><a class="nav-link" href="{{ route('roles.index') }}"><span class="nav-link-icon"><i class="ti ti-lock"></i></span><span class="nav-link-title">Roles</span></a></li>
                            @endcan
                        </ul>
                        <div class="navbar-nav flex-row order-md-last ms-md-auto">
                            <span class="nav-link">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-outline-secondary" type="submit">Salir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
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

@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <form class="card" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="card-body">
                    <h2 class="h3 mb-4">Fyrmosa CRM</h2>
                    <div class="mb-3">
                        <label class="form-label" for="nickname">Nickname</label>
                        <input class="form-control" id="nickname" type="text" name="nickname" value="{{ old('nickname') }}" required autofocus autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña</label>
                        <input class="form-control" id="password" type="password" name="password" required>
                    </div>
                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="1">
                        <span class="form-check-label">Recordarme</span>
                    </label>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary w-100" type="submit">Entrar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.app')
@section('title', 'Editar '.$order->folio)
@section('content')
    @include('orders._form')
@endsection

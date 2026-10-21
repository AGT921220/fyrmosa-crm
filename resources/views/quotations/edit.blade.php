@extends('layouts.app')
@section('title', 'Editar '.$quotation->folio)
@section('content')
    @include('quotations._form')
@endsection

{{-- Compatibilité : les pages légales utilisent désormais layouts.public --}}
@extends('layouts.public')

@section('title')
    @yield('title', 'Salang Group')
@endsection

@section('content')
    @yield('content')
@endsection

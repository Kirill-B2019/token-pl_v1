{{-- |KB Главная публичная страница: сборка секций Landing --}}
@extends('public.layouts.base')

@section('content')
    @include('public.partials.hero')
    @include('public.partials.stats')
    @include('public.partials.features')
    @include('public.partials.pricing')
    @include('public.partials.contact')
@endsection



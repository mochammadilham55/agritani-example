@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('heading', 'Laporan Penjualan')

@section('content')
    @include('reports._sales', ['route' => 'petani.reports.index'])
@endsection

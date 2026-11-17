@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('heading', 'Laporan Penjualan')

@section('content')
    @include('reports._sales', ['route' => 'admin.reports.index'])
@endsection

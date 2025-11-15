@extends('layouts.master')

@section('title', 'Manager List')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite([
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
])
@endsection

@section('vendor-script')
    @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite([
    'resources/assets/js/pages/BaseTable.js',
    'resources/assets/js/pages/ManagerTable.js',
])
@endsection


@section('content')
    <h4>
        <span class="fw-light">Manager List</span>
    </h4>

    <div class="card">
        <div class="card-datatable text-nowrap px-2">
            <table class="datatables-ajax table"></table>
        </div>
    </div>
@endsection

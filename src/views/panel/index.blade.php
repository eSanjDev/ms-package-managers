@extends('layouts.master')

@section('title', 'Manager List')

<!-- Vendor Styles -->
@section('vendor-style')
        <link rel="stylesheet" href="{{asset('assets/vendor/manager/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
        <link rel="stylesheet" href="{{asset('assets/vendor/manager/libs/datatables-bs5/datatables.bootstrap5.css')}}">
        <link rel="stylesheet" href="{{asset('assets/vendor/manager/libs/sweetalert2/sweetalert2.css')}}">
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.baseUrlApiAdmin = "{{config('esanj.manager.routes.api_prefix')}}"
        window.baseUrlAdmin = "{{config('esanj.manager.routes.panel_prefix')}}"
    </script>
    <script type="module" src="{{asset('assets/vendor/manager/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
    <script type="module" src="{{asset('assets/vendor/manager/libs/sweetalert2/sweetalert2.js')}}"></script>
    <script type="module" src="{{asset("assets/vendor/manager/js/ManagerTable.js")}}"></script>
@endsection


@section('content')
    <h4>
        <span class="fw-light">Manager List</span>
    </h4>

    <div class="card">
        <div class="card-datatable text-nowrap px-2">
            <table class="datatables-ajax table">
            </table>
        </div>
    </div>
@endsection

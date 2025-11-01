@extends('layouts.master')

@section('title', 'Manager List')

<!-- Vendor Styles -->
@section('vendor-style')
    <link rel="stylesheet"
          href="{{asset('assets/vendor/manager/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/manager/libs/datatables-bs5/datatables.bootstrap5.css')}}">
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.baseUrlApi = "{{config('esanj.manager.routes.api_prefix')}}"
        window.baseUrl = "{{config('esanj.manager.routes.panel_prefix')}}"
        window.manager_id = {{$manager->id}}
    </script>
    <script type="module"
            src="{{asset('assets/vendor/manager/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
    <script type="module" src="{{asset("assets/vendor/manager/js/ManagerActivityTable.js")}}"></script>
@endsection


@section('content')
    <h4>
        <span class="fw-light">Manager Activities '{{$manager->name}}'</span>
    </h4>

    <div class="card">
        <div class="card-datatable text-nowrap px-2">
            <input type="hidden" name="csrf" value="{{csrf_token()}}">
            <table class="datatables-ajax table"></table>
        </div>
    </div>

    <div class="modal fade" id="modalActivity" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-simple">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    <div class="text-center mb-6">
                        <h2 class="mb-2"></h2>
                        <span></span>
                    </div>
                    <p class="mb-6" style="justify-self: center"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

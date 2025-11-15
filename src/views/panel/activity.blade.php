@extends('layouts.master')

@section('title', 'Manager List')

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
    'resources/assets/packages/manager/js/ManagerActivityTable.js',
])
@endsection

@section('content')
    <h4>
        <span class="fw-light">Manager Activities '{{$manager->name}}'</span>
    </h4>

    <div class="card">
        <div class="card-datatable text-nowrap px-2">
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

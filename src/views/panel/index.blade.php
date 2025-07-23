@extends('layouts.layoutMaster')

@section('title', 'Manager List')

<!-- Vendor Styles -->
@section('vendor-style')
    {{--    @vite([--}}
    {{--      'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',--}}
    {{--      'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',--}}
    {{--      'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'--}}
    {{--    ])--}}
@stop

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.baseUrlApiAdmin = "{{config('manager.routes.api_prefix')}}"
        window.baseUrlAdmin = "{{config('manager.routes.panel_prefix')}}"
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment-jalaali@0.9.2/build/moment-jalaali.js"></script>
    <script type="module" src="{{asset("assets/vendor/manager/js/ManagerTable.js")}}"></script>
@stop


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

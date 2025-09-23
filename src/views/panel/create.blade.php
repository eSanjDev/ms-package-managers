@extends('layouts.layoutMaster')

@section('title', 'Create New Manager')

@section('page-style')
    <link rel="stylesheet" href="{{asset('assets/vendor/manager/css/style.css')}}">
@endsection

@section('page-script')
    <script>
        window.baseUrlApiAdmin = "{{config('esanj.manager.routes.api_prefix')}}"
    </script>
    <script src="{{asset("assets/vendor/manager/js/Manager.js")}}"></script>
@endsection

@section('content')
    <h4>Create New Manager</h4>
    <form action="{{route('managers.store')}}" class="row form-setting" method="post">
        @csrf
        <div class="row">
            <div class="col-lg-8" id="permissions">
                <div class="card p-6 mb-6">
                    <h3 class="mb-6">Permissions</h3>
                    @error('permissions')
                    <div class="text-danger">{{ $message }}</div> @enderror
                    <div class="table-responsive">
                        <table class="table table-flush-spacing">
                            <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex justify-content-start">
                                        <div class="form-check p-0 mb-0 d-flex align-items-center selectAll">
                                            <input class="form-check-input m-0" type="checkbox" id="selectAll">
                                            <label class="form-check-label text-heading" for="selectAll"> Select
                                                All </label>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex ">
                                        <div class="form-check ps-0">
                                            <h6 class="form-check-label text-heading"> Permission </h6>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                            @foreach($permissions as $index => $groups)
                                @foreach($groups as $key => $name)
                                    <tr>
                                        <td class="text-nowrap fw-medium text-heading">{{$name}}</td>
                                        <td>
                                            <div class="d-flex justify-content-evenly">
                                                <div class="form-check mb-0 ">
                                                    <input class="form-check-input" name="permissions[]"
                                                           @checked(in_array($key,old('permissions',[]))) type="checkbox"
                                                           value="{{$key}}" id="permission-{{$key}}">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-6 p-6 ">
                    <h3 class="mb-6">Configuration</h3>
                    <div class="col-12 mb-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" placeholder="Manager name" name="name"
                                   value="{{old('name')}}"/>
                            <label>Name</label>
                            @error('name')
                            <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" placeholder="Esanj ID"
                                   value="{{old('esanj_id')}}" name="esanj_id"/>
                            <label>Esanj ID</label>
                            @error('esanj_id')
                            <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-12 position-relative select-box mb-4">
                        <div class="form-floating">
                            <select name="is_active" class="form-select select2">
                                <option @selected(old('is_active') == 1) value="1">Active
                                </option>
                                <option @selected(old('is_active') == 0) value="0">Inactive
                                </option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>
                    <div class="col-12 position-relative select-box mb-4">
                        <div class="form-floating">
                            <select name="uses_token" class="form-select select2">
                                <option @selected(old('uses_token') == 1) value="1">Yes
                                </option>
                                <option @selected(old('uses_token') == 0) value="0">No
                                </option>
                            </select>
                            <label>Required Token</label>
                        </div>
                    </div>
                    <div class="col-12 position-relative select-box mb-4">
                        <div class="form-floating">
                            <select name="role" class="form-select select2">
                                @foreach($roles as $role)
                                    <option value="{{$role}}" selected>{{$role}}</option>
                                @endforeach
                            </select>
                            <label>Role</label>
                            @error('role')
                            <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    @if($isAdmin)
                        <div class="col-12 mb-4">
                            <div class="input-group input-group-lg">
                                <button type="button" class="btn btn-outline-dark" id="regenerate">
                                    <i class="icon-base ti ti-refresh"></i>
                                </button>
                                <input type="password" class="form-control" name="token" placeholder="Token" readonly/>
                                <button type="button" class="btn btn-outline-dark toggle-show-token">
                                    <i class="icon-base ti ti-eye-off"></i>
                                </button>
                            </div>
                            @error('token')
                            <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="col-12 text-end demo-vertical-spacing">
                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

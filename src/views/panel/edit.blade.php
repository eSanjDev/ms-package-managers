@extends('layouts.layoutMaster')

@section('title', 'Edit Manager')

@section('page-style')
    <link rel="stylesheet" href="{{asset('assets/vendor/manager/css/style.css')}}">
@endsection

@section('page-script')
    <script>
        window.baseApi = "{{config('manager.routes.api_prefix')}}"
    </script>
    <script src="{{asset('assets/vendor/manager/js/Manager.js')}}"></script>
@endsection

@section('content')
    <h4>Edit Manager</h4>
    <form action="{{route('managers.update',$manager->id)}}" class="row form-setting" method="post">
        @csrf
        @method('put')
        <div class="row">
            <div class="col-lg-8 {{$manager->role->value == 'admin' ? "d-none":""}}" id="permissions">
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
                                                           @checked(in_array($key,old('permissions',$managerPermissions))) type="checkbox"
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
                                   value="{{old('name',$manager->name)}}"/>
                            <label>Name</label>
                            @error('name')
                            <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" placeholder="Esanj ID"
                                   value="{{$manager->esanj_id}}" readonly/>
                            <label>Esanj ID</label>
                        </div>
                    </div>
                    <div class="col-12 position-relative select-box mb-4">
                        <div class="form-floating">
                            <select name="is_active" class="form-select">
                                <option @selected(old('is_active', $manager->is_active) == "1") value="1">Active
                                </option>
                                <option @selected(old('is_active', $manager->is_active) == "0")value="0">diactive
                                </option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>
                    <div class="col-12 position-relative select-box mb-4">
                        <div class="form-floating">
                            <select name="api_access" class="form-select">
                                <option @selected(old('api_access', $manager->api_access) === 1) value="1">Yes
                                </option>
                                <option @selected(old('api_access', $manager->api_access) === 0) value="0">No
                                </option>
                            </select>
                            <label>Api access</label>
                        </div>
                    </div>
                    <div class="col-12 position-relative select-box mb-4">
                        <div class="form-floating">
                            <select name="role" class="form-select"
                                    @if($manager->role->value === 'admin' && !$isAdmin) readonly @endif>
                                @foreach($roles as $role)
                                    <option
                                            @selected(old('role',$manager->role->value) === $role) value="{{$role}}">{{$role}}</option>
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
                                <span class="input-group-text cursor-pointer"><i class="icon-base ti ti-refresh"
                                                                                 id="regenerate"></i></span>
                                <input type="password" class="form-control" name="token" placeholder="Token" readonly/>
                                <span class="input-group-text cursor-pointer toggle-show-token"><i
                                            class="icon-base ti ti-eye-off"></i></span>
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
    <div class="row">
        <div class="col-lg-12">
            @foreach(config('manager.extra_blade') as $item)
                @include($item)
            @endforeach
        </div>
    </div>
@endsection

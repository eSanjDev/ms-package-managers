@extends('layouts.layoutMaster')

@section('title', 'Create New Manager')

@section('page-style')
    <link rel="stylesheet" href="{{asset('assets/vendor/manager/css/style.css')}}">
@endsection

@section('page-script')
    <script>
        window.baseUrlApiAdmin = "{{config('manager.routes.api_prefix')}}"
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
                            <select name="is_active" class="form-select">
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
                            <select name="role" class="form-select">
                                @foreach($roles as $role)
                                    <option @selected(old('role') === $role) value="{{$role}}"
                                            selected>{{$role}}</option>
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
                                <span class="input-group-text cursor-pointer">
                                    <i class="icon-base ti ti-refresh" id="regenerate"></i>
                                </span>
                                <input type="password" class="form-control" value="{{$token}}" name="token"
                                       placeholder="Token" readonly/>
                                <span class="input-group-text cursor-pointer toggle-show-token">
                                    <i class="icon-base ti ti-eye-off"></i>
                                </span>
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




@section('contednt')
    <h4>
        <span class="fw-light"></span>
    </h4>

    <form class="needs-validation" action="{{route('managers.store')}}" method="post">
        @csrf
        <div class="card">
            <div class="card-body">
                {{-- Esanj Id --}}
                <div class="form-group mb-3">
                    <label>Esanj ID</label>
                    <input type="number" name="esanj_id" value="{{old('esanj_id')}}" class="form-control"
                           placeholder="Enter Esanj ID">
                    @error('esanj_id')
                    <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                {{-- Role --}}
                <div class="form-group mb-3">
                    <label>Role</label>
                    <select name="role" id="role" class="form-control">
                        <option value="" selected hidden>Choose...</option>
                        @foreach($roles as $role)
                            <option @selected(old('role') == $role) value="{{$role}}">{{$role}}</option>
                        @endforeach
                    </select>
                    @error('role')
                    <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                @if($isAdmin)
                    {{-- Token --}}
                    <div class="form-group mb-3">
                        <label>Token</label>
                        <div class="input-group">
                        <span class="input-group-text cursor-pointer">
                            <i class="icon-base ti ti-refresh" id="regenerate"></i></span>
                            <input type="password" name="token" value="{{old('token')??$token}}" readonly
                                   class="form-control input-token">
                            <span class="input-group-text cursor-pointer toggle-show-token"><i
                                    class="icon-base ti ti-eye-off"></i></span>
                        </div>
                        @error('token')
                        <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                @endif

                {{-- is_active --}}
                <div class="form-group mb-3">
                    <label>Status</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input"
                               @checked(old('is_active')) id="status-switch">
                        <label class="form-check-label" for="status-switch">Active</label>
                    </div>
                </div>

                @if($permissions)
                    <hr>
                    <div class="row align-items-center mb-2" id="permissions">
                        <lable>Permissions</lable>
                        @error('permissions')
                        <div class="text-danger">{{ $message }}</div> @enderror
                        @foreach($permissions as $index => $groups)
                            <div class="col-md-6 mt-2">
                                @foreach($groups as $key => $name)
                                    <div class="form-check form-check-primary mt-4">
                                        <input class="form-check-input" name="permissions[]"
                                               @checked(in_array($key,old('permissions',[]))) value="{{$key}}"
                                               type="checkbox"
                                               id="permission-{{$key}}">
                                        <label class="form-check-label" for="permission-{{$key}}">{{$name}}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                    </div>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('managers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
@endsection

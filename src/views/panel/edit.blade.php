@php
    $configData = \App\Helpers\Helpers::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Edit Manager')


@section('page-script')
    <script>
        window.baseApi = "{{config('manager.routes.api_prefix')}}"
    </script>
    <script src="{{asset("assets/vendor/manager/js/Manager.js")}}"
@endsection


@section('content')
    <h4>
        <span class="fw-light">Edit Manager</span>
    </h4>

    <form class="needs-validation" method="post" action="{{route('managers.update',$manager->id)}}">
        @csrf
        @method('put')
        <div class="card">
            <div class="card-body">

                {{-- Esanj Id --}}
                <div class="form-group mb-3">
                    <label>Esanj ID</label>
                    <input type="number" value="{{$manager->esanj_id}}" class="form-control"
                           placeholder="Enter Esanj ID" readonly>
                    @error('esanj_id')
                    <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                {{-- Role --}}
                <div class="form-group mb-3">
                    <label>Role</label>
                    <select name="role" id="role" class="form-control"
                            @if($manager->role->value === 'admin' && !$isAdmin) readonly @endif>
                        <option value="" selected hidden>Choose...</option>
                        @foreach($roles as $role)
                            <option
                                    @selected(old('role',$manager->role->value) === $role) value="{{$role}}">{{$role}}</option>
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
                            <input type="password" name="token" readonly class="form-control input-token">
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
                        <input type="checkbox" name="is_active"
                               @checked(old('is_active', $manager->is_active)) class="form-check-input"
                               id="status-switch">
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
                                        <input class="form-check-input"
                                               @checked(in_array($key,old('permissions',$managerPermissions))) name="permissions[]"
                                               type="checkbox"
                                               value="{{$key}}"
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
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('managers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>

@endsection

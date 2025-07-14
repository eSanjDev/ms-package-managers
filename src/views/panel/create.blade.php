@extends('layouts.layoutMaster')

@section('title', 'Create New Manager')

@section('page-script')
    <script>
        window.baseApi = "{{config('manager.routes.api_prefix')}}"
    </script>
    <script src="{{asset("assets/vendor/manager/js/Manager.js")}}"
@endsection

@section('content')
    <h4>
        <span class="fw-light">Create New Manager</span>
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

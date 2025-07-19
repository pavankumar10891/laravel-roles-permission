@extends('admin.layouts.adminapp')

@section('content')

<div class="content"> 
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-primary">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item" ><a href="{{ route('roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create </li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-default">
                    <div class="card-body">
                        <form method="POST" action="{{ route('roles.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="fname">Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name') }}" required>
                                        @error('name')
                                        <div class="text-danger small mt-1">
                                           {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <h5 class="mt-4">Assign Permissions:</h5>

                            @php
                                $groupedPermissions = [];

                                foreach ($permissions as $permission) {
                                    $permName = is_object($permission) ? $permission->name : $permission;
                                    $groupKey = explode('-', $permName)[0]; // e.g. "user", "role"
                                    $groupedPermissions[$groupKey][] = $permission;
                                }
                            @endphp

                            @foreach ($groupedPermissions as $group => $groupPermissions)
                                <div class="border rounded p-3 mb-4">
                                    <h5 class="text-capitalize mb-3 border-bottom pb-2">{{ $group }} Permissions</h5>
                                    <div class="row">
                                        @foreach ($groupPermissions as $permission)
                                            <div class="col-md-3 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ is_object($permission) ? $permission->name : $permission }}"
                                                        class="custom-control-input"
                                                        id="permission_{{ is_object($permission) ? $permission->id : $loop->parent->index.'_'.$loop->index }}">
                                                    <label class="custom-control-label"
                                                        for="permission_{{ is_object($permission) ? $permission->id : $loop->parent->index.'_'.$loop->index }}">
                                                        {{ is_object($permission) ? ucfirst(str_replace('-', ' ', $permission->name)) : ucfirst(str_replace('-', ' ', $permission)) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div class="form-footer pt-5 ">
                                <button type="submit" class="btn btn-primary btn-pill">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

</div>
@endsection
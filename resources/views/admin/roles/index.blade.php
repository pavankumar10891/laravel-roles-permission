@extends('admin.layouts.adminapp')

@section('content')
<div class="content"> 
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-primary">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" ><a href="#">Roles</a></li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-xl-12">
                <!-- Sales by Product -->
                <div class="card card-default">
                    <div class="card-header align-items-center">
                    <h2 class="">Roles</h2>
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-pill">Add Role</a>
                    </div>
                    <div class="card-body">
                    <div class="tab-content">
                        <table id="product-sale" class="table table-product " style="width:100%">
                        <thead>
                            <th>#</th>
                            <th>Role Name</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>

                            @foreach ($roles as $index => $role)
                                <tr>
                                    <td>{{ $roles->firstItem() + $index }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        @foreach($role->permissions as $permission)
                                            <span class="badge badge-primary">{{ $permission->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        
                                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="mdi mdi-square-edit-outline mr-1"></i>
                                        </a>
                                        {{-- <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="delete"><i class=" mdi mdi-close-circle-outline mr-1"></i></button>
                                        </form> --}}
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $roles->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                    </div>
                </div>
            </div>    
        </div>
</div>
@endsection
@extends('admin.layouts.adminapp')

@section('content')
<div class="content"> 
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-primary">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" ><a href="#">Customers</a></li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-xl-12">
                <!-- Sales by Product -->
                <div class="card card-default">
                    <div class="card-header align-items-center">
                    <h2 class="">Roles</h2>
                        {{-- <a href="{{ route('users.create') }}" class="btn btn-primary btn-pill">Add User</a> --}}
                    </div>
                    <div class="card-body">
                    <div class="tab-content">
                        <table id="product-sale" class="table table-product " style="width:100%">
                        <thead>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>

                            @foreach ($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->mobile }}</td>
                                    <td>
                                        <a href="{{ route('customers.show', $user->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="mdi mdi-eye mr-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $users->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                    </div>
                </div>
            </div>    
        </div>
</div>
@endsection
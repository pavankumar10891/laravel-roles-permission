@extends('admin.layouts.adminapp')

@section('content')

<div class="content"> 
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-primary">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item" ><a href="{{ route('users.index') }}">Users</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create </li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-default">
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name') }}" required>
                                        @error('name')
                                        <div class="text-danger small mt-1">
                                           {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}">
                                        @error('email')
                                        <div class="text-danger small mt-1">
                                           {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="mobile">Mobile</label>
                                        <input type="text" name="mobile" class="form-control" placeholder="Mobile" value="{{ old('mobile') }}">
                                        @error('mobile')
                                        <div class="text-danger small mt-1">
                                           {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="mobile">Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Password" value="{{ old('password') }}">
                                        @error('password')
                                        <div class="text-danger small mt-1">
                                           {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Password" value="{{ old('password_confirmation') }}">
                                        @error('password')
                                        <div class="text-danger small mt-1">
                                           {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        

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
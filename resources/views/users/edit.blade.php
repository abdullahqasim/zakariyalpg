@extends('layouts.base')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User</h1>
@stop

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit User</h3>
                <div class="card-tools">
                    {{-- <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">Add User</a> --}}
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Email (Optional)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                    </div>

                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" class="form-control" required>
                            <option value="customer" @selected($user->type == 'customer')>Customer</option>
                            <option value="supplier" @selected($user->type == 'supplier')>Supplier</option>
                            {{-- <option value="distributor" @selected($user->type == 'distributor')>Distributor</option> --}}
                            {{-- <option value="admin" @selected($user->type == 'admin')>Admin</option> --}}
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>
@stop

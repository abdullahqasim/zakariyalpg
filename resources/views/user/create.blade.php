@extends('layouts.base')

@section('title', 'Add User')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Add User</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add User</h3>
                <div class="card-tools">
                    {{-- <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">Add User</a> --}}
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
            
                    <div class="form-group">
                        <label>Email (Optional)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
            
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="customer">Customer</option>
                            <option value="supplier">Supplier</option>
                            {{-- <option value="distributor">Distributor</option> --}}
                            {{-- <option value="admin">Admin</option> --}}
                        </select>
                    </div>
            
                    <button type="submit" class="btn btn-success">Save</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>
@stop

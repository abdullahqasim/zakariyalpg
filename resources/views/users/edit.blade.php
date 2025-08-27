@extends('layouts.base')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User</h1>
@stop

@section('content')
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
                <option value="distributor" @selected($user->type == 'distributor')>Distributor</option>
                <option value="admin" @selected($user->type == 'admin')>Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@stop

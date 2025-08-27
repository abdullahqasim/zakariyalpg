@extends('layouts.base')

@section('title', 'Add User')

@section('content_header')
    <h1>Add User</h1>
@stop

@section('content')
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
                <option value="distributor">Distributor</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@stop

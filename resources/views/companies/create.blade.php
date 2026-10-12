@extends('layouts.layout')

@section('title', 'Companies')

@section('content')
<div class="container">
    <h1>Add New Company</h1>
    <form action="{{ route('companies.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Company Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Company</button>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'تعديل المستخدم - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit"></i>
                        تعديل المستخدم: {{ $user->name }}
                    </h4>
                    <div>
                        <a href="{{ route('advanced-users.show', $user) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i>
                            عرض التفاصيل
                        </a>
                        <a href="{{ route('advanced-users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('advanced-users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- المعلومات الشخصية -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user-circle"></i>
                                    المعلومات الشخصية
                                </h5>

                                <div class="mb-3">
                                    <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
  
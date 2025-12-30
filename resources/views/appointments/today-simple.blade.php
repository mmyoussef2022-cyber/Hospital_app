@extends('layouts.app')

@section('page-title', 'مواعيد اليوم')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>مواعيد اليوم - اختبار</h5>
                </div>
                <div class="card-body">
                    <p>عدد المواعيد: {{ $appointments->count() }}</p>
                    
                    @if($appointments->count() > 0)
                        <ul>
                            @foreach($appointments as $appointment)
                                <li>
                                    {{ $appointment->patient->name ?? 'مريض غير محدد' }} - 
                                    {{ $appointment->doctor->name ?? 'طبيب غير محدد' }} - 
                                    {{ $appointment->appointment_time->format('H:i') }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>لا توجد مواعيد اليوم</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
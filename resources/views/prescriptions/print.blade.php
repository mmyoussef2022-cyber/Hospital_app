<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.prescription') }} - {{ $prescription->patient->name }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
            line-height: 1.6;
        }
        
        .prescription-header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .prescription-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .prescription-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .info-section {
            flex: 1;
            min-width: 250px;
            margin: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        
        .info-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 120px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
        }
        
        .prescription-details {
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
            background: #f8f9ff;
        }
        
        .medication-name {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #007bff;
        }
        
        .prescription-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .prescription-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        
        .prescription-item strong {
            color: #007bff;
            display: block;
            margin-bottom: 5px;
        }
        
        .instructions-section {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }
        
        .warnings-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .warnings-section strong {
            color: #856404;
        }
        
        .controlled-badge {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        
        .prescription-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .doctor-signature {
            text-align: center;
            min-width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .print-date {
            font-size: 12px;
            color: #666;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .prescription-info {
                flex-direction: column;
            }
            
            .info-section {
                margin: 5px 0;
                break-inside: avoid;
            }
            
            .prescription-details {
                break-inside: avoid;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .prescription-info {
            direction: rtl;
        }
        
        [dir="rtl"] .info-row {
            direction: rtl;
        }
        
        [dir="rtl"] .prescription-grid {
            direction: rtl;
        }
        
        [dir="rtl"] .prescription-footer {
            direction: rtl;
        }
    </style>
</head>
<body>
    <div class="prescription-header">
        <div class="hospital-name">{{ config('app.name', 'Hospital Management System') }}</div>
        <div class="prescription-title">{{ __('app.prescription') }}</div>
        <div class="print-date">{{ __('app.prescription_date') }}: {{ $prescription->created_at->format('Y-m-d H:i') }}</div>
    </div>

    <div class="prescription-info">
        <!-- Patient Information -->
        <div class="info-section">
            <div class="info-title">{{ __('app.patient_information') }}</div>
            <div class="info-row">
                <span class="info-label">{{ __('app.name') }}:</span>
                <span class="info-value">{{ $prescription->patient->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('app.patient_number') }}:</span>
                <span class="info-value">{{ $prescription->patient->patient_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('app.age') }}:</span>
                <span class="info-value">{{ $prescription->patient->age }} {{ __('app.years') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('app.gender') }}:</span>
                <span class="info-value">{{ __('app.' . $prescription->patient->gender) }}</span>
            </div>
        </div>

        <!-- Doctor Information -->
        <div class="info-section">
            <div class="info-title">{{ __('app.doctor_information') }}</div>
            <div class="info-row">
                <span class="info-label">{{ __('app.doctor') }}:</span>
                <span class="info-value">{{ $prescription->doctor->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('app.specialization') }}:</span>
                <span class="info-value">{{ $prescription->doctor->specialization ?? __('app.not_specified') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('app.prescription_date') }}:</span>
                <span class="info-value">{{ $prescription->created_at->format('Y-m-d') }}</span>
            </div>
        </div>
    </div>

    <!-- Prescription Details -->
    <div class="prescription-details">
        <div class="medication-name">
            {{ $prescription->medication_name }}
            @if($prescription->medication_name_ar)
                <br><small style="font-size: 14px; color: #666;">{{ $prescription->medication_name_ar }}</small>
            @endif
        </div>

        <div class="prescription-grid">
            <div class="prescription-item">
                <strong>{{ __('app.dosage') }}</strong>
                {{ $prescription->dosage }}
            </div>
            
            <div class="prescription-item">
                <strong>{{ __('app.frequency') }}</strong>
                {{ $prescription->frequency }}
                @if($prescription->frequency_ar)
                    <br><small style="color: #666;">{{ $prescription->frequency_ar }}</small>
                @endif
            </div>
            
            <div class="prescription-item">
                <strong>{{ __('app.duration') }}</strong>
                {{ $prescription->duration_days }} {{ __('app.days') }}
            </div>
            
            <div class="prescription-item">
                <strong>{{ __('app.start_date') }}</strong>
                {{ $prescription->start_date }}
            </div>
            
            <div class="prescription-item">
                <strong>{{ __('app.end_date') }}</strong>
                {{ $prescription->end_date }}
            </div>
            
            <div class="prescription-item">
                <strong>{{ __('app.status') }}</strong>
                {{ __('app.' . $prescription->status) }}
            </div>
        </div>

        @if($prescription->instructions)
            <div class="instructions-section">
                <strong>{{ __('app.instructions') }}:</strong>
                <p>{{ $prescription->instructions }}</p>
                @if($prescription->instructions_ar)
                    <p style="color: #666; font-style: italic;">{{ $prescription->instructions_ar }}</p>
                @endif
            </div>
        @endif

        @if($prescription->warnings)
            <div class="warnings-section">
                <strong>{{ __('app.warnings') }}:</strong>
                <p>{{ $prescription->warnings }}</p>
                @if($prescription->warnings_ar)
                    <p style="color: #666; font-style: italic;">{{ $prescription->warnings_ar }}</p>
                @endif
            </div>
        @endif

        @if($prescription->pharmacy_notes)
            <div class="instructions-section">
                <strong>{{ __('app.pharmacy_notes') }}:</strong>
                <p>{{ $prescription->pharmacy_notes }}</p>
                @if($prescription->pharmacy_notes_ar)
                    <p style="color: #666; font-style: italic;">{{ $prescription->pharmacy_notes_ar }}</p>
                @endif
            </div>
        @endif

        @if($prescription->is_controlled_substance)
            <div class="controlled-badge">
                {{ __('app.controlled_substance') }}
            </div>
        @endif
    </div>

    <div class="prescription-footer">
        <div class="print-date">
            {{ __('app.print') }}: {{ now()->format('Y-m-d H:i') }}
        </div>
        
        <div class="doctor-signature">
            <div class="signature-line">
                {{ __('app.doctor') }} {{ $prescription->doctor->name }}
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
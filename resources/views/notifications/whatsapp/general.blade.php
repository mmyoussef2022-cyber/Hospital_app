🏥 *{{ config('app.name') }}*

📢 *{{ $notification->title }}*

{{ $notification->message }}

@if(isset($data['appointment_date']))
📅 التاريخ: {{ $data['appointment_date'] }}
⏰ الوقت: {{ $data['appointment_time'] }}
@endif

@if(isset($data['doctor_name']))
👨‍⚕️ الطبيب: د. {{ $data['doctor_name'] }}
@endif

@if(isset($data['amount']))
💰 المبلغ: {{ number_format($data['amount'], 2) }} ريال
@endif

---
نظام إدارة المستشفى
{{ now()->format('Y-m-d H:i') }}
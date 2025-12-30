<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .priority-critical {
            border-left: 5px solid #dc3545;
        }
        .priority-high {
            border-left: 5px solid #fd7e14;
        }
        .priority-normal {
            border-left: 5px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 {{ config('app.name') }}</h1>
            <h2>{{ $notification->title }}</h2>
        </div>
        
        <div class="content priority-{{ $notification->priority }}">
            <p>مرحباً {{ $recipient->name }}،</p>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                {{ $notification->message }}
            </div>
            
            @if($notification->data)
                <div style="margin: 20px 0;">
                    @foreach($notification->data as $key => $value)
                        @if(is_string($value))
                            <p><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</p>
                        @endif
                    @endforeach
                </div>
            @endif
            
            <p>شكراً لاستخدامكم نظام إدارة المستشفى.</p>
        </div>
        
        <div class="footer">
            <p>تم الإرسال في {{ now()->format('Y-m-d H:i') }}</p>
            <p>نظام إدارة المستشفى - جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>
</html>
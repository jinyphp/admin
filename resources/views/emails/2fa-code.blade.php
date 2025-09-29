<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA 인증 코드</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .code-box {
            background-color: #f3f4f6;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #1f2937;
            margin: 10px 0;
        }
        .expires {
            color: #ef4444;
            font-weight: 500;
            margin: 15px 0;
        }
        .info {
            color: #6b7280;
            font-size: 14px;
            margin: 20px 0;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name', 'Laravel') }}</div>
            <h2>2FA 인증 코드</h2>
        </div>

        <p>안녕하세요, {{ $user->name }}님</p>
        
        <p>요청하신 2단계 인증 코드를 안내드립니다:</p>

        <div class="code-box">
            <div class="info">아래 6자리 코드를 입력하세요</div>
            <div class="code">{{ $code }}</div>
            <div class="expires">⏱️ {{ $expires_in }}분 후 만료됩니다</div>
        </div>

        <div class="warning">
            <strong>⚠️ 보안 주의사항:</strong><br>
            • 이 코드를 다른 사람과 공유하지 마세요<br>
            • 요청하지 않은 코드라면 계정 보안 설정을 확인하세요<br>
            • 코드가 만료되면 새로운 코드를 요청해야 합니다
        </div>

        <div class="info">
            <p>이 이메일은 {{ now()->format('Y-m-d H:i:s') }}에 발송되었습니다.</p>
            <p>IP 주소: {{ request()->ip() }}</p>
        </div>

        <div class="footer">
            <p>본 메일은 2단계 인증을 위해 자동으로 발송된 메일입니다.</p>
            <p>문의사항이 있으시면 관리자에게 연락해주세요.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
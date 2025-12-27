{{--
|==============================================================================
| Password Change OTP Template - Ng Wayne Xiang (User & Authentication Module)
|==============================================================================
|
| @author     Ng Wayne Xiang
| @module     User & Authentication Module
|
| Email template for OTP verification during password change.
| Sent via Supabase when user requests password reset.
|==============================================================================
--}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .otp-box {
            background: white;
            border: 2px dashed #667eea;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">🔒 Password Change Request</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>You have requested to change your password for your FoodHunter account.</p>
        
        <p>Please use the following One-Time Password (OTP) to verify this change:</p>
        
        <div class="otp-box">
            <div style="color: #666; font-size: 14px; margin-bottom: 10px;">Your OTP Code</div>
            <div class="otp-code">{{ $otp }}</div>
            <div style="color: #666; font-size: 12px; margin-top: 10px;">Valid for 10 minutes</div>
        </div>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>This OTP will expire in 10 minutes</li>
                <li>Never share this code with anyone</li>
                <li>If you didn't request this change, please ignore this email and secure your account</li>
            </ul>
        </div>
        
        <p style="margin-top: 30px;">
            <strong>Need help?</strong><br>
            If you didn't request this password change, please contact our support team immediately.
        </p>
        
        <p style="margin-top: 20px;">
            Best regards,<br>
            <strong>FoodHunter Team</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} FoodHunter - TARUMT Canteen Food Ordering System</p>
    </div>
</body>
</html>

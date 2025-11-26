<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your One-Time Password</title>
</head>
<body style="font-family: Inter, sans-serif; line-height: 1.6; color: #2E2E2E; background-color: #F5F3F7; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #7639C2; font-family: 'Zilla Slab', serif; font-size: 28px; margin: 0;">Zurit CRM</h1>
        </div>
        
        <h2 style="color: #2E2E2E; font-family: 'Zilla Slab', serif; font-size: 24px; margin-bottom: 20px;">Welcome, {{ $userName }}!</h2>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            Your account has been created. Please use the following one-time password to log in:
        </p>
        
        <div style="background-color: #F5F3F7; border: 2px solid #7639C2; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;">
            <div style="font-size: 36px; font-weight: bold; color: #7639C2; letter-spacing: 8px; font-family: monospace;">
                {{ $otp }}
            </div>
        </div>
        
        <p style="color: #6B6B6B; font-size: 14px; margin-bottom: 10px;">
            <strong>Important:</strong> After logging in with this password, you will be required to set a new password for your account.
        </p>
        
        <p style="color: #6B6B6B; font-size: 14px; margin-top: 30px;">
            This password will expire in 24 hours. If you did not request this account, please contact your administrator.
        </p>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #F5F3F7; text-align: center;">
            <p style="color: #6B6B6B; font-size: 12px; margin: 0;">
                © {{ date('Y') }} Zurit Consulting. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>


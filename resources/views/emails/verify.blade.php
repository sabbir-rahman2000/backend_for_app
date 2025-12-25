<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code - Zhengzhou University</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            padding: 20px;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5f8d 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background-color: #ffffff;
            border-radius: 50%;
            padding: 10px;
        }
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #1e3a5f;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .message {
            color: #4a5568;
            font-size: 15px;
            margin-bottom: 15px;
        }
        .code-container {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
        }
        .code-label {
            font-size: 13px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .code {
            font-size: 36px;
            font-weight: 700;
            color: #1e3a5f;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .expiry {
            font-size: 13px;
            color: #ef4444;
            margin-top: 10px;
            font-weight: 500;
        }
        .info-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box p {
            color: #92400e;
            font-size: 14px;
            margin: 0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 25px 30px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        .footer p {
            color: #64748b;
            font-size: 13px;
            margin: 5px 0;
        }
        .signature {
            margin-top: 20px;
            color: #1e3a5f;
            font-weight: 600;
        }
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #cbd5e1, transparent);
            margin: 25px 0;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .code {
                font-size: 28px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <img src="{{ asset('images/university-logo.png') }}" alt="Zhengzhou University">
            </div>
            <h1>Zhengzhou University</h1>
            <p>Campus Pre-owned Market</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hello, {{ $user->name }}!</div>
            
            @if($type === 'reset')
                <p class="message">
                    We received a request to reset your password for your Zhengzhou University Campus Pre-owned Market account. 
                    To proceed with resetting your password, please use the verification code below:
                </p>
            @else
                <p class="message">
                    Thank you for joining the Zhengzhou University Campus Pre-owned Market platform. 
                    To complete your registration and verify your email address, please use the verification code below:
                </p>
            @endif

            <!-- Verification Code -->
            <div class="code-container">
                <div class="code-label">
                    @if($type === 'reset')
                        Your Password Reset Code
                    @else
                        Your Verification Code
                    @endif
                </div>
                <div class="code">{{ $code }}</div>
                <div class="expiry">⏰ Expires in 2 minutes</div>
            </div>

            @if($type === 'reset')
                <p class="message">
                    Enter this code on the password reset screen to create a new password for your account.
                </p>
            @else
                <p class="message">
                    Simply enter this code in the verification screen to activate your account and start exploring 
                    the marketplace.
                </p>
            @endif

            <!-- Info Box -->
            <div class="info-box">
                <p>
                    <strong>⚠️ Security Notice:</strong> 
                    @if($type === 'reset')
                        If you did not request a password reset, please ignore this email and ensure your account is secure.
                    @else
                        If you did not create an account with us, please disregard this email. Your information is safe, and no account has been created.
                    @endif
                </p>
            </div>

            <div class="divider"></div>

            <p class="signature">
                Best regards,<br>
                Campus Pre-owned Market Team<br>
                Zhengzhou University
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated message from Zhengzhou University Campus Pre-owned Market.</p>
            <p>Please do not reply directly to this email.</p>
            <p style="margin-top: 15px; color: #94a3b8;">
                © {{ date('Y') }} Zhengzhou University. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Verification Code</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 24px; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 12px 20px; border-radius: 6px; text-decoration: none; }
        p { color: #333; }
    </style>
    </head>
<body>
    <div class="container">
        <h2>Hi {{ $user->name }},</h2>
        <p>Thanks for signing up for Campus Pre-owned Market.</p>
        <p>Please use the verification code below to verify your email address:</p>
        <h1 style="letter-spacing: 4px;">{{ $code }}</h1>
        <p>This code will expire in 15 minutes.</p>
        <p>If you didn't create an account, you can safely ignore this email.</p>
        <hr>
        <p>Best regards,<br>Campus Pre-owned Market Team</p>
    </div>
</body>
</html>

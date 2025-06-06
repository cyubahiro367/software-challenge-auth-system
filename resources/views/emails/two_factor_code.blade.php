<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Verification Code</title>
    <style>
        .container {
            font-family: Arial, sans-serif;
            background-color: #f7f8fa;
            padding: 20px;
            border-radius: 8px;
            max-width: 480px;
            margin: auto;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
        }

        .code-box {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin: 20px 0;
            padding: 16px;
            background-color: #ffffff;
            border: 1px dashed #2c3e50;
            border-radius: 4px;
            letter-spacing: 6px;
        }

        .footer {
            font-size: 13px;
            color: #888;
            text-align: center;
            margin-top: 30px;
        }

        h2 {
            color: #2c3e50;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Hello!</h2>
    <p>Thank you for registering. Please use the following code to verify your email address. This code will expire in 10 minutes.</p>

    <div class="code-box">{{ $code }}</div>

    <p>If you did not initiate this request, please ignore this email.</p>

    <div class="footer">
        &copy; {{ date('Y') }} Authentication System. All rights reserved.
    </div>
</div>
</body>
</html>

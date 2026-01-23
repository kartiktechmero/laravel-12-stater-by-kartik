<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        /* Reset default styles for email clients */
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        .header img {
            max-width: 160px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .header h1 {
            margin: 10px 0 0;
            font-size: 26px;
            font-weight: 600;
            color: #1a73e8;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666666;
        }
        .content {
            padding: 30px 20px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            font-size: 14px;
            color: #666666;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #1a73e8;
            text-decoration: none;
            margin: 0 12px;
            font-weight: 500;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .footer .social-icons img {
            width: 24px;
            height: 24px;
            margin: 10px 8px;
            display: inline-block;
        }
        pre {
            background: #f4f4f4;
            padding: 12px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 14px;
            color: #333333;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 10px;
                padding: 10px;
                box-shadow: none;
            }
            .header {
                padding: 15px;
            }
            .header img {
                max-width: 120px;
            }
            .header h1 {
                font-size: 20px;
            }
            .header p {
                font-size: 12px;
            }
            .content {
                padding: 20px 15px;
            }
            .footer {
                padding: 15px;
                font-size: 12px;
            }
            .footer a {
                margin: 0 8px;
            }
            .footer .social-icons img {
                width: 20px;
                height: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <!-- Logo: Replace with your actual logo path -->
        {{--        <img src="{{ config('app.url') }}/images/logo.png" alt="{{ config('app.name') }} Logo">--}}
        <h1>{{ $subject ?? config('app.name') }}</h1>
        <p>Thank you for being a part of {{ config('app.name') }}</p>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>
            <a href="{{ config('app.url') }}">Home</a> |
        </p>
        <p style="color: #999999; margin-top: 15px;">
            This is an automated message. Please do not reply directly to this email.
        </p>
    </div>
</div>
</body>
</html>

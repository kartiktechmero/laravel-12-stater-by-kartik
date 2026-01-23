@extends('emails.base_mail')

@section('content')
    <h2>Exception Occurred</h2>
    <p><strong>Message:</strong> {{ $mail_message }}</p>
    <p><strong>File:</strong> {{ $file }} (Line {{ $line }})</p>
    <p><strong>URL:</strong> {{ $url }}</p>
    <p><strong>Method:</strong> {{ $method }}</p>
    <p><strong>IP:</strong> {{ $ip }}</p>
    <p><strong>User Agent:</strong> {{ $user_agent }}</p>
    <p><strong>User ID:</strong> {{ $user_id }}</p>
    <p><strong>User Email:</strong> {{ $user_email }}</p>
    <p><strong>Environment:</strong> {{ $app_env }}</p>
    <p><strong>PHP Version:</strong> {{ $php_version }}</p>
    <h4>Input:</h4>
    <pre>{!! $input !!}</pre>
    <h4>Trace:</h4>
    <pre>{!! $trace !!}</pre>
@endsection

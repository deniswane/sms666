<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registration confirmation</title>
</head>
<body>
<h1>Thank you for registering with sms-receive website.</h1>

<p>
    Please click the link below to complete the registration：
    <a href="{{ $link = url('verification', $user->verification_token) . '?email=' . urlencode($user->email) }}"> {{ $link }}</a>

</p>

<p>
    If this is not your operation, please ignore this message.
</p>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset password request</title>
</head>
<body>
    <h1>{{$data['heading']}}</h1>
    <strong>Dear {{$data['name']}}</strong>
    <p>Here is the verification code, to reset your password <strong>{{$data['code']}}</strong></p>
    <br><br>

    Regards {{env('APP_NAME')}}
</body>
</html>

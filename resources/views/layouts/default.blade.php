<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    {{--<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">--}}
    <meta name="description"
          content="Receive SMS online , without Registration and without use your personal phone number.Numbers from United Kingdom, Romania ,United States,Spain,France,Germany">
    <meta name="keywords"
          content="SMS receive,SMS receive  online,sms receive uk,sms recevive romania,united kingdom, sms united states,free sms germany, sms Ukraine,spain,sms spain,sms germany,germany">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes"/>
    <title>@yield('title', 'SMS Receive Online')</title>
    <link href="/favicon.ico" rel="icon" type="image/x-icon">

    <link href="{{ URL::asset('css/default.css') }}" rel="stylesheet" type="text/css"/>

    <link rel="canonical" href="#">
    <script type="text/javascript" src="{{ URL::asset('js/policy.js') }}"></script>
    <script src="{{ URL::asset('js/jquery.min.js') }}"></script>
    <link href="/layui/css/layui.css" rel="stylesheet" type="text/css"/>
    <script src="/layui/layui.js" type="text/javascript"></script>
    <script src="https://www.paypalobjects.com/api/checkout.js"></script>
    <noscript>
        <div style="position:absolute;top:50px;left:100px;background-color:black;color:red; font-size:16px;"> Your browser
            does not support JavaScript!<br>Without JavaScript, this web page will not be displayed correctly!
        </div>
    </noscript>
</head>
<body>
    @include('layouts._header')
    @yield('content')
    @include('layouts._footer')
</body>
<script type="text/javascript">if (top.location != location) top.location.href = location.href;</script>
<script>
    function myFunction() {
        document.getElementsByClassName("topnav")[0].classList.toggle("responsive");
        var a = document.getElementById("home-link");
        if (a.style.zIndex == '999') a.style.zIndex = '1000'; else a.style.zIndex = '999';
    }
</script>

</body>
</html>



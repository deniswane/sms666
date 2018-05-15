<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    {{--<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">--}}
    <meta name="description"
          content="Receive SMS online for FREE, without Registration and without use your personal phone number.Numbers from United Kingdom, Romania ,United States,Spain,France,Germany">
    <meta name="keywords"
          content="receive SMS,receive SMS online,free receive sms,receive sms uk,recevive sms romania,united kingdom, sms united states,free sms germany, sms Ukraine,spain,sms spain,sms germany,germany">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes"/>
    <meta property="og:title"
          content="Receive SMS Online for FREE | NO Registration UK,Romania,USA,Spain,France,Germany,Russia">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://www.sms-receive-online.info/">
    <meta property="og:image" content="http://www.receive-sms-online.info/img/receive_sms.png">
    <meta name="msvalidate.01" content="CB3DC9D646F1A44B5CE15B6B1D3074F7">
    <title>@yield('title', 'Receive SMS Online')</title>
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



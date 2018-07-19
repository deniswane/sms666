<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="description"
          content="Read SMS: Receive SMS online for FREE - 919654766051  : India - without Registration and without use your personal phone number.">
    <meta name="keywords"
          content="receive SMS,receive SMS online,free receive sms,receive free sms,receive no registration">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="Receive SMS Online for FREE | NO Registration: 919654766051"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="http://www.receive-sms-online.info/"/>
    <meta property="og:image" content="http://www.receive-sms-online.info/img/receive_sms.png"/>
    <link href="/favicon.ico" rel="icon" type="image/x-icon"/>
    <link href="{{ URL::asset('css/default.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="canonical" href="https://www.receive-sms-online.info">
    <script type="text/javascript" src="{{ URL::asset('js/policy.js') }}"></script>
    <script src="{{ URL::asset('js/jquery.min.js') }}"></script>
</head>
<body>
{{--@include('shared._social')--}}
@include('layouts._header')
<div class="demoTable" style="text-align: center ;margin-top:200px"  >
    搜索手机信息：
    <div class="layui-inline">
        <input class="layui-input" name="phone" placeholder="输入手机号" id="phone" autocomplete="off">
    </div>
    <button class="layui-btn " onclick="searchPhone()">搜索</button>
    <a href="">下载今日数据</a> 下载昨日数据
</div>

</body>
</html>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="/static/client/layui/layui.js"></script>
<script type="text/javascript" src="/static/client/js/index.js"></script>

<script>
    // 获取手机号短信内容

    function searchPhone() {
        var phone = $('#phone').val();
        if (IsMobilePhoneNumber(phone)) {
            $.ajax({
                type: 'post',
                url: "{{route('client.index')}}",
                cache: false,
                data: {phone: phone, _token: "{{csrf_token()}}"},
                success: function (data) {
                    layer.alert(data, {'title': '最新信息','offset':'150px'})
                },
                error: function (data) {
                }
            });
        } else {
            layer.alert('格式不对')
        }

    }
    //验证手机号
    function IsMobilePhoneNumber(input) {
        var regex = /^((\+)?86|((\+)?86)?)0?1[3458]\d{9}$/;
        if (input.match(regex)) {
            return true;
        } else {
            return false;
        }
    }
</script>
</script>
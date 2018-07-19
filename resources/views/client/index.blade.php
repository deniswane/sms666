<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>搜索短信内容</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/static/cfcc/layui/css/layui.css" media="all" />
</head>
<body style="background-color: #F8F8F8">

    <div class="demoTable" style="text-align: center ;margin-top:200px"  >
        搜索手机信息：
        <div class="layui-inline">
            <input class="layui-input" name="phone" placeholder="输入手机号" id="phone" autocomplete="off">
        </div>
        <button class="layui-btn " onclick="searchPhone()">搜索</button>
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
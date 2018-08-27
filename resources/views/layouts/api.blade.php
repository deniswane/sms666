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
    <link rel="stylesheet" href="/static/client/css/laydate.css">
    <script type="text/javascript" src="{{ URL::asset('js/policy.js') }}"></script>
    <script src="{{ URL::asset('js/jquery.min.js') }}"></script>
    <script src="/static/client/js/laydate.js"></script>
</head>
<body>
{{--@include('shared._social')--}}
@include('layouts._header')

<noscript>
    <div style="position:absolute;top:50px;left:100px;background-color:black;color:red; font-size:16px;"> Your browser
        does not support JavaScript!<br>Without JavaScript, this web page will not be displayed correctly!
    </div>
</noscript>
<div class="panel">
    <h4 style="">1. http://sms-receive-online.info/manager/api/keyword?k=k1:k2&token=your token&p=province<br></h4>
    <h4 style="">return {"code":200,"msg":"success"}<br></h4>
    <h4 style=""> 2.http://sms-receive-online.info/manager/api/inside/getPhoneNumber?token=your token&p=province<br>
    </h4>

    <h4 style="">return {"code":200,"msg":1xxxxxxxxxx}<br></h4>
    <h4 style=""> 3.http://sms-receive-online.info/manager/api/inside/getSmsContent?token=your token&phone=The phone
        number obtained from the second step<br></h4>
    <h4 style=""> return {"code":200,"msg":"something"}<br></h4>
    <h4>your token : {{$token}}</h4>
    <div>
        Search for mobile information：
        <input  name="phone" placeholder="phone number" id="phone" autocomplete="off">
        <button  onclick="searchPhone()">search</button>
    </div>
    <br>

    <div class="laydate-box">

        <form action="download" method="post">
            Download data：
            <input type="text" id="laydateInput" placeholder="date"/>
            {{csrf_field()}}
            <input type="text" hidden name="time" value="{{time()}}">
            <input type="text" hidden id="date" name='date' value="{{date('Ymd',time())}}"/>

            <button >Download</button>

        </form>
        <div class="select-date">
            <div class="select-date-header">
                <ul class="heade-ul">
                    <li class="header-item header-item-one">
                        <select name="" id="yearList"></select>
                    </li>
                    <li class="header-item header-item-two" onselectstart="return false">
                        <select name="" id="monthList"></select>
                    </li>
                    <li class="header-item header-item-three" onselectstart="return false">
                        <span class="reback">now</span>
                    </li>
                </ul>
            </div>
            <div class="select-date-body">
                <ul class="week-list">
                    <li>Su</li>
                    <li>Mo</li>
                    <li>Tu</li>
                    <li>We</li>
                    <li>Th</li>
                    <li>Fr</li>
                    <li>Sa</li>
                </ul>
                <ul class="day-tabel"></ul>
            </div>
        </div>
    </div>
</div>
{{--<span id="date" hidden >{{date('Ymd',time())}}</span>--}}
<script>
    function getSelectDate(result) {
        $('#date').val(result);
    }


</script>


<script type="text/javascript">if (top.location != location) top.location.href = location.href;</script>
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
                    layer.alert(data, {'title': 'new message', 'offset': '150px', 'btn': 'ok'})
                },
                error: function (data) {
                }
            });
        } else {
            layer.alert('Format is wrong', {'title': 'new message', 'offset': '150px', 'btn': 'ok'})
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
</body>
</html>

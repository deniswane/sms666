<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <title>后台管理系统</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/static/cfcc/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/cfcc/css/style.css" media="all">
</head>

<body onload="loadTopWindow()">
<div class="login layui-anim-up">
    <div class="login-main">
        <div class="login-box login-header">
            <h2>{{ config('app.name')}}</h2>
            <p>后台管理系统</p>
        </div>
        <div class="login-box login-body">
            <form action="" class=" layui-form">
                <div class="layui-form-item">
                    <label class="login-icon layui-icon layui-icon-username" for="name"></label>
                    <input type="text" name="name" id="name" lay-verify="required" placeholder="用户名" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label class="login-icon layui-icon layui-icon-password" for="password"></label>
                    <input type="password" name="password" id="password"  lay-verify="required" placeholder="密码" class="layui-input">
                </div>

                <hr>
                <div class="layui-form-item" style="margin-bottom: 20px;">
                    <input type="checkbox" name="remember" value="1" lay-skin="primary" title="记住密码">
                </div>
                <div class="layui-form-item">
                    <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="layform">登 陆</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="login-footer">
    <hr>

</div>
<script src="/js/jquery.min.js"></script>
<script src="/static/cfcc/layui/layui.js"></script>
<script type="text/javascript">
    layui.config({
        base: '/static/cfcc/js/'
    }).use('lea');
    $(document).ready(function() {
        $('#vercode').click(function() {
            var src = "{{ captcha_src('flat') }}";
            $(this).attr('src', src + '?' + Math.random());
        });
        $('#vercode').click();
    });
</script>
<script language="JavaScript">
    //判断当前窗口是否有顶级窗口，如果有就让当前的窗口的地址栏发生变化，
    //这样就可以让登陆窗口显示在整个窗口了
    function loadTopWindow(){
        if (window.top!=null && window.top.document.URL!=document.URL){
            window.top.location= document.URL;
        }
    }
</script>
</head>
<!--在body的写上onload事件要调用的方法-->
</body>

</html>
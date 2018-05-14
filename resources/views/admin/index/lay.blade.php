<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="utf-8">
    <title>@yield('title','后台管理')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/static/admin/layui/css/layui.css">
    <link rel="stylesheet" href="/static/admin/plugins/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/static/admin/css/style.css">
    <script type="text/javascript" src="/static/admin/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/admin/layui/layui.js"></script>

</head>

<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin" layui-layout="{{ session('menu_status','open') }}">
    <div class="layui-header">
        <div class="layui-logo">
            <span>管理系统</span>
        </div>
        <!-- 头部区域 -->
        <ul class="layui-nav layui-layout-left">
            <li class="layui-nav-item layadmin-flexible" lay-unselect>
                <a href="" class="ajax-flexible" title="侧边伸缩">
                    <i class="layui-icon layui-icon-shrink-right"></i>
                </a>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" id="refresh" title="刷新数据">
                    <i class="layui-icon layui-icon-refresh"></i>
                </a>
            </li>

            <li class="layui-nav-item" lay-unselect onclick="flush()">
                <a href="" title="清空缓存" >
                    <i class="layui-icon">&#xe61d;</i></a>
            </li>

        </ul>
        <ul class="layui-nav  layui-layout-right">

            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" class="user">{{Auth::guard('admin')->user()->name}}<i class="layui-icon layui-icon-more-vertical"></i></a>
                <dl class="layui-nav-child">
                    <dd><a href="{{ route('me') }}"><i class="fa  fa-user"></i> 更改密码</a></dd>
                    <hr>
                    <dd><a href="{{route('cfcc.logout')}}"><i class="fa fa-sign-out"></i> 退出</a></dd>
                </dl>
            </li>
        </ul>
        {{--<ul class="layui-nav  layui-layout-right">--}}
            {{--<li class="layui-nav-item" >--}}
                {{--<a href="/" >网站首页</a>--}}
            {{--</li>--}}
            {{--<li class="layui-nav-item">--}}
                {{--<a href="javascript:;" >退出</a>--}}
            {{--</li>--}}
        {{--</ul>--}}
        <ul class="layui-nav  layui-layout-right">

        </ul>
    </div>

    <div class="aside">
        <div class="aside-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="aside-nav">

                <li>
                    <a href="{{route('cfcc.bal')}}"><i class="fa fa-code"></i> 客户余额</a>
                    <a href="{{route('cfcc.set_money')}}"><i class="fa fa-code"></i> 设置请求费用</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="main">

        <div class="main-content">
            <div class="layui-fluid" style="padding: 0 12px;">
                <div class="layui-card">

                    @yield('content')

                </div>
            </div>
        </div>
        <div class="main-footer">
            <!-- 底部固定区域 -->
            Copyright © 2016-{{ date('Y') }} 基于 LeaCMF 后台管理系统. All rights reserved.
        </div>
    </div>
</div>

<script type="text/javascript">
    layui.config({
        base: '/static/admin/js/'
    }).use('lea');
    function flush() {
        $.ajax({
            type: 'get',
            url: "{{route('flush')}}",
            cache: false,
            success: function (data) {
                layer.alert('清楚缓存成功', {icon: 6});
            }
        });
    }
    $('#refresh').click(function () {
        window.location.reload()
    })
</script>
@yield('script')
</body>

</html>
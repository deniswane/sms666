<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="utf-8">
    <title>@yield('title','后台管理')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    {{--<meta name="csrf-token" content="{{ csrf_token() }}">--}}
    <link rel="stylesheet" href="/static/admin/layui/css/layui.css">
    <link rel="stylesheet" href="/static/admin/plugins/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/static/admin/css/style.css">
    <script type="text/javascript" src="/static/admin/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/admin/layui/layui.js"></script>
    {{--@yield('style')--}}
</head>

<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin" layui-layout="{{ session('menu_status','open') }}">
    <div class="layui-header">
        <div class="layui-logo">
            <span>{{ env('APP_NAME') }} 管理系统</span>
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
            @role('super admin')
            <li class="layui-nav-item" lay-unselect>
                <a href="{{route('flush')}}" class="ajax-post" title="清空缓存">
                    <i class="fa fa-magic"></i>
                </a>
            </li>
            @endrole
        </ul>
        <ul class="layui-nav  layui-layout-right">
            <li class="layui-nav-item" lay-unselect="">
                <a lay-href="app/message/" layadmin-event="message">
                    <i class="layui-icon layui-icon-notice"></i>
                    <span class="layui-badge-dot"></span>
                </a>
            </li>
            <li class="layui-nav-item" lay-unselect>
                {{--<a href="javascript:;" class="user"><img src="" class="layui-nav-img"> <i class="layui-icon layui-icon-more-vertical"></i></a>--}}
                <dl class="layui-nav-child">
                    <dd><a href=""><i class="fa  fa-user"></i> 个人信息</a></dd>
                    <hr>
                    <dd><a href=""><i class="fa fa-sign-out"></i> 退出</a></dd>
                </dl>
            </li>
        </ul>
    </div>
    {{--@php $__NAV__ = Auth::user()->getNav();@endphp--}}
    <div class="aside">
        <div class="aside-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="aside-nav">

                <li>
                    <a href="{{route('admin.bal')}}"><i class="fa fa-code"></i> 客户余额</a>
                </li>
                {{--<li><a href="{{route('admin.info')}}"><i class="fa fa-code"></i> 消息记录</a></li>--}}{{--@endrole--}}
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

{{--<script type="text/javascript">--}}
    {{--layui.config({--}}
        {{--base: '/static/admin/js/'--}}
    {{--}).use('lea');--}}
{{--</script>--}}
@yield('script')
</body>

</html>
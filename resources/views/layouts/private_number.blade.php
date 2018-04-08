<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>layout 后台大布局 - Layui</title>
    <link href="{{ URL::asset('css/layui.css') }}" rel="stylesheet" type="text/css"/>
    <script src="{{ URL::asset('js/layui.js') }}"></script>

</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo">layui 后台布局</div>
        <!-- 头部区域（可配合layui已有的水平导航） -->

        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">
                <a href="javascript:;">
                    <img src="http://t.cn/RCzsdCq" class="layui-nav-img">
                    贤心
                </a>
                <dl class="layui-nav-child">
                    <dd><a href="">基本资料</a></dd>
                    <dd><a href="">安全设置</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item"><a href="">退了</a></li>
        </ul>
    </div>

    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="layui-nav layui-nav-tree"  lay-filter="test">

                <li class="layui-nav-item"><a href="javascript:void(0)" onclick="myTab()">API接口文档</a></li>
                <li class="layui-nav-item"><a href="javascript:;">价格</a></li>
            </ul>
        </div>
    </div>

    <div class="layui-body">
        <!-- 内容主体区域 -->
        <div style="padding: 15px;">内容主体区域</div>
    </div>

    <div class="layui-footer">
        <!-- 底部固定区域 -->
        © layui.com - 底部固定区域
    </div>
</div>
<script>
    //JavaScript代码区域
    layui.use('layer', function(){
        var layer = layui.layer;

        //window.location = "http://www.baidu.com";
    });
    function myTab() {
        layer.tab({
            area: ['600px', '300px'],
            tab: [{
                title: 'API接口',
                content: 'API接口API接口API接口API接口API接口API接口API接口API接口API接口'
            }, {
                title: '价格',
                content: '价格价格价格价格价格价格价格价格价格价格价格价格价格'
            }]
        });
    }

</script>
</body>
</html>

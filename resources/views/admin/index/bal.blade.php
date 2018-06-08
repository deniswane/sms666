@extends('admin.index.lay')
{{--<meta name="csrf-token" content="{{ csrf_token() }}">--}}
@section('title','用户余额')
@section('content')

    <div class="demoTable">
        搜索用户：
        <div class="layui-inline">
            <input class="layui-input" name="keyword" id="demoReload" autocomplete="off">
        </div>
        <button class="layui-btn " data-type="reload">搜索</button>
    </div>
    <table id="test" lay-filter="test"></table>
    <script>
        layui.use('table', function () {

            var table = layui.table;
            table.render({
                //请求后台获取数据
                request: {
                    pageName: 'curr' //页码的参数名称，默认：page
                    , limitName: 'nums' //每页数据量的参数名，默认：limit
                }
                ,height: 'full-200'
                ,elem: '#test'
                ,limits: ['25', '30']
                ,limit: 25
                ,id: 'testReload'
                ,page: true
                ,url: "{{route('cfcc.test')}}"
                ,method:'post'
                ,where:{'_token':"{{ csrf_token() }}"}
             ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
                , cols: [[
                    {title: '序号', align: 'center', width: 80,  templet: '#indexTpl'}
                    , {field: 'name', align: 'center', title: '用户名'}
                    , {field: 'email', align: 'center', title: '邮箱'}
                    , {field: 'balance', align: 'center', title: '余额',event: 'setSign', sort: true,style:"cursor:pointer "}
                    , {field: 'daliy_amount', align: 'center', title: '今日请求次数', sort: true}
                    , {field: 'amounts', align: 'center', title: '总的请求次数', sort: true}
                    , {field: 'yes_num', align: 'center', title: '昨天的统计数量', sort: true}
                    , {field: 'updated_at', align: 'center', title: '更新时间'} //minWidth：局部定义当前单元格的最小宽度，layui 2.2.1 新增
                    , {field: 'created_at', align: 'center', title: '注册时间'}
                ]]
            });
            //监听单元格事件
            table.on('tool(test)', function (obj) {
                var data = obj.data;
                if (obj.event === 'setSign') {
                    layer.prompt({
                        formType: 2
                        ,title: '设置余额'
                        ,value: data.balance
                    }, function(value, index){
                        layer.close(index);
                        //这里一般是发送修改的Ajax请求
                        $.ajax({
                            type: 'post',
                            url: "{{route('cfcc.set_bal')}}",
                            cache: false,
                            data: {balance:value,email:data.email,_token:"{{csrf_token()}}"},
                            success: function (data) {
                                console.log(data)

                                //同步更新表格和缓存对应的值
                                if(data.code != 200){
                                    layer.alert('请填写数字')
                                }else{
                                    obj.update({
                                        balance: value
                                    });
                                }
                            },
                            error:function (data) {
                                layer.alert(data.msg)

                            }
                        });
                        obj.update({
                            sign: value
                        });
                    });
                }
            });

            //表格重载
            var $ = layui.$, active = {
                reload: function () {
                    var demoReload = $('#demoReload');

                    table.reload('testReload', {
                        where: {
                            user: demoReload.val()
                        }
                    });
                }
            };

            $('.demoTable .layui-btn').on('click', function () {
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        });
    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">查看</a>
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

    <script type="text/html" id="indexTpl">
    @{{d.LAY_TABLE_INDEX+1}}
    </script>

@stop
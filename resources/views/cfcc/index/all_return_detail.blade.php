@extends('cfcc.index.lay')
@section('content')
    <blockquote class="layui-elem-quote quoteBox">

        <div class="layui-form demoTable">
            <div class="layui-form-item">
                开始时间：
                <div class="layui-inline">
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" id="start">
                    </div>
                </div>
                结束时间：
                <div class="layui-inline">
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" id="end" >
                    </div>
                </div>
                类型：
                <div class="layui-inline">
                    <div class="layui-input-inline">
                        <select name="province" lay-verify="required" id="type" lay-search="">
                                <option value="0">京东</option>
                                <option value="1">淘宝</option>
                        </select>
                    </div>
                </div>
                <button class="layui-btn " data-type="reload">搜索</button>

            </div>
        </div>
    </blockquote>
    <div style="text-align: center;">
        <div class="layui-inline">
            <table id="content" lay-filter="content"></table>
        </div>
    </div>


    <script>
        layui.use(['laydate','table'], function () {
            var laydate = layui.laydate;
            var table = layui.table;

            //时间选择
            laydate.render({
                elem: '#start'
                , value: new Date()
                , calendar: true
            });
            laydate.render({
                elem: '#end'
                , value: new Date()
                , calendar: true
            });

            //表格加载
            table.render({
                //请求后台获取数据
                request: {
                    pageName: 'curr' //页码的参数名称，默认：page
                    , limitName: 'nums' //每页数据量的参数名，默认：limit
                },
                done:function(res){
                        if(res.data !=''){
                            var sudcess =res.result.jd_success*1+res.result.tb_success*1,
                                    fail=res.result.jd_fail*1+res.result.tb_fail*1
                            $('tbody').append('<tr><td align="center">合计</td><td align="center">成功：'+sudcess+'&nbsp;&nbsp;失败：'+fail+'</td><td align="center"></td>' +
                                    '<td align="center">京东成功：'+res.result.jd_success*1+'&nbsp;&nbsp;淘宝成功：'+res.result.tb_success*1+'</td></tr>')

                        }
                         },
                elem: '#content'
                ,id: 'testReload'
                ,url: "{{route('cfcc.all_return_detail')}}"
                ,width:'800'
                ,page: true //开启分页
                ,align:'center'
                ,method:'post'
                ,where:{'_token':"{{ csrf_token() }}"}
                , cols: [[
                    {field: 'phone',align: 'center', title: '手机号'}
                    , {field: 'result', align: 'center', title: '结果'}
                    , {field: 'created_at', align: 'center', title: '时间', sort: true}
                    , {field: 'type', align: 'center', title:'类型',templet: '#res_type'}
                ]]


            });
            //表格重载
            var $ = layui.$, active = {
                reload: function () {
                    var start = $('#start').val();
                    var end = $('#end').val();
                    var type = $('#type').val();

                    table.reload('testReload', {
                        where: {
                            start:start,
                            end:end,
                            type:type,
                        }
                    });
                }
            };

            $('.demoTable .layui-btn').on('click', function () {
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });


        })
    </script>
    <script type="text/html" id="res_type">
        @{{#  if(d.type == '0'){ }}
        京东
        @{{#  } else { }}
        淘宝
       @{{#  } }}
    </script>



@endsection
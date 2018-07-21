{{--总数据统计--}}
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
                省份：
                <div class="layui-inline">
                    <div class="layui-input-inline">
                        <select name="province" lay-verify="required" id="province" lay-search="">
                            @foreach($provinces as $province)
                                <option value="{{$province['province']}}">{{$province['province']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button class="layui-btn " data-type="reload">搜索</button>

            </div>
        </div>
    </blockquote>
    <table id="content" lay-filter="content"></table>

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
                , value: new Date(new Date().getTime() + 24*60*60*1000)
                , calendar: true
            });

            //表格加载
            table.render({
                //请求后台获取数据
//                request: {
////                    pageName: 'curr' //页码的参数名称，默认：page
////                    , limitName: 'nums' //每页数据量的参数名，默认：limit
//                }
                done:function(res){
                   // console.log(res)
                    var arrs=res.data,
                    n=arrs.length,
                    count_total=count_num=0;
                    for (i=0;i<n;i++){
                        if(typeof(arrs[i].total) !='undefined') count_total +=arrs[i].total;
                        if(typeof(arrs[i].number) !='undefined') count_num   +=arrs[i].number;
                    }
//                    console.log(count_total,count_num)
                    $('tbody').append('<tr><td align="center">合计</td><td align="center">'+count_total+'</td><td align="center">'+count_num+'</td></tr>')
                },
                elem: '#content'
                ,id: 'testReload'
                ,url: "{{route('cfcc.month_detail')}}"
                ,method:'post'
                ,where:{'_token':"{{ csrf_token() }}"}
                , cols: [[
                    {field: 'province', align: 'center', title: '省份'}
                    , {field: 'total', align: 'center', title: '取走内容', sort: true}
                    , {field: 'number', align: 'center', title: '取走手机号', sort: true}
                    , {field: 'success', align: 'center', title:'成功率',sort: true,templet: '#success'}
                ]]


            });
            //表格重载
            var $ = layui.$, active = {
                reload: function () {
                    var start = $('#start').val();
                    var end = $('#end').val();
                    var province = $('#province').val();

                    table.reload('testReload', {
                        where: {
                            start:start,
                            end:end,
                            province:province,
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
    <script type="text/html" id="success">
    @{{#  if(d.number !=undefined){ }}
        @{{(d.total/d.number*100).toFixed(2)}}%
     @{{#  } }}
    </script>
@endsection
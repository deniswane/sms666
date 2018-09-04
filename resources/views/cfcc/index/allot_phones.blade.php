@extends('cfcc.index.lay')
@section('content')
    <blockquote class="layui-elem-quote quoteBox">
       可分配手机号：
        @foreach($data as $key => $value)
            @if($key !='截取')
            {{$key}} : {{$value['un_allot']}}&nbsp;
                @endif()
        @endforeach

        {{--东方头条：1000 京东20000 探探100000 淘宝100000 火牛10000--}}
    </blockquote>
    <div style="text-align: center;">

        <div class="layui-inline">
            <table id="test" lay-filter="test" ></table>
        </div>
    </div>
    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status"  value="@{{d.code}}" lay-skin="switch" lay-text="开|关" lay-filter="sexDemo" @{{ d.status =='1' ? 'checked' : '' }}>
    </script>

    <script>
        layui.use(['table'] ,function () {
            var table = layui.table;
            form=   layui.form;
            $ = layui.jquery

            var tableIns=table.render({
                //请求后台获取数据
                request: {
                    pageName: 'curr' //页码的参数名称，默认：page
                    , limitName: 'nums' //每页数据量的参数名，默认：limit
                }
                ,elem: '#test',
                done :function(res){
                },
                 width:'800'
                ,limits: ['25', '30']
                ,limit: 25
                ,page: true
                ,align:'center'
                ,id: 'testReload'
                ,url: "{{route('cfcc.allot_phones')}}"
                ,method:'post'
                ,where:{'_token':"{{ csrf_token() }}"}
                ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
                , cols: [[
                     {field: 'name', align: 'center', title: '用户'}
                    ,{field:'type_name', align:'center' ,title:'类型'}
                    ,{field:'num', title:'数量', align:'center',event: 'setDateTimes',templet: '#num'}
                    ,{fixed: 'right', title:'操作',width:178, align:'center', toolbar: '#barDemo'}

                ]]
            });
            //列表操作
            table.on('tool(test)', function(obj){
                var layEvent = obj.event,
                    data = obj.data;
                    console.log(data)
                if (obj.event === 'setDateTimes') {
                    layer.prompt({
                        formType: 2
                        ,title: '输入数量'
                        ,value: data.num
                    }, function(value, index){
                        layer.close(index);
                        //这里一般是发送修改的Ajax请求
                        $.ajax({
                            type: 'put',
                            url: "{{route('cfcc.allot_phones')}}",
                            cache: false,
                            data: {num:value,max:data.num,id:data.id,_met:'edit',_token:"{{csrf_token()}}"},
                            success: function (data) {
                                //同步更新表格和缓存对应的值
                                if(data.code != 200){
                                    layer.msg(data.msg)
                                }else{
                                    layer.msg(data.msg)
                                    location.reload()
                                    obj.update({
                                        num: value
                                    });
                                }
                            },
                            error:function (data) {

                            }
                        });
                        obj.update({
                            // sign: value
                        });
                    });
                }

                if(obj.event==='del'){
                  $.ajax({
                        url: '{{route("cfcc.allot_phones")}}',

                        data:{user_id:data.user_id,type_id:data.type_id,_met:'del',_token:"{{csrf_token()}}"},
                        async: true,
                        cache: false,
                        type: "put",
                        dataType: "json",
                        success: function(result){
                            layer.msg(result.msg)
                            location.reload()
                        }
                    });
                }
            });
        });

    </script>
    <script type="text/html" id="num">
       <span style="color:#A2A2A2; "> 可分配：@{{ d.num }} &nbsp;  待用:@{{ d.alloat }}</span>
    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">清零</a>
    </script>
@endsection
@extends('cfcc.index.lay')
@section('content')
    <blockquote class="layui-elem-quote quoteBox">
        <form class="layui-form">
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal addLink_btn">添加用户</a>
            </div>

        </form>
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
                ,page: true
                ,limits: ['25', '30']
                ,limit: 25
                ,elem: '#test',
                done :function(res){
                },
                width:'1000'
                ,align:'center'
                ,id: 'testReload'
                ,url: "{{route('cfcc.user_manager')}}"
                ,method:'post'
                ,where:{'_token':"{{ csrf_token() }}"}
                ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
                , cols: [[
                    {field: 'id', align: 'center', width: 80,  title: '用户id'}
                    , {field: 'name', align: 'center', title: '用户名'}
                    , {field: 'email', align: 'center', title: '邮箱'}
                    , {field: 'token', align: 'center', title: 'token'}
                    ,{fixed: 'right', title:'操作',width:178, align:'center', toolbar: '#barDemo'}
                ]]
            });
            //列表操作
            table.on('tool(test)', function(obj){
                var layEvent = obj.event,
                    data = obj.data;

                if(layEvent === 'edit'){ //编辑
                    addLink(data);
                } else if(layEvent === 'del'){ //删除
                    layer.confirm('确定删除该用户吗？',{icon:3, title:'提示信息'},function(index){
                        $.post( "{{route('cfcc.user_manager_delete')}}",{
                            id : data.id,
                            _token:"{{csrf_token()}}"
                        },function(res){
                            layer.msg(res.msg);
                            tableIns.reload();
                            layer.close(index);
                        })

                    });
                }else if(layEvent ==='reset'){//重置token
                    layer.confirm('确定重置token吗？',{icon:3, title:'提示信息'},function(index) {
                        $.post( "{{route('cfcc.user_manager_reset')}}",{
                            id : data.id,
                            _token:"{{csrf_token()}}"
                        },function(res){
                            layer.msg(res.msg);
                            tableIns.reload();
                            layer.close(index);
                        })
                    })
                    }
            });




            //添加友链
            function addLink(edit){
                var title;
                if (edit){
                    title= '修改用户信息';
                    $('#type').hide()
                } else{
                    title ='添加用户'
                }
                var index = layer.open({
                    title :title,
                    type : 2,
                    area : ["40%","50%"],
                    content : "{{route('cfcc.user_manager_list')}}",
                    success : function(){
                        var body = $($(".layui-layer-iframe",parent.document).find("iframe")[0].contentWindow.document.body);
                        if(edit){

                            body.find(".name").val(edit.name);
                            body.find(".email").val(edit.email);
                            body.find(".password").val(edit.password);
                            body.find(".re_password").val(edit.re_password);
                            body.find("#fenlei").hide();
                            body.find("#danjia").hide();


                            body.find("#_method").val('PUT');
                            form.render();
                        }
                    }
                })
            }
            $(".addLink_btn").click(function(){
                addLink();
            })

        });

    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="reset">重置token</a>
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

    <script type="text/html" id="statusTpl">
        @{{#  if(d.verified == '0'){ }}
        未激活
        @{{#  } else { }}
        已激活
        @{{#  } }}
    </script>
@endsection
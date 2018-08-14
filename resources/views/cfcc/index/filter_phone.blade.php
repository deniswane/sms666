@extends('cfcc.index.lay')
@section('content')
    <blockquote class="layui-elem-quote quoteBox">
        <form class="layui-form">
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal addLink_btn">增加手机号</a>
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
                    ,elem: '#test',
                    done :function(res){
                    },
                    width:'800'
                    ,align:'center'
                    ,id: 'testReload'
                    ,url: "{{route('cfcc.filter_phones')}}"
                    ,method:'post'
                    ,where:{'_token':"{{ csrf_token() }}"}
                    ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
                    , cols: [[
                        {field: 'code', align: 'center', width: 80,  title: '编号'}
                        , {field: 'phone', align: 'center', title: '手机号'}
                        ,{field:'sex', title:'状态', width:85, toolbar: '#switchTpl', unresize: true}
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
                        layer.confirm('确定删除该手机号吗？',{icon:3, title:'提示信息'},function(index){
                        $.get( "{{route('cfcc.filter_phone_add')}}",{
                                code : data.code
                            },function(res){
                            layer.msg(res.msg);
                            tableIns.reload();
                            layer.close(index);
                            })
                        });
                    }
                });


                //监听操作
                form.on('switch(sexDemo)', function(obj){
                    var status = obj.elem.checked==true?'1':'0';
                    var code=obj.value;
                    $.ajax({
                        type: 'put',
                        url: "{{route('cfcc.filter_phones')}}",
                        cache: false,
                        data: {code:code,status:status,_token:"{{csrf_token()}}"},
                        success: function (data) {
                            if (data.code =='200'){
                                layer.msg('操作成功',{time:800})
                            }else {
                                layer.alert('操作失败')
                            }
                        },
                        error:function (data) {
                        }
                    });
                    form.on("submit(addLink)",function(data){
                    })
                });

                //添加友链
                function addLink(edit){
                    var title;
                    if (edit){
                        title= '编辑手机号'
                    } else{
                        title ='添加手机号'
                    }
                    var index = layer.open({
                        title :title,
                        type : 2,
                        area : ["30%","40%"],
                        content : "{{route('cfcc.filter_phone_add')}}",
                        success : function(){
                            var body = $($(".layui-layer-iframe",parent.document).find("iframe")[0].contentWindow.document.body);
                            if(edit){
                                body.find(".code").val(edit.code);
                                body.find(".phone").val(edit.phone);
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
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>


@endsection
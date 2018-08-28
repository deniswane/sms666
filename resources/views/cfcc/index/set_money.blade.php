@extends('cfcc.index.lay')
@section('content')
    <blockquote class="layui-elem-quote quoteBox">
        <form class="layui-form">
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal addUserLink_btn">添加用户及类型</a>
            </div>
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal addLink_btn">添加分类</a>
            </div>
        </form>
    </blockquote>
    <blockquote>
        <div class="layui-inline" style="padding-left:20px ">
        注：针对全返回的：如果此处没有设置单价，用户将不能应用接口获取数据，返回105错误码。
            对于截取的：如果没有设置个人用户截取单价，会按1/次扣除。
        </div>
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
                ,url: "{{route('cfcc.set_money')}}"
                ,method:'post'
                ,where:{'_token':"{{ csrf_token() }}"}
                ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
                , cols: [[
                     {field: 'name', align: 'center', title: '用户',templet:'#statusTpl'}
                    , {field: 'type_name', align: 'center', title: '类型'}
                    , {field: 'price', align: 'center', title: '单价（点击数字修改单价）',event: 'setSign',style:"cursor:pointer "}
                    ,{fixed: 'right', title:'操作',width:178, align:'center', templet: '#barDemo'}
                ]]
            });
            //列表操作
            table.on('tool(test)', function(obj){
                var layEvent = obj.event,
                    data = obj.data;

                if(layEvent === 'edit'){ //编辑
                    addLink(data);
                } else if(layEvent === 'del'){ //删除
                    var id = data.id
                    console.log(id);
                    if (id=='0'){
                        layer.alert('默认的请不要删除');
                    }else{
                        layer.confirm('确定删除该用户吗？',{icon:3, title:'提示信息'},function(index){

                            $.post( "{{route('cfcc.set_money_user_delete')}}",{
                                id : id,
                                _token:"{{csrf_token()}}"
                            },function(res){
                                layer.msg(res.msg);
                                tableIns.reload();
                                layer.close(index);
                            })

                        });
                    }

                }else if(layEvent =='setSign'){
                    layer.prompt({
                        formType: 2
                        ,title: '设置单次请求金额'
                        ,value:data.price
                    }, function(value, index){
                        layer.close(index);
                        //这里一般是发送修改的Ajax请求
                        $.ajax({
                            type: 'post',
                            url: "{{route('cfcc.set_price')}}",
                            cache: false,
                            data: {price:value,id:data.id,_token:"{{csrf_token()}}"},
                            success: function (dan) {
                                //同步更新表格和缓存对应的值
                                if(dan.code != 200){
                                    layer.alert('请填写数字')
                                }else{
                                    obj.update({
                                        price: value
                                    });
                                }
                            },
                            error:function (data) {
                                layer.alert('错误')
                            }
                        });
                        obj.update({
                            sign: value
                        });
                    });
                }
            });


            //添加分类
            function addLink(edit){
                var title;
                if (edit){
                    title= '修改用户信息'
                } else{
                    title ='添加分类'
                }
                var index = layer.open({
                    title :title,
                    type : 2,
                    area : ["40%","50%"],
                    content : "{{route('cfcc.set_money_add')}}",
                    success : function(){
                        var body = $($(".layui-layer-iframe",parent.document).find("iframe")[0].contentWindow.document.body);
                    }
                })
            }
            //添加用户及分类
            function addUserLink(edit){
                var title;
                if (edit){
                    title= '修改用户信息'
                } else{
                    title ='添加分类'
                }
                var index = layer.open({
                    title :title,
                    type : 2,
                    area : ["40%","50%"],
                    content : "{{route('cfcc.set_money_user_add')}}",
                    success : function(){
                        var body = $($(".layui-layer-iframe",parent.document).find("iframe")[0].contentWindow.document.body);
                    }
                })
            }
            $(".addLink_btn").click(function(){
                addLink();
            })
            $(".addUserLink_btn").click(function(){
                addUserLink();
            })
        });

    </script>
    <script type="text/html" id="barDemo">
        @{{#  if(d.name == undefined){ }}

        @{{#  } else { }}
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" >删除</a>
        @{{#  } }}
    </script>

    <script type="text/html" id="statusTpl">
        @{{#  if(d.name == undefined){ }}
        默认
        @{{#  } else { }}
        @{{ d.name }}
        @{{#  } }}
    </script>
@endsection
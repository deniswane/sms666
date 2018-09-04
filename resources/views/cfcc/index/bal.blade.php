@extends('cfcc.index.lay')
@section('content')

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status"  value="@{{d.email}}" lay-skin="switch" lay-text="开|关" lay-filter="switchChange" @{{ d.switch =='1' ? 'checked' : '' }}>
    </script>
    <div style="text-align: center;">

        <div class="layui-inline" >
            <table id="test" lay-filter="test" ></table>
        </div>
    </div>
    <script>
        layui.use('table', function () {

            var table = layui.table;
            form=   layui.form;
            table.render({
                //请求后台获取数据
                request: {
                    pageName: 'curr' //页码的参数名称，默认：page
                    , limitName: 'nums' //每页数据量的参数名，默认：limit
                }
                ,height: 'full-104'
                ,elem: '#test',
                done :function(res){
                    // console.log(res)
                }
                // ,width:'1200'
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
                    , {field: 'daliy_amount', align: 'center',width:100, title: '今日请求次数', sort: true}
                    , {field: 'yes_num', align: 'center', width:100,title: '昨天的统计数量', sort: true}

                    ,{field:'switch', title:'全国', width:85, toolbar: '#switchTpl', unresize: true}
                    ,{field:'date_times', title:'日限', align: 'center', event: 'setDateTimes', sort: true,style:"cursor:pointer",templet: '#date_times'}
                    ,{field:'percentum', title:'新旧比', width:85,event: 'setRate',style:"cursor:pointer "}
                ]]
            });
            //监听单元格事件
            table.on('tool(test)', function (obj) {
                var data = obj.data;
                //余额
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

                                //同步更新表格和缓存对应的值
                                if(data.code != 200){
                                    layer.alert('请填写数字')
                                }else{
                                    layer.msg('成功');
                                  location.reload()
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
                //日限
                if (obj.event === 'setDateTimes') {
                       layer.prompt({
                        formType: 2
                        ,title: '每天限量'
                        ,value: data.date_times
                    }, function(value, index){
                        layer.close(index);
                        //这里一般是发送修改的Ajax请求
                        $.ajax({
                            type: 'post',
                            url: "{{route('cfcc.change_switch')}}",
                            cache: false,
                            data: {date_times:value,code:data.email,_token:"{{csrf_token()}}",met:'date_times'},
                            success: function (data) {
                                //同步更新表格和缓存对应的值
                                if(data.code != 200){
                                    layer.alert('更新失败')
                                }else{
                                    obj.update({
                                        date_times: value
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
                //比例
                if (obj.event === 'setRate') {
                    layer.prompt({
                        formType: 2
                        ,title: '取号新旧比（请以 冒号  ‘:’ 将数字隔开）'
                        ,value: data.percentum
                }, function(value, index){
                        console.log(value);
                        layer.close(index);
                        //这里一般是发送修改的Ajax请求
                        $.ajax({
                            type: 'post',
                            url: "{{route('cfcc.change_switch')}}",
                            cache: false,
                            data: {percentum:value,code:data.email,_token:"{{csrf_token()}}",met:'set_rate'},
                            success: function (data) {
                                //同步更新表格和缓存对应的值
                                if(data.code != 200){
                                    layer.alert(data.msg)
                                }else{
                                    obj.update({
                                        percentum: value
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

            //监听操作
            form.on('switch(switchChange)', function(obj){
                var status = obj.elem.checked==true?'1':'0';

                var code=obj.value;
                $.ajax({
                    type: 'post',
                    url: "{{route('cfcc.change_switch')}}",
                    cache: false,
                    data: {code:code,status:status,_token:"{{csrf_token()}}",met:'switch'},
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

    <script type="text/html" id="indexTpl">
    @{{d.LAY_TABLE_INDEX+1}}
    </script>

    <script type="text/html" id="date_times">

        @{{#  if(d.times >= d.date_times && d.date_times !=0){ }}
        <span style="color: red;">手机号达到上限</span>
        @{{#  } else{ }}
        @{{d.date_times  }}
        @{{#  } }}
    </script>
@endsection
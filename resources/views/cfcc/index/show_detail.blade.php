@extends('cfcc.index.lay')
{{--<meta http-equiv="refresh" content="60">--}}

@section('content')
    <blockquote class="layui-elem-quote quoteBox">
        <div class="demoTable" hidden>
            <div class="layui-inline">
                <input class="layui-input" name="keyword" placeholder="" id="demoReload" autocomplete="off">
            </div>
            <button class="layui-btn " data-type="reload" id="reload">z</button>
            &nbsp;&emsp;

        </div>
        <div class="demoTable">
            搜索用户：
            <div class="layui-inline">
                <input class="layui-input" name="username" placeholder="用户名" id="username" autocomplete="off">
            </div>
            <button class="layui-btn " onclick="searchContent()">搜索</button>
            搜索手机信息：
            <div class="layui-inline">
                <input class="layui-input" name="phone" placeholder="输入手机号" id="phone" autocomplete="off">
            </div>
            <button class="layui-btn " onclick="searchPhone()">搜索</button>
        </div>
    </blockquote>
    说明：省份 取走内容的手机号数量--取走的手机号 （比率大于100%手机号包含昨天的）；由于异步请求数据实时更新，总数以下边数据表总数为准。
    <div class="layui-form">

        <table class="layui-table">

            <tbody>
            <tr>
                <td width="160">
                    今日请求数据
                </td>
                <td id="today">

                    @if(empty($allIds['to_phone']))
                        无
                    @else
                        <span hidden> {{$count =0}}
                            {{$n=0}}</span>
                        @foreach($allIds['to_phone'] as $k => $id)

                            <span style="width:250px; float:left; display:block; ">
                           @if(isset($contents['to_phone'][$k]))

                                    {{$k}}：

                                    {{$contents['to_phone'][$k]}} --
                                    {{count($id)}}
                                    <span hidden> {{$count = $count+$contents['to_phone'][$k]}}
                                        {{$n = $n+count($id)}}</span>
                                    成功率 ： {{ round($contents['to_phone'][$k]/count($id),4)*100}} %
                                @endif
                          </span>

                            @if($loop->iteration%5==0)

                            </br>
                            @endif
                        @endforeach

                        总数据： {{$count}} -- {{$n}}
                                &nbsp;&nbsp; {{ round($count/ $n,4)*100}} %

                    @endif
                </td>
            </tr>
            <tr>

                <td width="160">昨日请求数据</td>
                <td id="yesday">

                    @if(empty($allIds['yes_phone']))
                        无
                    @else

                        <span hidden> {{$ye_count =0}}
                            {{$ye_n=0}}</span>
                        @foreach($allIds['yes_phone'] as $k => $id)
                            <span style="width:250px; float:left; display:block; ">
                            @if(isset($contents['yes_phone'][$k]))
                                    {{$k}}：
                                    {{$contents['yes_phone'][$k]}}&nbsp;--
                                    {{count($id)}}&nbsp;

                                    <span hidden> {{$ye_count = $ye_count+$contents['yes_phone'][$k]}}
                                        {{$ye_n = $ye_n+count($id)}}</span>
                                    成功率 ： {{ round($contents['yes_phone'][$k]/count($id),4)*100}} %
                           </span>
                            @endif
                            @if($loop->iteration%5==0)
                            </br>
                            @endif

                        @endforeach
                        总数据： {{$ye_count}} -- {{$ye_n}} &nbsp;&nbsp; {{ round($ye_count/ $ye_n,4)*100}} %
                    @endif

                </td>

            </tr>
            <tr id="data">
                <td>请求数据</td>
                <td style="padding: 0px ">
                    <table id="contents" lay-filter="contents" style="margin: 0px"></table>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
    <script>
        function searchContent() {

            var username = $('#username').val();
            $('#demoReload').val(username)
            $('#reload').click()

            $.ajax({
                type: 'post',
                url: "{{route('cfcc.searchUserContent')}}",
                cache: false,
                data: {username: username, _token: "{{csrf_token()}}"},
                success: function (data) {
                    if (data.code == '201') {
                        layer.alert(data.msg)
                    } else {
                        console.log(data);
                        $('#today').html(function () {
                            if (data.allIds.to_phone == false) {
                                return '无'
                            } else {
                                console.log(data)
                                var contents = ''
                                var i = 1;
                                var count = num = 0;
                                for (var Key in data.allIds.to_phone) {
                                    contents += '<span style="width:250px; float:left; display:block; ">'
                                    contents += Key + '：' + data.contents.to_phone[Key] + ' -- ' + data.allIds.to_phone[Key].length + "&nbsp;" + '成功率：' + Math.floor((data.contents.to_phone[Key] / data.allIds.to_phone[Key].length) * 100) + "%"
                                    contents += '</span>'
                                    count += data.contents.to_phone[Key];
                                    num += data.allIds.to_phone[Key].length;
                                    if (i % 5 == 0) {
                                        contents += "<br>"
                                    }
                                    ;
                                    i++;
                                }
                                contents += '</span>'
                                contents += '总数据：' + count + ' -- ' + num + ' 成功率：' + Math.floor(count / num * 100) + '%'
                                return contents
                            }

                        });
                        $('#yesday').html(function () {
                            if (data.allIds.yes_phone == false) {
                                return '无'
                            } else {
                                var contents = ''
                                var i = 1;
                                var count = num = 0;
                                for (var Key in data.allIds.yes_phone) {
                                    contents += '<span style="width:250px; float:left; display:block; ">'
                                    contents += Key + '：' + data.contents.yes_phone[Key] + ' -- ' + data.allIds.yes_phone[Key].length + "&nbsp;" + '成功率：' + Math.floor((data.contents.yes_phone[Key] / data.allIds.yes_phone[Key].length) * 100) + '%'
                                    contents += '</span>'
                                    count += data.contents.yes_phone[Key];
                                    num += data.allIds.yes_phone[Key].length;
                                    if (i % 5 == 0) {
                                        contents += "<br>"
                                    }
                                    ;
                                    i++;
                                }

                                contents += '总数据：' + count + ' -- ' + num + ' 成功率：' + Math.floor(count / num * 100) + '%'
                                return contents
                            }

                        });
                    }
                },
                error: function (data) {
                }
            });
        }
    </script>


    <script>
        layui.use('table', function () {

            var table = layui.table;
            table.render({
                //请求后台获取数据
                request: {
                    pageName: 'curr' //页码的参数名称，默认：page
                    , limitName: 'nums' //每页数据量的参数名，默认：limit
                }
//                ,height: 'full-104'
                , elem: '#contents'
                , limits: ['10', '15']
                , limit: 10
                , id: 'testReload'
                , page: true
                , url: "{{route('cfcc.showContents')}}"
                , method: 'post'
                , where: {'_token': "{{ csrf_token() }}"}
                , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
                , cols: [[
                    {field: 'phone', align: 'center', title: '手机号'}
                    , {field: 'province', align: 'center', title: '省份'}
                    , {field: 'content', align: 'center', title: '内容'}
                    , {field: 'updated_at', align: 'center', title: '更新时间'} //minWidth：局部定义当前单元格的最小宽度，layui 2.2.1 新增
                ]]
            });
            //监听单元格事件
            table.on('tool(contents)', function (obj) {
                var data = obj.data;
                if (obj.event === 'setSign') {
                    layer.prompt({
                        formType: 2
                        , title: '设置余额'
                        , value: data.balance
                    }, function (value, index) {
                        layer.close(index);
                        //这里一般是发送修改的Ajax请求
                        $.ajax({
                            type: 'post',
                            url: "{{route('cfcc.set_bal')}}",
                            cache: false,
                            data: {balance: value, email: data.email, _token: "{{csrf_token()}}"},
                            success: function (data) {

                                //同步更新表格和缓存对应的值
                                if (data.code != 200) {
                                    layer.alert('请填写数字')
                                } else {
                                    obj.update({
                                        balance: value
                                    });
                                }
                            },
                            error: function (data) {
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
        // 获取手机号短信内容
        function searchPhone() {
            var phone = $('#phone').val();
            if (IsMobilePhoneNumber(phone)) {
                console.log(phone);
                $.ajax({
                    type: 'post',
                    url: "{{route('cfcc.searchContent')}}",
                    cache: false,
                    data: {phone: phone, _token: "{{csrf_token()}}"},
                    success: function (data) {
                        layer.alert(data, {'title': '最新信息'})
                    },
                    error: function (data) {
                    }
                });
            } else {
                layer.alert('格式不对')
            }

        }
        //验证手机号
        function IsMobilePhoneNumber(input) {
            var regex = /^((\+)?86|((\+)?86)?)0?1[3458]\d{9}$/;
            if (input.match(regex)) {
                return true;
            } else {
                return false;
            }
        }
    </script>
@endsection

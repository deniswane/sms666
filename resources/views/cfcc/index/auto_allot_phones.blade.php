@extends('cfcc.index.lay')
@section('content')

    <blockquote class="layui-elem-quote quoteBox">



        <form class="layui-form" action="">
        {{--<div class="layui-form-item">--}}
            <label class="layui-form-label">京东</label>
            <div class="layui-input-block">
                <input type="checkbox" checked="" name="open" lay-skin="switch" lay-filter="switchTest" lay-text="开|关">

                <span style="margin-left: 30px">注意：分配的百分比总和应该小于100！</span>
            </div>
        {{--</div>--}}
        </form>
    </blockquote>

    <form class="layui-form" >
        <table class="layui-table" style="margin: 10px;">
            <tbody>
            <tr>

            @foreach($configs as $k=> $config)
                    <td width="80px">{{$config->name}}:
                        <input type="tel" style="width: 30px;border:none;" name="{{$config->id}}" lay-verify="required|number|max:100" value="{{$config->percent}}" autocomplete="off" >%
                    </td>
                    @if($k/8==1)
            </tr><tr>
                    <td width="80px">{{$config->name}}
                        <input type="tel" style="width: 30px;border:none;" name="{{$config->id}}" lay-verify="required|number|max:100" value="{{$config->percent}}" autocomplete="off" >%
                    </td>

                    @endif()
                    @endforeach()

            </tr>

            </tbody>
        </table>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
            </div>
        </div>
    </form>

    <script>
        layui.use(['form', 'layedit', 'laydate'], function(){
            var form = layui.form

            //监听指定开关
            form.on('switch(switchTest)', function(data){
              var  open=  this.checked?'1':'0';


                $.ajax({
                    url: '{{route("cfcc.auto_allot_phones")}}',

                    data:{open:open,_token:"{{csrf_token()}}"},
                    async: true,
                    cache: false,
                    type: "put",
                    dataType: "json",
                    success: function(result){
                       var ti= open =='1'?'打开':'关闭'
                        layer.msg(ti+result.msg)
                    }
                });
            });

            //监听提交
            form.on('submit(demo1)', function(data){
                var dat=JSON.stringify(data.field)
                $.ajax({
                    url: '{{route("cfcc.auto_allot_phones")}}',

                    data:{dat:dat,_token:"{{csrf_token()}}"},
                    async: true,
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    success: function(result){
                        layer.alert(result.msg)
                        location.reload()
                    }
                });
                return false;

            });

        });

    </script>
@endsection
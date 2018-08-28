@extends('cfcc.index.lay')
@section('content')
    <form class="layui-form linksAdd" >
        <input name="_method" id="_method" type="hidden" value="post">

        <div style="padding: 45px">
            已有类别：

                @foreach($types as $type)
                    {{$type}}
                @endforeach

        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">新的分类</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input type-name" lay-verify="required" placeholder="输入新类别" />
            </div>
        </div>
        <div class="layui-form-item" style="text-align:center; ">
            <button class="layui-btn layui-block" lay-filter="addLink" lay-submit>提交</button>
        </div>
    </form>
    <script>
        layui.use(['form','layer','table'],function(){
            var form = layui.form,
                layer = parent.layer === undefined ? layui.layer : top.layer,
                $ = layui.jquery


            form.on("submit(addLink)",function(data){
                //弹出loading
                var index = top.layer.msg('数据提交中，请稍候',{icon: 16,time:false,shade:0.8});
                // 实际使用时的提交信息
                $.post("{{route('cfcc.set_money_add')}}",{
                    type_name : $(".type-name").val(),
                    _method:$('#_method').val(),
                    _token:"{{csrf_token()}}"
                },function(res){
                    console.log(res);
                    if (res.code != 200){
                        top.layer.msg(res.msg);
                    } else{
                        setTimeout(function(){
                            top.layer.close(index);
                            top.layer.msg(res.msg);
                            layer.closeAll("iframe");
                            //刷新父页面
                            $(".layui-tab-item.layui-show",parent.document).find("iframe")[0].contentWindow.location.reload();
                        },500);
                    }
                })
                return false;
            })

        })
    </script>
@endsection()
@extends('cfcc.index.lay')
@section('content')
<form class="layui-form linksAdd" style="text-align:center;">
    <input name="_method" id="_method" type="hidden" value="post">
    <div class="layui-form-item">
        <label class="layui-form-label">编号</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input code" lay-verify="required" placeholder="输入编号" />
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">手机号</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input phone" lay-verify="required|phone" placeholder="输入手机号" />
        </div>
    </div>
    <div class="layui-form-item" >
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
            $.post("{{route('cfcc.filter_phone_add')}}",{
                code : $(".code").val(),  //编号
                phone : $(".phone").val(),  //手机号
                _method:$('#_method').val(),
                _token:"{{csrf_token()}}"
            },function(res){
                console.log(res);
                setTimeout(function(){
                        top.layer.close(index);
                        top.layer.msg(res.msg);
                        layer.closeAll("iframe");
                        //刷新父页面
                        $(".layui-tab-item.layui-show",parent.document).find("iframe")[0].contentWindow.location.reload();
                    },500);
            })
            return false;
        })

    })
</script>
@endsection()
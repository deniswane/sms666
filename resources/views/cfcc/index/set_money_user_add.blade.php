@extends('cfcc.index.lay')
@section('content')
    <form class="layui-form linksAdd" >
        <input name="_method" id="_method" type="hidden" value="post">

        <div class="layui-form-item">
            <label class="layui-form-label">分类</label>
            <div class="layui-input-inline">
                <select name="type" class="type">
                    @foreach($types as $type)
                        <option value="{{$type->id}}">{{$type->type_name}}</option>

                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">用户</label>
            <div class="layui-input-inline">
                <select name="user" class="user">
                    @foreach($users as $user)
                        <option value="{{$user->id}}">{{$user->name}}--{{$user->email}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">输入单价</label>
            <div class="layui-input-inline">
                <input type="number" class="layui-input price"   lay-verify="required" placeholder="输入单价" />
            </div>
        </div>
        <div class="layui-input-inline">

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
                $.post("{{route('cfcc.set_money_user_add')}}",{
                    type : $(".type").val(),
                    user : $(".user").val(),
                    price : $(".price").val(),
                    _method:$('#_method').val(),
                    _token:"{{csrf_token()}}"
                },function(res){
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
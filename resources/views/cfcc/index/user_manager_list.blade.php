@extends('cfcc.index.lay')
@section('content')
    <form class="layui-form linksAdd" style="text-align:center;">
        <input name="_method" id="_method" type="hidden" value="post">
        <div class="layui-form-item">
            <label class="layui-form-label">用户名</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input name" lay-verify="required" placeholder="输入用户名" />
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">邮箱</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input email" lay-verify="required|email" placeholder="输入邮箱" />
            </div>
        </div>

        <div class="layui-form-item" id="fenlei">
            <label class="layui-form-label">分类</label>
            <div class="layui-input-inline">
                <select name="type" id="type" class="type">
                    @foreach($types as $type)

                        <option value="{{$type->id}} ">{{$type->type_name}}</option>

                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item" id="danjia">
            <label class="layui-form-label">单价</label>
            <div class="layui-input-inline">
                <input type="number" class="layui-input price" id="price" value="1" lay-verify="required|password" placeholder="输入单价" />
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">密码</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input password" id="password" lay-verify="required|password" placeholder="输入密码" />
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">确认密码</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input re_password" id="re_password" lay-verify="required|checkRepeat" placeholder="确认密码" />
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
                $.post("{{route('cfcc.user_manager_list')}}",{
                    name : $(".name").val(),
                    email : $(".email").val(),
                    price : $(".price").val(),
                    type : $(".type").val(),
                    password : $(".password").val(),
                    re_password : $(".re_password").val(),
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
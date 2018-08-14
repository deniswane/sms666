@extends('cfcc.index.lay')
@section('content')

        <div style="margin-left: 50px;" id="ajaxForm">
            <div class="layui-form-item">

                <div class="layui-input-inline">
                    <input  name="user_id" id="user_id" value="{{$user->id}}" type="hidden">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="L_username" class="layui-form-label">昵称</label>
                <div class="layui-input-inline">
                    <input type="text" name="nickname" id="name" value="{{$user->name}}" required="" lay-verify="required" autocomplete="off"  class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="L_pass" class="layui-form-label">新密码</label>
                <div class="layui-input-inline">
                    <input type="password" name="password" id="password" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="L_repass" class="layui-form-label">确认密码</label>
                <div class="layui-input-inline">
                    <input type="password" name="password_confirmation"  id="password_confirmation" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">创建时间</label>
                <div class="layui-input-inline">
                    <input type="text" value="{{$user->created_at}}" disabled="disabled" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">更新时间</label>
                <div class="layui-input-inline">
                    <input type="text" value="{{$user->updated_at}}" class="layui-input" disabled="disabled">
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-sm layui-btn-normal" id="ajaxSubmit" lay-submit lay-filter="layform">确认修改</button>
            </div>
        </div>

        <script type="text/javascript" src="/js/jquery.min.js"></script>
        <script>

            $("#ajaxSubmit").click(function() {
                $.ajax({
                    url: "{{route('me')}}",

                    data: {
                        user_id:$('#user_id').val(),
                        password:$('#password').val(),
                        password_confirmation:$('#password_confirmation').val(),
                        _token:"{{csrf_token()}}",
                        nickname: $("#name").val(),
                    },
                    async: true,
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    success: function(result){
                        if(result.code=='0'){
                            window.location.href = result.url;

                        }else if(result.code=='1'){
                            alert(result.msg)
                       }
                    }
                });
            });
        </script>

@endsection
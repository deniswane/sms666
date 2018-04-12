@extends('admin.index.lay')
@section('content')
    <div class="panel panel-default panel-intro">
        <div class="panel-body">
            <div class="layui-tab layui-tab-brief" lay-filter="user">
                <ul class="layui-tab-title" id="LAY_mine">
                    <li class="layui-this" lay-id="info">设置单次请求价格</li>
                </ul>
                <div class="layui-tab-content" style="padding: 20px 0;">
                    <div class="layui-form layui-form-pane layui-tab-item layui-show">
                        <form method="post" action="">
                            {{ csrf_field() }}


                            <div class="layui-form-item">
                                <label for="prices" class="layui-form-label">请设置</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="prices"  value="{{$price['price']}}" autocomplete="off" lay-verify="number" class="layui-input">
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">更新时间</label>
                                <div class="layui-input-inline">
                                    <input type="text" value="{{$price['updated_at']}}" class="layui-input" disabled="disabled">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit lay-filter="layform">确认修改</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('cfcc.index.lay')
@section('content')
    <div class="panel panel-default panel-intro">
        <div class="panel-body">
            <div class="layui-tab layui-tab-brief" >
                <div class="layui-tab-content" style="padding: 20px 0;">
                    <div class="layui-form layui-form-pane layui-tab-item layui-show">
                        <form method="post" action="">
                            {{ csrf_field() }}

                            <div class="layui-form-item" style="margin-left: 40px;">
                                <label for="prices" class="layui-form-label">请设置</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="prices"  value="{{$price->price}}" autocomplete="off" lay-verify="number" class="layui-input">
                                </div>
                                <div class="layui-form-mid">$/ 次 (可以有两位小数)</div>
                            </div>

                            <div class="layui-form-item" style="margin-left: 40px;">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit lay-filter="layform">&nbsp;&nbsp;确认修改</button>
                            </div>
                        </form>
                    </div>

                    {{--<div class="layui-form layui-form-pane layui-tab-item ">--}}
                        {{--<form method="post" action="">--}}
                            {{--{{ csrf_field() }}--}}

                            {{--<div class="layui-form-item">--}}
                                {{--<div class="layui-inline">--}}
                                    {{--<label class="layui-form-label">价格1</label>--}}
                                    {{--<div class="layui-input-inline" style="width: 100px;">--}}
                                        {{--<input type="text" name="price_min" value="{{$price->price_i}}" autocomplete="off" lay-verify="number" class="layui-input">--}}
                                    {{--</div>--}}
                                    {{--<div class="layui-form-mid">￥</div>--}}
                                    {{--<div class="layui-input-inline" style="width: 100px;">--}}
                                        {{--<input type="text" name="num_min" value="{{$price->num_i}}" lay-verify="number" autocomplete="off" class="layui-input">--}}
                                    {{--</div>--}}
                                    {{--<div class="layui-form-mid">次</div>--}}

                                {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="layui-form-item">--}}
                                {{--<div class="layui-inline">--}}
                                    {{--<label class="layui-form-label">价格2</label>--}}
                                    {{--<div class="layui-input-inline" style="width: 100px;">--}}
                                        {{--<input type="text" name="price_max" value="{{$price->price_a}}" lay-verify="number" autocomplete="off" class="layui-input">--}}
                                    {{--</div>--}}
                                    {{--<div class="layui-form-mid">￥</div>--}}
                                    {{--<div class="layui-input-inline" style="width: 100px;">--}}
                                        {{--<input type="text" name="num_max" value="{{$price->num_a}}" lay-verify="number"  autocomplete="off" class="layui-input">--}}
                                    {{--</div>--}}
                                    {{--<div class="layui-form-mid">次</div>--}}

                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<div class="layui-form-item">--}}
                                {{--<button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit lay-filter="layform">确认修改</button>--}}
                            {{--</div>--}}
                        {{--</form>--}}
                    {{--</div>--}}

                </div>
            </div>
        </div>
    </div>

@endsection
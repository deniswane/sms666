@extends('cfcc.index.lay')
@section('content')
<div class="layui-form">

    <table class="layui-table" style="text-align: center;width:80%; ">

        <tbody >
        <tr>
            <th style="text-align: center">省份</th>
            <th style="text-align: center">筛选的手机号</th>
            <th style="text-align: center">使用的手机号</th>
            <th style="text-align: center">剩余手机号</th>
        </tr>
        @foreach( $c as $key =>$value)
            <tr>
                <td>
                    {{$key}}
                </td>
                <td>
                @if(isset($value['total']))
                    {{$value['total']}}
                @endif
                </td>
                <td>
                    @if(isset($value['nun']))
                        {{$value['nun']}}
                    @endif


                </td>
                <td>
                    @if(isset($value['total']))
                        @if($value['total']-$value['nun'] <=100)
                          <span style="color: #ff5800"> {{$value['total']-$value['nun']}}</span>

                        @else
                        {{$value['total']-$value['nun']}}
                        @endif
                    @else

                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
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
                        @if(isset($value['nun']))

                            @if($value['total']-$value['nun'] <=100)
                                <span style="color: #ff5800"> {{$value['total']-$value['nun']}}</span>

                            @else
                        {{$value['total']-$value['nun']}}
                        @endif
                        @endif

                    @else

                    @endif
                </td>
            </tr>
        @endforeach
        <tr>
            <td>合计</td>
            <td id="total">

            </td>
            <td id="nun">

            </td>
            <td id="remainder"></td>
        </tr>
        </tbody>
    </table>
</div>
<script src="/js/jquery.min.js"></script>
<script>
    $(function () {
        count_total=count_nun=remainder=0;
        var total_tds = $('.layui-table  tr:not(:first)').find("td:eq(1)")
        var i=0 ,n=total_tds.length;
        for(i=0; i<n;i++){
            count_total += total_tds[i].innerText*1;
        }

        var nun_tds = $('.layui-table  tr:not(:first)').find("td:eq(2)")
        var i=0 ,n=nun_tds.length;
        for(i=0; i<n;i++){
            count_nun += nun_tds[i].innerText*1;
        }

        var remainder_tds = $('.layui-table  tr:not(:first)').find("td:eq(3)")
        var i=0 ,n=remainder_tds.length;
        for(i=0; i<n;i++){
            remainder += remainder_tds[i].innerText*1;
        }

        $('#total').html(count_total);
        $('#nun').html(count_nun);
        $('#remainder').html(remainder);
    })
</script>
@endsection
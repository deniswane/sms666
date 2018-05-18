@extends('layouts.default')
@section('content')
    <div class="panel">
        <h5><strong>{{ trans('home.subtitle_1') }}</strong><br>
            {{ trans('home.received_email') }}<br>
            <strong>{{ trans('home.subtitle_2') }}</strong><br>
            {{ trans('home.subtitle_2_con') }}<br>
        </h5>
        <div align="center">
            <div class='login'></div>
            <div class="Table">

                <div class="Row">
                    @include('shared._cell')
                </div>
            </div>
        </div>
        <br>
        @guest
        @else
        Please enter the amount in the input box and click on the purchase
        <form style="margin: 20px 5px;" method="post" >
            <input type="text" placeholder=""  id="pri" autofocus  class="ec_input">USD
            <input style="display:none" >
            <img id="submitAdd" style="cursor:pointer" src="/img/btn_buynow.gif" alt="PayPal - The safer, easier way to pay online!">
        </form>

            <script>

            $("#submitAdd").click(function() {
                $.ajax({
                    type: 'post',
                    url: "{{route('ec-checkout')}}",
                    cache: false,
                    data: {'_token': "{{csrf_token()}}", 'prices': $('#pri').val()},
                    dataType: 'json',
                    beforeSend: function () {
                        layer.msg('Loading...', {
                            icon: 16,
                            shade: [0.5, '#f5f5f5'],
                            scrollbar: false,
                            offset: '50%',
                            time: 300000
                        });
                    },
                    success: function (data) {
                        if (data.code == '1') {
                            layer.alert('Incomplete filling format', {btn: 'OK'});
                        } else if (data.code == 200) {
                            $(location).attr('href', data.msg);
                        }
                    },
                    error: function (data) {
                        console.log(data)
                    }
                });
            })
            </script>

            @endguest
<div align="left">
<h5><strong>• {{ trans('home.subtitle_3') }} ?</strong>
<br>
<strong>{{config('app.name')}}</strong> {{ trans('home.subtitle_3_con') }}<br>
<br>
<strong> • </strong>{{ trans('home.subtitle_4') }}<br>
<br>
<strong>{{ trans('home.subtitle_5') }}</strong><br>
{{ trans('home.subtitle_4_con') }}<br>
<br>
<strong>{{ trans('home.subtitle_6') }}</strong><br>
{{ trans('home.subtitle_6_con') }}<br>
<br>
<strong>{{ trans('home.subtitle_7') }}</strong><br>
{{ trans('home.subtitle_7_con') }}<br>
<br>
<strong>{{ trans('home.subtitle_8') }}</strong><br>
{{ trans('home.subtitle_8_con') }}<br>
<br>
<strong> {{ trans('home.subtitle_9') }}</strong><br>
{{ trans('home.subtitle_9_con') }}<br>
<br>
<strong> {{ trans('home.subtitle_10') }}</strong><br>
{{ trans('home.subtitle_10_con') }}<br>
<br>
</h5>
</div>
</div>
@endsection
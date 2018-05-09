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


        {{--<a href="{{url('paypal/ec-checkout')}}">--}}

            {{--<img src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0"  alt="PayPal - The safer, easier way to pay online!">--}}
            {{--<img alt="" border="0" src="https://www.paypalobjects.com/zh_XC/i/scr/pixel.gif" width="1" height="1">--}}
        {{--</a>--}}
        <div align="left">
            <h5><strong>• {{ trans('home.subtitle_3') }} ?</strong>
                <br>
                <strong>Receive-sms-online.info</strong> {{ trans('home.subtitle_3_con') }}<br>
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
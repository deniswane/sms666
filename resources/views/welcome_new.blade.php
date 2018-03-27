@extends('layouts.default')
@section('content')
    <div class="panel">
        <h5><strong>Receive SMS Online</strong><br>
            Receive-SMS-online.info is FREE service for receive SMS messages online, based on REAL SIM and shows you the
            exact information received by the modem with Dynamic Sender ID. On this website you can check if you receive
            SMS
            on different routes,or if you receive using the Dynamic Sender ID feature.<br>
            <strong>How to use?</strong><br>
            Select one of the numbers listed below and you can see the SMS that reach that number within seconds.All
            messages are shown, nothing is blocked.<br>
        </h5>
        <div align="center">
            <div class='login'></div>
            <div class="Table">

                <div class="Row">
                    @include('shared._cell')
                </div>
            </div>
        </div>

        <div align="left">
            <h5><strong>• This site have any limitations ?</strong>
                <br>
                <strong>Receive-sms-online.info</strong> has no limitation when SMS is received. If you don&apos;t see
                the
                message on this website, probably due to limitations imposed by mobile operators.<br>
                <br>
                <strong> • </strong>If you use the numbers above for <strong>Phone Verification</strong> like Google,
                Facebook, Ebay, Paypal ... you will do on your own responsibility, because this is a
                <strong>PUBLIC</strong>
                site and we are not responsible for what you do.<br>
                <br>
                <strong>• What countries are currently available ?</strong><br>
                Currently We support few countries numbers like: USA, Spain, Romania,Uk,Germany, France, Russia, Italy
                and
                we will try to add more.<br>
                <br>
                <strong> • How long is available a number ?</strong><br>
                Numbers are available from several days to a month.<br>
                <br>
                <strong>• How often change the numbers ?</strong><br>
                We will try to change minimum one number per day.<br>
                <br>
                <strong> • How much does it cost?</strong><br>
                It&apos;s free, there is no charge to receive sms via this website. This free text message service is
                here
                to protect your privacy by keeping your phone number private.<br>
                We do hope you will refer us to your friends so this site will keep growing.<br>
                <br>
                <strong> • Does this site support mobile version on phone ?</strong><br>
                Yes, on <a href="#">Google Play</a> you will find
                our application for mobile phone that support Android OS.<br>
                <br>
                <strong> • Phone number is no longer on site?</strong><br>
                If you can not find the phone number on the site, is because there have been too many records with that
                number, and was replaced by another.<br>
                <br>
            </h5>
        </div>
    </div>
@endsection
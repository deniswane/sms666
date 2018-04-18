@extends('layouts.default')
@section('title', 'Detail')
@section('content')
    <div class="panel">
        <h4 style="text-align:center;"> List of SMS messages received for phone number : <strong>919654766051 :
                India </strong></h4>

        <h5>
<span class="alert-box [secondary radius round]"> - Refresh the page to see the new SMS messages.<br>
- Message will appear on this page within seconds after we receive.<br>
- Received messages are displayed as such, does not change anything.<br>
- We do not send response to incoming SMS.<br>
- This is the list of the last 200 messages received on this number.<br>
<span class="text-right">SMS received today: 1475<br>
Time of use: 48 days<br>
Availability: <b class="statut_online">online</b></span><br><br>
<div align="center"><button class="autorefresh"
                            onClick="window.location.reload();">Refresh Webpage</button></div></span></h5>
        @include('shared._sms_content_cell')
        </label>
        <script type="text/javascript">


            $(window).load(function () {
                null == document.getElementsByTagName("iframe").item(ga.length - 1) && $("div.login:last").html('<p class="alert-box [secondary warning radius round]">We&apos;ve detected that you&apos;re using <strong>AdBlock Plus</strong> or some other adblocking software. Please be aware that this is only contributing to the demise of the site. We need money to operate the site, and almost all of that comes from our online advertising. Please disable <strong>AdBlock Plus</strong> and refresh webpage!</p>') && $('#msgs').html('')
            });


        </script>
    </div>
@endsection
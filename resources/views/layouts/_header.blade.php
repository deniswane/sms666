<ul class="topnav">

    @guest
        <li><a href="{{ route('login') }}">{{ trans('home.login') }}</a></li>
        @elseif(Auth::user()->isVerified())

            <li>
                <a id="hover" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                   document.getElementById('logout-form').submit();">Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>

            </li>

            <li><a id="" href="#">{{ Auth::user()->name }}</a></li>
        @else

            {{ Auth::logout() }}
            <li><a href="{{ route('login') }}">{{ trans('home.login') }}</a></li>

            <script>
                layui.use('layer', function () {
                    var layer = layui.layer;
                    layer.alert('Please login to your mailbox to complete the activation and registration !', {
                        title: 'msg',
                        btn: 'ok',
                        icon: 6,
                        skin: 'layer-ext-moon'
                    })
                });

            </script>
            @endguest
            {{--<li><select id="formLanguage" onchange="location = this.value;">--}}
                    {{--<option value="#">select language</option>--}}
            {{--@foreach (Config::get('app.locales') as $lang => $language)--}}
                {{--@if ($lang != App::getLocale())--}}
                    {{--<option value="http://sms-receive-online.info/language/{{$lang}}">{{$language}}</option>--}}
                {{--@endif--}}
            {{--@endforeach--}}
                    {{--<option value="http://www.sms-receive-online.info/language/zh-CN">Chinese</option>--}}
                    {{--<option value="http://www.sms-receive-online.info/language/en">English</option>--}}
                {{--</select></li>--}}
            <li><a href="javascript:void(0)" onclick="myTab()">{{ trans('home.private_numbers') }}</a></li>


            <li><a href="{{route('home')}}">{{ trans('home.home') }}</a></li>
            <li class="icon"><a href="javascript:void(0);" onclick="myFunction()">&#9776;</a></li>
            <li><a href="#"><img id="android_img"
                                 src="/img/android-app_google-play_button.png"
                                 alt="Android App"
                                 style="height: 35px; margin-top: -12px;"></a>
            </li>

</ul>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script>

    //JavaScript代码区域
    layui.use('layer', function () {
        var layer = layui.layer;

    });

    function myTab() {

        $.ajax({
            type: 'get',
            url: "{{route('getprice')}}",
            data: '',
            cache: false,
            success: function (data) {

                layer.tab({
                    area: ['600px', '400px'],
                    tab: [{
                        title: 'API',
                        content: '<p "><strong style="font-size: 18px;">Address</strong><br/>' +
                        '1.Set keywords :</br>http://sms-receive-online.info/manager/api/keyword?k=k1:k2&token=Your token<br/>'+
                        '2.Get phone number :</br>http://sms-receive-online.info/manager/api/getPhoneNumber?token=Your token<br/>'+
                        '3.Get content :</br>http://sms-receive-online.info/manager/api/getSmsContent?token=Your token&phone=The phone number obtained from the first step <br/><strong style="font-size: 18px;">Response</strong><br/>' +
                        '{"code":200,"msg":"success"}<br/>{"code":201,"msg":"Please update your text message first"}<br/>{"code":102,"msg":"Format error"}<br/>' +
                        '{"code":401,"msg":"No new text messages"}<br/>' +
                        '{"code":103,"msg":"The frequency is too fast"}<br/>' +
                        '{"code":105,"msg":"Sorry, sir. You have no right to visit"}<br/>' +
                        '{"code":106,"msg":"You need to charge money"}<br/>' +
                        '{"code":107,"msg":"No mobile phone number for the time being"}<br/><table></table></p>'
                    }, {
                        title: 'Price',
                        content:
                        "<p ><strong style='font-size: 18px;'>Price</strong><br/> "+data.price+
                        "  USD/time<p><strong <strong style='font-size: 18px;'>Your token</strong><br/>"+
                        "@guest Please register and log in @else{{ Auth::user()->token}} @endguest<br/>"+
                        "<strong <strong style='font-size: 18px;'>Your balance</strong><br/>"+
                        "@guest  @else{{ Auth::user()->balance}}USD @endguest"

                    {{--},{--}}
                        {{--title: 'Recharge',--}}
                        {{--content:--}}
                        {{--'@guest Please register and log in @else--}}
                         {{--<form id="addForm" ><input id="pri"  onkeyup='+'value=value.replace(/[^1234567890.]+/g,"") >'+' USD (Input number)<br>\--}}
                        {{--<input style="display:none" ></input><img id="submitAdd" style="cursor:pointer" src="/img/btn_buynow.gif" alt="PayPal - The safer, easier way to pay online!"></form> @endguest'--}}
                    }
                    ]
                });
                 {{--$("#submitAdd").click(function(){--}}
                    {{--$.ajax({--}}
                        {{--type:'post',--}}
                        {{--url:"{{route('ec-checkout')}}",--}}
                        {{--cache: false,--}}
                        {{--data:{'_token':"{{csrf_token()}}",'prices':$('#pri').val()},--}}
                        {{--dataType:'json',--}}
                        {{--beforeSend: function () {--}}
                            {{--layer.msg('Loading...', { icon: 16, shade: [0.5, '#f5f5f5'], scrollbar: false, offset: '50%', time: 300000 });--}}
                        {{--},--}}
                        {{--success:function(data){--}}
                            {{--if(data.code =='1'){--}}
                                {{--layer.alert('Incomplete filling format',{btn:'OK'});--}}
                            {{--}else if(data.code ==200){--}}
                                {{--$(location).attr('href', data.msg);--}}
                            {{--}--}}
                        {{--},--}}
                        {{--error:function(){--}}
                        {{--}--}}
//                    })

//                })
            },
            error: function () {
            }
        });
    }

</script>


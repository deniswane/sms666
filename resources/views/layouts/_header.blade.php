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
            <li><select id="formLanguage" onchange="location = this.value;">
            <option value="http://www.sms-receive-online.info/language/zh-CN">Chinese</option>
            <option value="http://sms-receive-online.info/language/en">English</option>
            </select></li>
            <li><a href="javascript:void(0)" onclick="myTab()">{{ trans('home.private_numbers') }}</a></li>
            {{--<li><a href="{{route('inactive_numbers')}}">Inactive numbers</a></li>--}}
            {{--<li><a href="{{route('contact')}}">Contact</a></li>--}}


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
        layer.tab({
            area: ['500px', '400px'],
            tab: [{
                title: 'API',
                content: '<p><strong>address</strong><br/><codeg>http://sms-receive-online.info/manager/api/getPhoneNumber?token=Your token</codeg><br/><codeg>http://sms-receive-online.info/manager/api/getSmsContent?token=Your token&phone=The number you want</codeg><br/><strong>response</strong><br/><codeg>' +
                '{"code":200,"msg":"success"}</codeg><br/><codeg>{"code":101,"msg":"Not sufficient funds"}<codeg/><br/>' +
                '<codeg>{"code":401,"msg":"No new text messages"}<codeg/><br/>' +
                '<codeg>{"code":103,"msg":"The frequency is too fast"}<codeg/><br/>' +
                '<codeg>{"code":105,"msg":"Sorry, sir. You have no right to visit"}<codeg/><br/><table></table></p>'
            }, {
                title: 'Price',
                content: "<p><strong>price</strong><br/><codeg>{{$prices->price_i}}元 " +
                "{{$prices->num_i}}次</codeg></br><codeg>{{$prices->price_a}}元 {{$prices->num_a}}次</codeg>" +
                "<p><strong>Your token</strong><br/>" +
                "@guest<codeg> Please register and log in</codeg> @else<codeg>{{ Auth::user()->token}}</codeg> @endguest"
            }]
        });
    }

</script>
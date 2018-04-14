<ul class="topnav">

    @guest
        <li><a href="{{ route('login') }}">Login</a></li>
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

            <li ><a id="" href="#">{{ Auth::user()->name }}</a></li>
        @else

            {{ Auth::logout() }}
        <li><a href="{{ route('login') }}">Login</a></li>

        <script>
            layui.use('layer', function(){
                var layer = layui.layer;
                layer.alert('Please login to your mailbox to complete the activation and registration !', {
                    title:'msg',
                    btn:'ok',
                    icon: 6,
                    skin: 'layer-ext-moon'
                })
            });

        </script>
            @endguest
            {{--<li><select id="formLanguage" onchange="location = this.value;">--}}
            {{--<option value="">Select language ..</option>--}}
            {{--<option value="https://www.receive-sms-online.info/">English</option>--}}
            {{--<option value="https://es.receive-sms-online.info/">Español</option>--}}
            {{--<option value="https://de.receive-sms-online.info/">Deutsche</option>--}}
            {{--<option value="https://ar.receive-sms-online.info/">العربية</option>--}}
            {{--<option value="https://fr.receive-sms-online.info/">Français</option>--}}
            {{--<option value="https://it.receive-sms-online.info/">Italiano</option>--}}
            {{--<option value="https://ru.receive-sms-online.info/">Pусский</option>--}}
            {{--<option value="https://ro.receive-sms-online.info/">Română</option>--}}
            {{--</select></li>--}}
            <li><a href="javascript:void(0)" onclick="myTab()">Private numbers</a></li>
            <li><a href="{{route('inactive_numbers')}}">Inactive numbers</a></li>
            <li><a href="{{route('contact')}}">Contact</a></li>
            <li><a href="{{route('home')}}">Home</a></li>
            <li class="icon"><a href="javascript:void(0);" onclick="myFunction()">&#9776;</a></li>
            <li><a href="#"><img id="android_img"
                                 src="img/android-app_google-play_button.png"
                                 alt="Android App"
                                 style="height: 35px; margin-top: -12px;"></a>
            </li>

</ul>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script>
    //JavaScript代码区域
    layui.use('layer', function(){
        var layer = layui.layer;

    });
    function myTab() {
        layer.tab({
            area: ['600px', '300px'],
            tab: [{
                title: 'API',
                content: '<p><strong>正式版接口地址</strong><br/><codeg>http://wz.tkc8.com/manage/api/check?token=您的令牌&url=域名</codeg><br/><strong>接口返回json</strong><br/><codeg>' +
                '{"code":9900,"msg":"获取成功"}</codeg><br/><br/><codeg>{"code":139,"msg":"用户没有权限"}<codeg/><table></table></p>'
            }, {
                title: 'price',
                content: '价格价格价格价格价格价格价格价格价格价格价格价格价格'
            }]
        });
    }

</script>
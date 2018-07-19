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
                    {{--<option value="?lan=en">language</option>--}}

                    {{--<option value="/?lan=zh-CN"  >简体中文</option>--}}
                    {{--<option value="?lan=en">English</option>--}}
                {{--</select></li>--}}


        <li><a href="{{ route('api') }}">{{ trans('Api') }}</a></li>


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


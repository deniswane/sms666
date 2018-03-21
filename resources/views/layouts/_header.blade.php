<ul class="topnav">
    @guest
        <li><a href="{{ route('login') }}">Login</a></li>
        @else
            <li>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    Logout
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>
            <li><a href="#">
                    {{ Auth::user()->name }}
                </a></li>
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
            <li><a href="{{route('private_numbers')}}">Private numbers</a></li>
            <li><a href="{{route('inactive_numbers')}}">Inactive numbers</a></li>
            <li><a href="{{route('contact')}}">Contact</a></li>
            <li><a href="{{route('home')}}">Home</a></li>
            <li class="icon"><a href="javascript:void(0);" onclick="myFunction()">&#9776;</a></li>
            <li><a target="_blank" href="#"><img id="android_img"
                                                 src="img/android-app_google-play_button.png"
                                                 alt="Android App"
                                                 style="height: 35px; margin-top: -12px;"></a>
            </li>

</ul>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
{{---jquery需要引入的文件-->--}}
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.js"></script>

<!--ajax提交表单需要引入jquery.form.js-->
<script type="text/javascript" src="http://malsup.github.io/jquery.form.js"></script>
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
            {{--<li><a href="{{route('inactive_numbers')}}">Inactive numbers</a></li>--}}
            {{--<li><a href="{{route('contact')}}">Contact</a></li>--}}


            <li><a href="{{route('home')}}">Home</a></li>
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
    layui.use('layer', function(){
        var layer = layui.layer;

    });
    function myTab() {
        $.ajax({
            type: 'get',
            url: "{{route('getprice')}}",
            data:'',
            cache: false,
            success: function (data) {

                layer.tab({
                    area: ['600px', '400px'],
                    tab: [{
                        title: 'API',
                        content: '<p "><strong style="font-size: 18px;">address</strong><br/>1.get phone number :</br>http://sms-receive-online.info/manager/api/getPhoneNumber?token=Your token<br/>'+
                        '2.get content :</br>http://sms-receive-online.info/manager/api/getSmsContent?token=Your token&phone=The phone number obtained from the first step <br/><strong style="font-size: 18px;">response</strong><br/>' +
                        '{"code":200,"msg":"success"}<br/>{"code":101,"msg":"Not sufficient funds"}<br/>' +
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

                    },{
                        title: 'Recharge',
                        content:
                        '@guest Please register and log in @else
                         <form id="addForm" ><input id="pri"  onkeyup='+'value=value.replace(/[^1234567890.]+/g,"") >'+' USD (Input number)<br>\
                        <input style="display:none" ></input><img id="submitAdd" style="cursor:pointer" src="/img/btn_buynow.gif" alt="PayPal - The safer, easier way to pay online!"></form> @endguest'

                    }
                    ]
                });
                 $("#submitAdd").click(function(){
                    $.ajax({
                        type:'post',
                        url:"{{route('ec-checkout')}}",
                        cache: false,
                        data:{'_token':"{{csrf_token()}}",'prices':$('#pri').val()},
                        dataType:'json',
                        beforeSend: function () {
                            layer.msg('Loading...', { icon: 16, shade: [0.5, '#f5f5f5'], scrollbar: false, offset: '50%', time: 300000 });
                        },
                        success:function(data){
//                            console.log(data)

                            if(data.code =='1'){
                                layer.alert('Incomplete filling format',{btn:'OK'});
                            }else if(data.code ==200){
                                console.log(data.msg)
                                $(location).attr('href', data.msg);
                            }
                        },
                        error:function(){
                        }
                    })

                })
            },
            error:function(){
            }
        });
    }

</script>


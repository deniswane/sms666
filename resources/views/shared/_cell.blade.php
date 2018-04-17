{{--每个国家号码--}}
@if(count($numbers)>0)
    @foreach($numbers as $num)
        <div class="Cell">
            <div>{{$num->country}}<br>
                {{--号码-国家图片-国家--}}
                <a href="{{ route('phone.detail',$num->id) }}">
                    {{--<img src="{{ asset("img/flags/aodili.gif") }}" alt="SMS - {{ $num->country }}"--}}
                    <img src="{{ $num->src }}" alt=""
                         style="vertical-align: middle;">&nbsp;&nbsp;
                    @php
                    echo preg_replace("/[^\.]{1,3}$/","****",$num->phone);
                    @endphp
                    </br>
                </a>
                <strong> SMS received:{{ $num->amount }}
                    <section
                            style="border:none; height: auto; padding: 1px; width: auto; background: #33FF66;">
                    </section>
                </strong>
            </div>
        </div>
    @endforeach
@endif
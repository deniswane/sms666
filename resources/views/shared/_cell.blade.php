{{--每个国家号码--}}
@foreach($numbers as $num)
    <div class="Cell">
        <div>{{$num->country}}<br>
            {{--号码-国家图片-国家--}}
            <a href="{{ route('phone.detail',$num->id) }}">
                <img src="{{ asset("img/Latvia.jpg") }}" alt="SMS - {{ $num->country }}" style="vertical-align: middle;">&nbsp;&nbsp;{{$num->phone}}<br>
            </a>
            <strong> SMS received:{{ $num->amount }}
                <section
                        style="border:none; height: auto; padding: 1px; width: auto; background: #33FF66;">
                </section>
            </strong>
        </div>
    </div>
@endforeach
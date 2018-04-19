@if(count($contents)>0)
    <table id="msgs" align="center">
        <tr>
            <th width="auto">From</th>
            <th width="auto">SMS Messages</th>
            <th width="auto">Added</th>
            <th width="auto" rowspan="15">
                {{--<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>--}}
                {{--<ins class="adsbygoogle tow_1"--}}
                     {{--style="width:300px;height:600px"--}}
                     {{--data-ad-client="ca-pub-4371427440572181"--}}
                     {{--data-ad-slot="3618379681"></ins>--}}
            </th>
        </tr>
        @foreach($contents as $con)
            <tr>
                <td data-label="From   :">
                    @php
                        echo preg_match('/\d/is', $con->from) ? preg_replace("/[^\.]{1,3}$/","xxxx",$con->from) :preg_match('/\d/is', $con->from)  ;
                    @endphp
                </td>
                <td id="divhid1" data-label="Message:">{{$con->content}}</td>
                <td data-label="Added:">{{$con->created_at->diffForHumans()}}</td>
            </tr>
        @endforeach
    </table>
@endif

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Jump prompt
    </title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
        .system-message{ padding: 24px 48px; }
        .system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
        .system-message .jump{ padding-top: 10px}
        .system-message .jump a{ color: #333;}
        .system-message .success,.system-message .error{ line-height: 1.8em; font-size: 36px }
        .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display:none}
    </style>
</head>
<body>
<div class="system-message">
    @if($error ==1)
    <p class="error">activation failure！</p><p class="detail"></p>
    <p class="jump">
        Page automatically
        <a id="href" href="javascript:history.back(-1);">jump</a> waiting time： <b id="wait">3</b>
        [ <a href="/">back home</a> ]</p>
    @elseif($error ==2)
    <p class="error">activation success！</p><p class="detail"></p>
    <p class="jump">
        Page automatically
        <a id="href" href="/">jump</a> waiting time： <b id="wait">3</b>
        [ <a href="/">back home</a> ]</p>
    @elseif($error ==3)
        <p class="error">Please login to your mailbox to complete the activation and registration !</p><p class="detail"></p>

        <p class="jump">
            Page automatically
            <a id="href" href="/">jump</a> waiting time： <b id="wait">3</b>
            [ <a href="/">back home</a> ]</p>
    @else
        <p class="jump">
            Page automatically
            <a id="href" href="/">jump</a> waiting time： <b id="wait">3</b>
            [ <a href="/">back home</a> ]</p>
    @endif
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time == 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
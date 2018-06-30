<?php

return [
    'alipay' => [
        // 支付宝分配的 APPID
        'app_id' => '2016091400508627',

        // 支付宝异步通知地址
        'notify_url' => 'http://www.sms-receive-online.info/alinotify',

        // 支付成功后同步通知地址
        'return_url' => 'http://www.sms-receive-online.info/alireturn',

        // 阿里公共密钥，验证签名时使用
//        'ali_public_key' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuWJKrQ6SWvS6niI+4vEVZiYfjkCfLQfoFI2nCp9ZLDS42QtiL4Ccyx8scgc3nhVwmVRte8f57TFvGhvJD0upT4O5O/lRxmTjechXAorirVdAODpOu0mFfQV9y/T9o9hHnU+VmO5spoVb3umqpq6D/Pt8p25Yk852/w01VTIczrXC4QlrbOEe3sr1E9auoC7rgYjjCO6lZUIDjX/oBmNXZxhRDrYx4Yf5X7y8FRBFvygIE2FgxV4Yw+SL3QAa2m5MLcbusJpxOml9YVQfP8iSurx41PvvXUMo49JG3BDVernaCYXQCoUJv9fJwbnfZd7J5YByC+5KM4sblJTq7bXZWQIDAQAB',
        'ali_public_key' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuL1KE0iTP5J0DcVyXDK3i8JEMNRL/uZpypZqqxnBY8VFO+j9e1XyGY/OT10bVzuwJlDqcYUY0SG21t1Q1AGlhJadrXcnFI6qO8WJKMlXz1tV1PsEvNhSZxsmIzTXADIQZoXvV/0XQ/3qYnTJSPUGoy32lOVcV4ZnOxlkBxRuZo1NchigusypUicI5A547Xa68Za57zRRAnb5LJgk9I6j11EW97srXRvCDSC7kmaB6f3sGZXKK6rESX7Dh3MKQEuBYCmwJhIuxbjHdTT0byERaE1XBqWb+eG9OKDesgvzcR0qlE0KjKWkIqi8NrLAwqyYt5rDTAmj9ErIie2E3H9KWwIDAQAB',
        // 自己的私钥，签名时使用
        'private_key' =>'MIIEpAIBAAKCAQEAt5GQfUsoQZgNApkzhIEolwEz7qYn6FRMBKU+bJpKyqCv3jxx8SQu+8Q/6Sy70UPNeYE7Ur24V0L2SY7TdGWG52hN5m6RVYU7pBQcoR9ElTF9qUzHHG3c8s6zOn4RWCUDpsUUUkqUp8jvqiRHoucefS4DY1eySdoVOTvhFCYemkRLsj8Xnx8CgTKeFL6U2onFeLDLQDghLBMpQmGaro3kDqFAb3PkUiKDOav6t7A3rZ5YuIdodlvMB9i0mgskyWcTDgFgakp9sDSZqHeQ5tPl9nP7N0d8CDccJad7vksPLxP82GcT8Hi6ZFu3qQH+lxVTZNvKmzWOsX2F0xdyB5JvWQIDAQABAoIBAQCwFOEUTlN/F+ri4zGXmIzmzDCUaq09Hh8NFbSbWCUF2LzZ4AEr3hlzRvxHHrHKOc+PDXdqFrIMgh7c0DPlIr4UmuiecNDXx8U9zkzoAKY4thBjpVIY4wldnwgsw2C/vEGUZtbnWMLfbs711Xchu7BzQv/c+vH6BUb2b4mnWilgryED7gHclYoURJk1l93E5dLabbNsLLYj+MLEu+W1LdRVJdBCZIbi39+Pp/kCtxt4vybLbwyGAubZmGptYn/nT/jNejreEBVeOwjgDZiyquG0fbWPY/q0a06Q1/u99xNpBoAVUNDF9BgEDcnwWKDe60nMh4aCrcAq7n0/74XIK2ABAoGBAOpzP1eWwvYM/pNwof2OsiS1MSjT+PXg72+IkA0DZZLyX2ocOFRBwlquKXuTmEaABVXJDG3XLZdfPT/59XoWqRWQBRw5uizuh6nQ8eGN+wLpiZWZ0XS8VURPkxpNXo7O9a9t0Xdv5jwVIVsImbP3P2ytR/XJdXkB/S8MC+G7/d8ZAoGBAMhxCxBT2IkforAIMA8h3eqmb765yFBuEWOZYETRtztFVGKCMOeJ/s36s4YzFDxN30CHyuTGFM6z8o2v3XKfptvyaGRojcKoMpvwtiAydJfcxG9V0K9uZAOBvYp3KtnFEhS/SMRZuBjT3O9/O3V3dELhunTuukYxGmad8c203lpBAoGACrxWRTOBH/U+XxAESvES1T16z8zNFK1FKY1OU1o5d34jwl8icTFFrhNVkPQUP/4ywFfhetIko07YJirTA9Ev0u3yXfWCwfX2Pl90BAkVWm/JPhF6Fudc3DDsooKydsWhWHQl6Fs2Zr/s9Bczupryy44vwmCEQZNGvbXGgYKzQFkCgYEApHGRuSCAyuboavEctJ19WhCYJup2e/4BSCxB/dPsNrVHaNYU1zCwmj6u4E+xr1PX8DI38/7KfVbGjRWWYX63v0Ud/hqFCwlBFAyk7r9WRmz5v7mwzuyLIxFi9mGUBzuV/O19/pD4522Rme9RUarh+CkG9v4QVpvcZAn6omBb98ECgYBEgGgLUR1TxwRvQdTkp+cCO1w3fGu7dn42n+6/dy3YYZ1m/EQfUdWhFfYlgzKQr/Ot7RPlHWzVVvxd7Ip8s8Civa/mekXlmCxHCSBpQXgwEeAec0QIp2AsKxh5zVhitu+Qu46R9CWZWF/y9JK+8IXfOKEEJzOsN4E2HrqxA5wXAw=='
        ,

        'log' => [
            'file' => storage_path('logs/alipay.log'),
        //     'level' => 'debug'
        ],

        // optional，设置此参数，将进入沙箱模式
         'mode' => 'dev',
    ],

    'wechat' => [
        // 公众号 APPID
        'app_id' => 'wx08fbba9216c0cc40',

        // 小程序 APPID
        'miniapp_id' => '',

        // APP 引用的 appid

        // 微信支付分配的微信商户号
        'mch_id' => '1497130592',

        // 微信支付异步通知地址
        'notify_url' => '',

        // 微信支付签名秘钥
        'key' => 'beite8079123003219708etiebzheke6',

        // 客户端证书路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_client' => '',

        // 客户端秘钥路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_key' => '',

        // optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
        'log' => [
            'file' => storage_path('logs/wechat.log'),
        //     'level' => 'debug'
        ],

        // optional
        // 'dev' 时为沙箱模式
        // 'hk' 时为东南亚节点
         'mode' => 'dev',
    ],
];

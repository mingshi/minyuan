<?php
$config = array(
    'login' => array(
        'username|用户名' => 'required',
        'password|密码' => 'required',
        'captcha|验证码' => 'required|alpha|valid_captcha',
    ),
);

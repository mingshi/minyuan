<?php
class MY_Captcha extends Util_Captcha
{
    function onGenerateCode()
    {
        $ci = get_instance(); 
        $ci->ci_session->set_userdata(array(
            'cap_word' => $this->sCode,
            'cap_time' => time(),
        ));
    }

    function validate($sUserCode) {
        $ci = get_instance(); 
        $code = $ci->ci_session->userdata('cap_word');
        $time = $ci->ci_session->userdata('cap_time');
        $expire = 7200;

        return time() - $time < 7200 && strtolower($sUserCode) == strtolower($code); 
    }
}

function create_captcha()
{
    $ci = get_instance();

    $fonts = array(
        PATH_FONT . DS . 'VeraBd.ttf',
        PATH_FONT . DS . 'VeraIt.ttf',
        PATH_FONT . DS . 'Vera.ttf',
    );
    
    $cap = new MY_Captcha($fonts, 100, 30);
    $cap->SetNumChars(4);

    return $cap->create();
}

function validate_captcha($code)
{
    return MY_Captcha::validate($code);  
}

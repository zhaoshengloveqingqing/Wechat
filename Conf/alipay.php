<?php

/// 支付宝接口的配置参数
return array (
    'alipay_config' => array(
        'sign_type' => 'MD5',
        'input_charset' => 'utf-8',
        'cacert' => getcwd().'\\Conf\\Wap\\key\\cacert.pem',
        'transport' => 'http',
    ),
 );

?>
<?php

/* 
 * 发送短信模板,【#merchant#】部分为亿美要求的签名，若为空则短信发送失败
 */
return array(
    'dine_notify'  => '【#merchant#】新订餐#sn#提醒，来自#username#，手机号为#tel#，用餐时间#dinetime#，#table#，#guestnum#人，点菜#dishnum#份，包括#menus#',
    'vcode'        => '【#merchant#】#code#，您正在使用短信验证码，十分钟内有效。请妥善保管！',
    'host_notify'  => '【#merchant#】微预定新订单#sn#提醒，来自#book_people#，电话：#tel#，内容：#room_type#，数量：#book_num#，总价：#price#元，预订时间：#dateline#',
    'card_binding' => '【#merchant#】#code#，您正在使用验证码绑定微信会员卡，五分钟内有效。',
    );
?>
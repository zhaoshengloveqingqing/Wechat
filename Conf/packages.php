<?php
/// 该文件定义商户和代理商的套餐列表
return array(
    // 商户套餐列表
    'MERCHANT_PACKAGES'                  =>  array( //商户套餐列表. 切记：不要改已有套餐的ID
        '1' => array(
            'id' => 1,
            'type' => 1, 
            'name' => '通用基础版套餐',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,22,23',
            'price_month' => 250.0,
            'detail_img' => 'themes/agent/img/basic.jpg',
        ),
        '2' => array(
            'id' => 2,
            'type' => 1, 
            'name' => '电商行业套餐',
            'function_groups' => '0,1,3,4,5,6,7,8,9,10,11,12,14,16,22,23',
            'price_month' => 400.0,
            'detail_img' => 'themes/agent/img/shop.jpg',
        ),
        '3' => array(
            'id' => 3,
            'type' => 1, 
            'name' => '餐饮行业套餐',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,17,22,23',
            'price_month' => 400.0,
            'detail_img' => 'themes/agent/img/dinner.jpg',
        ),
        '4' => array(
            'id' => 4,
            'type' => 1, 
            'name' => '宾馆行业',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,18,22,23',
            'price_month' => 400.0,
            'detail_img' => 'themes/agent/img/hotel.jpg',
        ), 
        '5' => array(
            'id' => 5,
            'type' => 1, 
            'name' => '汽车行业',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,21,22,23',
            'price_month' => 560,
            'detail_img' => 'themes/agent/img/car.jpg',
        ),
        '6' => array(
            'id' => 6,
            'type' => 1, 
            'name' => '房地产行业',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,20,22,23',
            'price_month' => 560,
            'detail_img' => 'themes/agent/img/fangchan.jpg',
        ),
        '7' => array(
            'id' => 7,
            'type' => 2, 
            'name' => '短信套餐',
            'function_groups' => '',
            'price_month' => 0.1,//单条短信的价格,复用price_month
            'detail_img' => '',
        ),
    ),
    
    'merchant_invoice_ratio' => 1.0,
    
    
    
    
    // 代理商套餐列表
    'AGENT_PACKAGES'                  =>  array( //代理商套餐列表. 切记：不要改已有套餐的ID
        '1' => array(
            'id' => 1,
            'type' => 1, 
            'name' => '通用基础版套餐',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,22,23',
            'price_month' => 100.0,
            'detail_img' => 'themes/agent/img/basic.jpg',
        ),
        '2' => array(
            'id' => 2,
            'type' => 1, 
            'name' => '电商行业套餐',
            'function_groups' => '0,1,3,4,5,6,7,8,9,10,11,12,14,16,22,23',
            'price_month' => 150.0,
            'detail_img' => 'themes/agent/img/shop.jpg',
        ),
        '3' => array(
            'id' => 3,
            'type' => 1, 
            'name' => '餐饮行业套餐',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,17,22,23',
            'price_month' => 150.0,
            'detail_img' => 'themes/agent/img/dinner.jpg',
        ),
        '4' => array(
            'id' => 4,
            'type' => 1, 
            'name' => '宾馆行业',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,18,22,23',
            'price_month' => 150.0,
            'detail_img' => 'themes/agent/img/hotel.jpg',
        ),
        '5' => array(
            'id' => 5,
            'type' => 1, 
            'name' => '汽车行业',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,21,22,23',
            'price_month' => 200.0,
            'detail_img' => 'themes/agent/img/car.jpg',
        ),
        '6' => array(
            'id' => 6,
            'type' => 1, 
            'name' => '房地产行业',
            'function_groups' => '0,1,3,5,6,7,8,9,10,11,12,14,16,20,22,23',
            'price_month' => 200.0,
            'detail_img' => 'themes/agent/img/fangchan.jpg',
            
        ),
        '7' => array(
            'id' => 7,
            'type' => 2, 
            'name' => '短信套餐',
            'function_groups' => '',
            'price_month' => 0.1,//单条短信的价格,复用price_month
            'detail_img' => '',
        ),
    ),
);
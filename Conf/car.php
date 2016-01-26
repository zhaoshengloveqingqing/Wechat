<?php
// 汽车行业方案相关配置
return array(
    //变速箱列表
    'car_box_options' =>  array(
        1 => array(
            'id' => 1,
            'name' => "自动变速箱(AT)"),
        2 => array(
            'id' => 2,
            'name' => "手动变速箱(MT)"),
        3 => array(
            'id' => 3,
            'name' => "手自一体"),
        4 => array(
            'id' => 4,
            'name' => "无级变速箱(CVT)"),
        5 => array(
            'id' => 5,
            'name' => "无级变速(VDT)"),
        6 => array(
            'id' => 6,
            'name' => "双离合变速箱(DCT)"),
        7 => array(
            'id' => 7,
            'name' => "序列变速箱(AMT)"),
    ),
    
    // 实用工具列表
    'car_tools' => array(
        1 => array(
            'id' => 1,
            'name' => '保险计算',
            'link' => 'http://car.m.yiche.com/qichebaoxianjisuan/',
        ),
        2 => array(
            'id' => 2,
            'name' => '车贷计算',
            'link' => 'http://car.m.yiche.com/qichedaikuanjisuanqi/',
        ),
        3 => array(
            'id' => 3,
            'name' => '全款计算',
            'link' => 'http://car.m.yiche.com/gouchejisuanqi/',
        ),
        4 => array(
            'id' => 4,
            'name' => '车型比较',
            'link' => 'http://car.m.yiche.com/chexingduibi/?carIDs=102501',
        ),
        5 => array(
            'id' => 5,
            'name' => '违章查询',
            'link' => 'http://wap.bjjtgl.gov.cn/wimframework/portal/wwwroot/bjjgj/xxcx.psml?contentType=%E8%BF%9D%E6%B3%95%E6%9F%A5%E8%AF%A2',
        ),
    ),
    'car_insurance_mileage_unit' => 5000, //公里
);
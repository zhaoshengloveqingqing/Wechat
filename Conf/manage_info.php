<?php
return array(
    //mapping from token to function
    'manage_functions'	=> array('shangcheng' => 'Shop','dingdan' =>'Booking','huiyuanka' =>'Member','canyin' => 'Dining','binguan' => 'Hotel','shenhe'=>'Fxs'),
    
    
    //操作员权限action-list与模块Module、Action的映射，用于高亮显示菜单栏
    //'manage_modules'=>模块=>模块功能=>action集合，入口
    'manage_modules' => array(
                        
                        'Shop'    => array(  
                                             'order'     =>array('active_action_set'=>array('order','orderList'),
                                                                 'default_entry_url'=> U('Manage/Shop/orderList',array('status'=>1))),
                                             'product'   =>array('active_action_set'=>array('product','productList'),
                                                                 'default_entry_url'=> U('Manage/Shop/productList')),
                                             'category'  =>array('active_action_set'=>array('category','categoryList'),
                                                                 'default_entry_url'=> U('Manage/Shop/categoryList')),
                                                'audit'  =>array('active_action_set'=>array('audit','auditList'),
                                                    'default_entry_url'=> U('Manage/Shop/auditList')),
                                          ),
						'Fxs'    => array(  
                                             'check'     =>array('active_action_set'=>array('Fxs','check'),
                                                                 'default_entry_url'=> U('Fxs/Fxs/fxs_list')),                                     
                                          ),
                        'Booking'  => array(
                                             'order' =>array(   'active_action_set'=>array('order','orderList','merchantList'),
                                                                'default_entry_url'=>  U('Manage/Booking/merchantList')),
                                           ),
                        'Hotel'    => array(
                                               'order' =>array( 'active_action_set'=>array('order','orderList','merchantList'),
                                                                'default_entry_url'=> U('Manage/Hotel/index')),
                                            ),
                        'Member'   => array(
                                              'users' 	=> array(  'active_action_set'=>array('users'),
                                                                   'default_entry_url' => U('Manage/Member/users')),
                                              'points' 	=> array(  'active_action_set'=>array('points'),
                                                                   'default_entry_url'=> U('Manage/Member/points')),
                                              'exchanges' =>array( 'active_action_set'=>array('exchanges'),
                                                                    'default_entry_url'=>U('Manage/Member/exchanges')),
                                            ),
                        'Dining'   =>  array(  
                                                'order'    => array(  'active_action_set'=>array('order','orderList'),
                                                                      'default_entry_url'=> U('Manage/Dining/orderList',array('status'=>2))),
                                                'menu'     => array(  'active_action_set'=>array('menu','menuList'),
                                                                      'default_entry_url'=> U('Manage/Dining/menuList')),
                                                'category'=> array(   'active_action_set'=>  array('category','categoryList'),
                                                                      'default_entry_url'=> U('Manage/Dining/categoryList')),
                                            ),
                                ),

	'manage_menu_lang' => array(
        'Shop'      => '商城管理',
        'category'  => '分类管理',
        'product'   => '产品管理',
        'order'     => '订单管理',
        'audit'     => '订单管理',
        'Member'    => '会员卡管理',
        'points'    => '消费积分管理',
        'exchanges' => '积分兑换管理',
        'users'     => '会员管理',
        'Booking'   => '预订管理',
        'Dining'    => '餐厅管理',
        'menu'      => '菜品管理',
        'Hotel'     => '宾馆预订管理',
		'Fxs'		=> '分销商审核管理',
		'check'		=> '分销商审核',
    ),

    //模块以及相应权限
    'manage_actions'    => array(
            'shangcheng' => array('Shop-order', 'Shop-product', 'Shop-category', 'Shop-audit'),
            'dingdan' => array('Booking-order'),
            'huiyuanka' => array('Member-users', 'Member-points', 'Member-exchanges'),
            'canyin' => array('Dining-order', 'Dining-menu', 'Dining-category'),
            'binguan' => array('Hotel-order'),
			'shenhe' => array('Fxs-check'),
        ),

    'manage_action_lang' => array(
        'shangcheng'        => '商城管理',
        'Shop-category'     => '分类管理',
        'Shop-product'      => '产品管理',
        'Shop-order'        => '订单管理',
        'dingdan'           => '预定管理',
        'Booking-order'     => '订单管理',
        'huiyuanka'         => '会员卡管理',
        'Member-points'     => '消费积分管理',
        'Member-users'      => '会员管理',
        'Member-exchanges'  => '积分兑换管理',
        'canyin'            => '餐饮管理',
        'Dining-category'   => '分类管理',
        'Dining-menu'       => '菜品管理',
        'Dining-order'      => '订单管理',
        'binguan'           => '宾馆预订管理',
        'Hotel-order'       => '宾馆订单管理',
		'shenhe'            => '分销商审核',
		'Fxs-check'			=> '分销商审核',
        'Shop-audit'	=> '查看已审核订单',
    )
);
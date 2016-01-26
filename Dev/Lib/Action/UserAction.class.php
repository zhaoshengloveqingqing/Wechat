<?php
class UserAction extends BaseAction 
{

    protected $function;
    protected $token;
    protected $uid;
    protected $agent_cfg;

    protected function _initialize()
    {
        parent::_initialize();
        $this->token = session('token');
        $this->uid = session('uid');

        if (isset($_POST['param1']))
        // hack for uploadify flash 302 error without session-id
        {  
            $sid = base64_decode($_POST['param1']);
            if (session_id() != $sid) 
            //destroy the automatical generated one or it won't be resumed to old one;
            {
                session_destroy(); 
            }
            session_id($sid);
            session_start();
        }


        //未登录用户
        if (empty($this->uid)) 
        {
            $this->redirect('User/Index/login');
        }
        else
        {
            $sql = "select ac.cfg_data, ag.role from tp_users as u left join tp_user as ag on u.administrator = ag.id LEFT JOIN tp_oem_cfg as ac on u.administrator = ac.agent_id where u.id = $this->uid; ";
            $Model = new Model();
            $user = $Model->query($sql);
            if ($user != false) 
            {
                $this->agent_cfg = $this->getAgentCfg($user[0]['cfg_data']);
                $this->assign('agentInfo', $this->agent_cfg);
                if ($user[0]['role'] == 18) 
                {
                    $this->assign('user_role', 'oem');
                }
            }
        }

        if (strcasecmp(GROUP_NAME, 'User') == 0 
                && strcasecmp(MODULE_NAME, 'Index') != 0
                && strcasecmp(MODULE_NAME, 'Function') != 0
                && strcasecmp(MODULE_NAME, 'Account') != 0
                && strcasecmp(MODULE_NAME, 'Help') != 0) 
        {

            //如果在公众列表页以及功能列表页以外的页面，检查session中保存的token
            //并检查session中的opened funcs，该值用于做“是否开通该功能”的检查
            if (!empty($this->token)) 
            {
                $wecha = M('Wxuser')->field('wxname,weixin,type')->where(array('token' => $this->token, 'uid' => $this->uid))->find();
                $this->assign('wecha',$wecha);
                $this->assign('token', $this->token);
            } 
            else 
            {
                $this->redirect('User/Index/index');
            }

            //如果不是在功能列表页和公众号页，检查是否保存当前公众号的功能，否则保存当前公众号开通的功能
            if (session('?opened_funcs') == false) 
            {
                //保存开通功能信息
                $token_open = M('Token_open')->field('id,queryname')->where(array('token'=>session('token'),'uid'=>session('uid')))->find();
                if (!empty($token_open)) 
                {
                    $opened_funcs = explode(',',$token_open['queryname']);
                    session('opened_funcs',$opened_funcs);
                 } 
                 else 
                 {
                    $this->redirect('User/Function/Index', array('internal'=>1));
                }
            }

            //判断该用户是否开通预定功能，是则启用弹出订单提醒菜单
            $opened_funcs = session('opened_funcs');
            $cur_func = 'dingdan';
            if (in_array($cur_func ,$opened_funcs))
            {
                //检查是否过期
                $uid = session('uid');
                $cur = time();
                $sql = 'select f.funname, ufg.expire_time from tp_function as f LEFT JOIN tp_function_group as fg on f.fgid = fg.id LEFT JOIN tp_user_func_group as ufg on ufg.group_id = f.fgid'
                          . " where ufg.user_id = '$uid' and f.funname = '$cur_func' and ufg.expire_time > $cur;";
                $Model = new Model();
                $func = $Model->query($sql);
                if ($func) 
                {
                    $this->assign('show_order_tips', true);
                }
            } 
        }
        $this->assign('sideMenu',$this->getFunctionSideMenu());
    }

    protected function checkOpenedFunction($curFunc = '') 
    {
        if (!empty($curFunc) || !empty($this->function)) 
        {
            $cur_func = $curFunc ? $curFunc : $this->function;
            $opened_funcs = session('opened_funcs'); 

            $uid = session('uid');
            $sql = 'select f.funname, ufg.expire_time '
                        .' from tp_function as f LEFT JOIN tp_function_group as fg on f.fgid = fg.id LEFT JOIN tp_user_func_group as ufg on ufg.group_id = f.fgid'
                        . " where ufg.user_id = '$uid' and f.funname = '$cur_func' and ufg.status = 1;";
            //检查是否过期
            $Model = new Model();
            $func = $Model->query($sql);
            if ($func) 
            {
                if ($func[0]['expire_time'] < time()) 
                {
                    $this->error('请充值后联系客服开通该功能。',U('Function/index',array('token'=>session('token'),'id'=>session('wxid'))));
                }
            }
            else
            {
                $this->error('请充值后联系客服开通该功能。',U('Function/index',array('token'=>session('token'),'id'=>session('wxid'))));
            }

            if(!empty($opened_funcs) && !in_array($cur_func ,$opened_funcs))
            {
                $this->error('您尚未开启该模块的使用权,请到功能模块中添加',U('Function/index',array('token'=>session('token'),'id'=>session('wxid'))));
            } 
        } 
        else 
        {
            $this->redirect('User/Index/index');
        }
    }

    protected function getAgentCfg($cfg_data_str)
    {
        $default_agent_cfg = C('default_agent_info');
        $agent_cfg;
        if (!empty($cfg_data_str)) 
        {
           $agent_cfg = unserialize($cfg_data_str); 
        }
        
        if (empty($agent_cfg['service_tel'])) 
        {
            $agent_cfg['service_tel'] = $default_agent_cfg['service_tel'];
        }

        if (empty($agent_cfg['service_qq'])) 
        {
            $agent_cfg['service_qq'] = $default_agent_cfg['service_qq'];
        }

        if (empty($agent_cfg['service_qq2'])) 
        {
            $agent_cfg['service_qq2'] = $default_agent_cfg['service_qq2'];
        }

        if (empty($agent_cfg['company_name'])) 
        {
            $agent_cfg['company_name'] = $default_agent_cfg['company_name'];
        }

        if (empty($agent_cfg['company_logo'])) 
        {
            $agent_cfg['company_logo'] = $default_agent_cfg['company_logo'];
        }

        if (empty($agent_cfg['company_qrcode_url'])) 
        {
            $agent_cfg['company_qrcode_url'] = $default_agent_cfg['company_qrcode_url'];
        }

        if (empty($agent_cfg['platform_name'])) 
        {
            $agent_cfg['platform_name'] = $default_agent_cfg['platform_name'];
        }

        if (empty($agent_cfg['icp_info'])) 
        {
            $agent_cfg['icp_info'] = $default_agent_cfg['icp_info'];
        }

        
        return $agent_cfg;
    }

    protected function getFunctionSideMenu() 
    {

        $token = session('token');
        // Menu => sub_menu
        $side_menu = array(
            '0' => array(
                    'name'      => '基础设置',
                    'id'        => 'setting',
                    'selected'  => MODULE_NAME == 'Function' || MODULE_NAME == 'Customer' || MODULE_NAME == 'Admin' || MODULE_NAME == 'Diymen' || MODULE_NAME == 'Sms' ? 1 : 0,
                    'sub_menu'  => array(
                                    '0' =>  array('selected'=> MODULE_NAME == 'Function'? 1 : 0,    'name' => '功能管理',           'url' => U('Function/index',array('token'=>$token, 'id'=>session('wxid')))),
                                    '1' =>  array('selected'=> MODULE_NAME == 'Customer' ? 1 : 0,    'name' => '公司信息',          'url' => U('Customer/index')),
                                   // '2' =>  array('selected'=> MODULE_NAME == 'Sms' ? 1 : 0,    'name' => '短信通知设置',           'url' => U('Sms/index')),
                                    '3' =>  array('selected'=> MODULE_NAME == 'Admin'? 1 : 0,     'name' => '操作员管理',           'url' => U('Admin/index')),
                                    '4' =>  array('selected'=> MODULE_NAME == 'Diymen' ? 1 : 0,'name' => '自定义菜单',       'url' => U('Diymen/index')),
                                    ),
                     ),
			  '14' => array(
                    'name'      => '微分销',
                    'id'        => 'Wsc',
                    'selected'  => MODULE_NAME == 'Wsc' ||MODULE_NAME == 'Fxs' || MODULE_NAME == 'OrderKeyword' || MODULE_NAME == 'Department'? 1 : 0,
                    'sub_menu'  => array(
									'0' =>  array('selected'=> MODULE_NAME == 'Wsc' && ACTION_NAME =='partner_index' ? 1 : 0,    'name' => '发展部门',          'url' => U('Wsc/partner_index')),   	
									'1' =>  array('selected'=> MODULE_NAME == 'Department' && ACTION_NAME =='index' ? 1 : 0,    'name' => '部门管理',           'url' => U('Department/index')),
									'2' =>  array('selected'=> MODULE_NAME == 'Fxs' && ACTION_NAME =='fxs_list' ? 1 : 0,    'name' => '分销商审核',          'url' => U('Fxs/fxs_list')),
									'3' =>  array('selected'=> MODULE_NAME == 'Fxs' && ACTION_NAME =='index' ? 1 : 0,    'name' => '分销商信息',          'url' => U('Fxs/index')),
                                    ),
                     ),
            '1' => array(
                    'name'      => '微信客服',
                    'id'        => 'cs',
                    'selected'  => MODULE_NAME == 'Areply' || MODULE_NAME == 'Text' || MODULE_NAME == 'Voice'  || MODULE_NAME == 'Other' || MODULE_NAME == 'Img' || MODULE_NAME == 'CSSetting'? 1 : 0,
                    'sub_menu'  => array(
                                    '0' =>  array('selected'=> MODULE_NAME == 'Areply'? 1 : 0,      'name' => '关注时回复',           'url' => U('Areply/index')),
                                    '1' =>  array('selected'=> MODULE_NAME == 'Text' ? 1 : 0,       'name' => '自动文本回复',              'url' => U('Text/index')),
                                    '2' =>  array('selected'=> MODULE_NAME == 'Img'? 1 : 0,         'name' => '自动图文回复',               'url' => U('Img/index')),
                                    '3' =>  array('selected'=> MODULE_NAME == 'Voice'? 1 : 0,       'name' => '自动语音回复',              'url' => U('Voice/index')),
                                    '4' =>  array('selected'=> MODULE_NAME == 'Other'? 1 : 0,       'name' => '匹配不上回复',                'url' => U('Other/index')),
                                    '5' =>  array('selected'=> MODULE_NAME == 'CSSetting'? 1 : 0,   'name' => '多客服设置',              'url' => U('CSSetting/index')),
                                    ),
                     ),
            '2' => array(
                    'name'      => '微信官网',
                    'id'        => 'web',
                    'selected'  => MODULE_NAME == 'Home' || MODULE_NAME == 'Flash' || MODULE_NAME == 'Classify' || MODULE_NAME == 'Article' || MODULE_NAME == 'Tmpls' ? 1 : 0,
                    'sub_menu'  => array(
                                    '0' =>  array('selected'=> MODULE_NAME == 'Home'? 1 : 0,    'name' => '① 微网站图文消息',       'url' => U('Home/set')),
                                    '1' =>  array('selected'=> MODULE_NAME == 'Tmpls'? 1 : 0,   'name' => '② 网站模板选择',         'url' => U('Tmpls/index')),
                                    '2' =>  array('selected'=> MODULE_NAME == 'Flash'? 1 : 0,    'name' => '③ 主页幻灯片设置',        'url' => U('Flash/index')),
                                    '3' =>  array('selected'=> MODULE_NAME == 'Classify'? 1 : 0,'name' => '④ 栏目管理',         'url' => U('Classify/index')),
                                    '4' =>  array('selected'=> MODULE_NAME == 'Article'? 1 : 0,    'name' => '⑤ 发布文章',     'url' => U('Article/index')),
                                    ),
                    ),
            '3' => array(
                    'name'      => '微信会员卡',
                    'id'        => 'membership',
                    'selected'  => MODULE_NAME == 'Membership' || MODULE_NAME == 'Member_card' || MODULE_NAME == 'Member' ? 1 : 0,
                    'sub_menu'  => array(
                                    '0' =>  array('selected'=> (MODULE_NAME == 'Membership' && ACTION_NAME == 'img') || MODULE_NAME == 'Member_card' && in_array(ACTION_NAME, array('info','index','create_add','exchange')) || (MODULE_NAME == 'Member' && ACTION_NAME == 'setmemberinfo') ? 1 : 0,'name' => '会员卡设置',     'url' => U('Membership/img')), 
                                    '1' =>  array('selected'=> MODULE_NAME == 'Member_card' && ACTION_NAME =='create' ? 1 : 0,    'name' => '微信会员管理',         'url' =>U('Member_card/create')), 
                                    '2' =>  array('selected'=> MODULE_NAME == 'Member' && ACTION_NAME =='physicalmemberadmin' ? 1 : 0,    'name' => '实体会员管理',         'url' => U('Member/physicalmemberadmin')),
                                    '3' =>  array('selected'=> MODULE_NAME == 'Member_card' && in_array(ACTION_NAME , array('privilege' , 'privilege_add') ) ? 1 : 0,    'name' => '会员特权管理',     'url' => U('Member_card/privilege')),
                                    '4' =>  array('selected'=> MODULE_NAME == 'Member_card' && in_array(ACTION_NAME , array('coupon','coupon_add' ,'coupon_edit' , 'integral' , 'integral_add' ,'integral_edit' ))  ? 1 : 0,    'name' => '会员营销管理',     'url' => U('Member_card/coupon')),
                                    '5' =>  array('selected'=> MODULE_NAME == 'Member' && ACTION_NAME =='chargelist' ? 1 : 0,    'name' => '会员充值管理',         'url' => U('Member/chargelist')),
                                    '6' =>  array('selected'=> MODULE_NAME == 'Member' && ACTION_NAME =='index' ? 1 : 0,    'name' => '会员消费管理',         'url' => U('Member/index')),
                                    '7' =>  array('selected'=> MODULE_NAME == 'Member' && ACTION_NAME =='scorecost' ? 1 : 0,    'name' => '积分兑换管理',         'url' => U('Member/scorecost')),                                    
                                    
                                    ),
                    ),
            '5' => array(
                            'name'         => '微信相册',
                            'id'        => 'album',
                            'selected'    => MODULE_NAME == 'Photo' ? 1 : 0,
                            'sub_menu'  => array(
                                '0' =>  array('selected'=> MODULE_NAME == 'Photo' && ACTION_NAME =='set' ? 1 : 0,'name' => '微相册图文消息',     'url' => U('Photo/set')),
                                '1' =>  array('selected'=> MODULE_NAME == 'Photo' && ACTION_NAME !='set' ? 1 : 0,    'name' => '微相册管理',           'url' => U('Photo/index')),
                                ), 
                ),
            '6' => array(
                    'name'      => '3D全景相册',
                    'id'        => 'panorama',
                    'selected'  => MODULE_NAME == 'Panorama' ? 1 : 0,
                    'sub_menu'  => array(
                                        '0' =>  array('selected'=> MODULE_NAME == 'Panorama' && ACTION_NAME =='set' ? 1 : 0,'name' => '全景相册图文消息',     'url' => U('Panorama/set'),'status' => 'new'),
                                        '1' =>  array('selected'=> MODULE_NAME == 'Panorama' && ACTION_NAME !='set' ? 1 : 0,    'name' => '全景相册管理',   'url' => U('Panorama/index')),
                                    ), 
                    ),         
            '7' => array(
                    'name'      => '互动营销工具',
                    'id'        => 'market',
                    'selected'  => MODULE_NAME == 'Lottery' || MODULE_NAME == 'Coupon' || MODULE_NAME == 'Guajiang' || MODULE_NAME == 'Golden'  || MODULE_NAME == 'Vote' || MODULE_NAME == 'Signin'  || MODULE_NAME == 'RedCash'  ? 1 : 0,
                    'sub_menu'  => array(
                                    '0' =>  array('selected'=> MODULE_NAME == 'Vote' ? 1 : 0,'name' => '微投票',             'url' => U('Vote/index'), 'status' => 'new'),
                                    '1' =>  array('selected'=> MODULE_NAME == 'Lottery' ? 1 : 0,'name' => '幸运大转盘',           'url' => U('Lottery/index')),
                                    '2' =>  array('selected'=> MODULE_NAME == 'Coupon' ? 1 : 0,'name' => '优惠券',             'url' => U('Coupon/index')),
                                    '3' =>  array('selected'=> MODULE_NAME == 'Guajiang' ? 1 : 0,'name' => '刮刮卡',             'url' => U('Guajiang/index')),
								    '4' =>  array('selected'=> MODULE_NAME == 'Golden' ? 1 : 0,'name' => '砸金蛋',             'url' => U('Golden/index')),
								    '5' =>  array('selected'=> MODULE_NAME == 'Signin' ? 1 : 0,'name' => '微信签到',   'url' => U('Signin/index')),
								    '6' =>  array('selected'=> MODULE_NAME == 'RedCash' ? 1 : 0,'name' => '微信红包',   'url' => U('RedCash/wxconf')),
                                    ),
                    ),
            '8' => array(
                    'name'      => '微订单预订',
                    'id'        => 'order',
                    'selected'  => MODULE_NAME == 'Host' || MODULE_NAME == 'HostPay' || MODULE_NAME == 'Reservation'   || MODULE_NAME == 'AttendApply' ? 1 : 0,
                    'sub_menu'  => array(  
                                    '0' =>  array('selected'=> MODULE_NAME == 'Host' ? 1 : 0,'name' => '预约/报名/预订',   'url' => U('Host/index')),
									'1' =>  array('selected'=> MODULE_NAME == 'Reservation' ? 1 : 0,'name' => '课程预约',   'url' => U('Reservation/index')),
									//'2' =>  array('selected'=> MODULE_NAME == 'AttendApply' ? 1 : 0,'name' => '参会申请',   'url' => U('AttendApply/index'))
                                    ), 
                    ),
			'9' => array(
                    'name'      => '微现场',
                    'id'        => 'wall',
                    'selected'  => MODULE_NAME == 'Wall' || MODULE_NAME == 'Signin' ? 1 : 0,
                    'sub_menu'  => array(  
                                    '0' =>  array('selected'=> MODULE_NAME == 'Wall' ? 1 : 0,'name' => '微现场',   'url' => U('Wall/index'), 'status' => 'new')
                                    ), 
                    ),
			'10' => array(
                    'name'      => '微评论',
                    'id'        => 'reply',
                    'selected'  => MODULE_NAME == 'Reply' || MODULE_NAME == 'Impress' ? 1 : 0,
                    'sub_menu'  => array(  
                                    '0' =>  array('selected'=> MODULE_NAME == 'Reply' ? 1 : 0,'name' => '微评论',   'url' => U('Reply/index'), 'status' => 'new'),
									'1' =>  array('selected'=> MODULE_NAME == 'Impress' ? 1 : 0,'name' => '微印象',   'url' => U('Impress/index'), 'status' => 'new'),
                                    ), 
                    ),
            /*'9' => array(
                    'name'      => '微信推广页',
                    'id'        => 'brcode',
                    'selected'  => MODULE_NAME == 'Adma' ? 1 : 0,
                    'sub_menu'  => array( 
                                    '0' =>  array('selected'=> MODULE_NAME == 'Adma' ? 1 : 0,'name' => '微信推广页',          'url' => U('Adma/index')),
                                    ),    
                    ),*/
           /* '11' => array(
                    'name'      => 'WIFI接入',
                    'id'        => 'order',
                    'selected'  => MODULE_NAME == 'Wifi' ? 1 : 0,
                    'sub_menu'  => array(  
                                    '0' =>  array('selected'=> MODULE_NAME == 'Wifi' && ACTION_NAME =='index' ? 1 : 0,'name' => '路由器设置',   'url' => U('Wifi/index')),
                                    '1' =>  array('selected'=> MODULE_NAME == 'Wifi' && ACTION_NAME =='weixin' ? 1 : 0,'name' => '微信回复设置',   'url' => U('Wifi/weixin')),
                                    ), 
                    ),*/
            '12' => array(
                    'name'      => '第三方接入',
                    'id'        => 'openapi',
                    'selected'  => MODULE_NAME == 'Openapi' ? 1 : 0,
                    'sub_menu'  => array(  
                                    '0' =>  array('selected'=> MODULE_NAME == 'Openapi' ? 1 : 0,'name' => '第三方接入设置',   'url' => U('Openapi/index')),
                                    ), 
                    ),       
            
            '4' => array(
                    'name'      => '行业解决方案',
                    'id'        => 'industry',
                    'selected'  => MODULE_NAME == 'Shop' || MODULE_NAME == 'Dining'|| MODULE_NAME == 'Hotel' || MODULE_NAME == 'Car' || MODULE_NAME == 'Wedding' || MODULE_NAME == 'Estate'? 1 : 0,
                    'sub_menu'  => array(
                                    '0' =>  array('selected'=> MODULE_NAME == 'Shop' ? 1 : 0, 'name' => '电子商务行业',       'url' => U('Shop/index') ),
                                    '1' =>  array('selected'=> MODULE_NAME == 'Dining' ? 1 : 0, 'name' => '餐饮服务行业',     'url' => U('Dining/index')),
                                    '2' =>  array('selected'=> MODULE_NAME == 'Hotel' ? 1 : 0, 'name' => '酒店宾馆行业',      'url' => U('Hotel/index') ),
                                    '3' =>  array('selected'=> MODULE_NAME == 'Wedding' ? 1 : 0, 'name' => '婚庆服务行业',    'url' => U('Wedding/index') ),
                                    '4' =>  array('selected'=> MODULE_NAME == 'Car' ? 1 : 0, 'name' => '汽车汽服行业',      'url' => U('Car/index') ),
                                    '5' =>  array('selected'=> MODULE_NAME == 'Estate' ? 1 : 0, 'name' => '房地产行业',       'url' => U('Estate/index') ),
                                
                                    ), 
                    ),
             '13' => array(
                    'name'      => '统计数据',
                    'id'        => 'statics',
                    'selected'  => MODULE_NAME == 'WeixinStatics' ? 1 : 0,
                    'sub_menu'  => array(  
                                    '0' =>  array('selected'=> MODULE_NAME == 'WeixinStatics' ? 1 : 0,'name' => '消息分析',   'url' => U('WeixinStatics/index'), 'status' => 'new'),
                                    ), 
                    ),  
            
        );
        return $side_menu;

    }
    
}

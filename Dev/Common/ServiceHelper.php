<?php

/* 
 * 该类用户获取指定token所有模块信息
 */
require_once(COMMON_PATH.'/LinkHelper.php');
class ServiceHelper {

	public static function getServices($token) 
    {

        $service_ary = array();

        //插入基础功能
        $opened_funcs = session('opened_funcs'); 
        $sys_basic_function = array(
            'shouye'    => '微网站',
            'huiyuanka' => '会员卡',
            'dingdan'   => '预约\报名\预定',
            'xiangce'   => '微相册',
            'panorama'  => '全景相册',
            'wifi'      => '微路由');
        $sys_basic_function_tags = array();
        Log::write("basic open funcs:".print_r($opened_funcs,true));
        foreach ($sys_basic_function as $key => $value) 
        {
            //若用户没开通该功能则不显示
            if(!empty($opened_funcs) && in_array($key ,$opened_funcs))
            { 
                array_push($sys_basic_function_tags, array('tag'=>$key, 'name'=>$value));
            }
        }
        if ($sys_basic_function_tags && count($sys_basic_function_tags) > 0) 
        {
            array_push($service_ary, array('name'=>'功能模块','functions' => $sys_basic_function_tags));
        }

        //插入行业功能
        $sys_industry_function = array(
            'shangcheng'    => '微商城',
            'canyin'        => '微餐饮',
            'car'           => '微汽车',);
        
        foreach ($sys_industry_function as $key => $value) 
        {
            $sys_industry_function_tags = array();
            if(!empty($opened_funcs) && in_array($key,$opened_funcs))
            {
                if ($key == 'car') 
                {
                    //汽车行业有多个入口
                    $sys_industry_function_tags = array(
                            array('tag'=>'car-car',      'name'=>'微汽车-所有车型'),
                            array('tag'=>'car-drive',    'name'=>'微汽车-预约试驾'),
                            array('tag'=>'car-maintain', 'name'=>'微汽车-预约保养'),
                            array('tag'=>'car-tools',    'name'=>'微汽车-实用工具'),
                            array('tag'=>'car-care',     'name'=>'微汽车-车主关怀'),
                            array('tag'=>'car-sales',    'name'=>'微汽车-销售服务'), 
                            );
                }
                else
                {
                    array_push($sys_industry_function_tags, array('tag'=>$key, 'name'=>$value));
                }
                array_push($service_ary, array('name'=>'行业-'.$value,'functions' => $sys_industry_function_tags));
                
            }
        }

        if(!empty($opened_funcs) && in_array('shouye' ,$opened_funcs))
        {
            $sys_web_function_tags = array();
            //获取微网站可显示栏目信息
            $classify_db     = D('Classify');
            $where['token']  = session('token');
            $where['status'] = 1;
            $where['linktype'] = 'articles';
            $classifies = $classify_db->where($where)->order('sorts desc')->select();
            foreach ($classifies as $key => $value) 
            {
                array_push($sys_web_function_tags, array('tag'=>"shouye-classify-".$value['id'], 'name'=>'栏目-'.$value['name']));
            }
            //获取微网站可显示文章信息
            $sql = 'select a.id,a.title,a.content,c.`name` as classfy_name '
                        .' from tp_article as a LEFT JOIN tp_classify as c on a.c_id = c.id '
                        ." where a.token='$token' and a.`status`=1 and a.linktype='articles' and c.`status`=1;";
            $Model = new Model();
            $articles = $Model->query($sql);
            foreach ($articles as $key => $value) 
            {
                array_push($sys_web_function_tags, array('tag'=>'shouye-article-'.$value['id'], 'name'=>'文章（'.$value['classfy_name'].'）-'.$value['title']));
            }

            if ($sys_web_function_tags && count($sys_web_function_tags) > 0) 
            {
                array_push($service_ary, array('name'=>'微网站内容','functions' => $sys_web_function_tags));
            }
        }

        // 插入互动功能
        $sys_activities = array('dazhuanpan','guaguaka','youhuiquan','toupiao','zajindan');
        $sys_activity_tags = array();
        foreach ($sys_activities as $act) 
        {
            if(!empty($opened_funcs) && !in_array($act, $opened_funcs))
                continue;
            $details;
            switch($act)
            {
                case 'dazhuanpan':
                    $details = LinkHelper::getLotteryList($token,1);
                    $name = '大转盘';
                    break;
                case 'guaguaka':
                    $details = LinkHelper::getLotteryList($token, 2);
                    $name = '刮刮卡';
                    break;
                case 'youhuiquan':
                    $details = LinkHelper::getLotteryList($token,3);
                    $name = '优惠券';
                    break;
                case 'zajindan':
                    $details = LinkHelper::getLotteryList($token,4);
                    $name = '砸金蛋';
                    break;
                case 'toupiao':
                    $details = LinkHelper::getVoteList($token); 
                    $name = '投票';
                    break;
            }

            if ($details != false && is_array($details) && count($details)>0) 
            {
            	foreach ($details as $key => $value) 
	            {
	                array_push($sys_activity_tags, array('tag'=>$act."--".$value['id'], 'name'=>$name.'-'.$value['keyword']));
	            }
            }
            

        }
        if ($sys_activity_tags && count($sys_activity_tags) > 0) 
        {
            array_push($service_ary, array('name'=>'互动营销','functions' => $sys_activity_tags));
        }

        //插入宾馆行业
        $hotels = LinkHelper::getHotels($token);
        $sys_hotel_tags = array();
        if ($hotels && is_array($hotels)) 
        {
            foreach ($hotels as $key => $value) 
            {
                $sys_hotel_tags[$key] = array('tag'=>'hotel'."--".$value['id'], 'name'=> '宾馆-'.$value['keyword']);
            }
        }
        if ($sys_hotel_tags && count($sys_hotel_tags) > 0) 
        {
            array_push($service_ary, array('name'=>'行业-微宾馆','functions' => $sys_hotel_tags));
        }
        
        Log::write("service_ary:".print_r($service_ary,true));
        return $service_ary;
    }
	
	
    public static function constructHyperLink($token, $function, $module='', $activity='') 
    {
        $param_ary = array('token' => $token, 'wxref' => 'mp.weixin.qq.com');
        switch($function)
        {
            case 'shangcheng':
                return  'http://'.C('wx_handler_server').U('Wap/Shop/index', $param_ary);
                break;
            case 'canyin':
                return  'http://'.C('wx_handler_server').U('Wap/Dining/index', $param_ary);
                break;
            case 'hotel':
                $param_ary['hid'] = $activity;
                return  'http://'.C('wx_handler_server').U('Wap/Hotel/index', $param_ary);
                break;
            case 'panorama':
                return 'http://'.C('wx_handler_server').'/index.php/3d/g/'.$token;
                break;
            case 'xiangce':
                return 'http://'.C('wx_handler_server').'/index.php/photo/'.$token;
                break;
             case 'huiyuanka':
                return "http://".C('wx_handler_server').U('Wap/Card/vip', $param_ary);
                break;
            case 'dingdan':
                $param_ary['hid'] = $activity;
                return  'http://'.C('wx_handler_server').U('Wap/Host/index', $param_ary);
                break;
            case 'dazhuanpan':
                $param_ary['id'] = $activity;
                return  'http://'.C('wx_handler_server').U('Wap/Lottery/index', $param_ary);
                break;
            case 'zajindan':
                $param_ary['id'] = $activity;
                return  'http://'.C('wx_handler_server').U('Wap/Golden/index', $param_ary);
                break;
            case 'guaguaka':
                $param_ary['id'] = $activity;
                return  'http://'.C('wx_handler_server').U('Wap/Guajiang/index', $param_ary);
                break;
            case 'youhuiquan':
                $param_ary['id'] = $activity;
                return  'http://'.C('wx_handler_server').U('Wap/Coupon/index', $param_ary);
                break;
            case 'toupiao':
                $param_ary['id'] = $activity;
                return  'http://'.C('wx_handler_server').U('Person/Vote/showVote', $param_ary);
                break;
            case 'shouye':
                switch($module)
                {
                    case 'classify':
                        $param_ary['classid'] = $activity;
                         return 'http://'.C('wx_handler_server').U('Wap/Index/lists', $param_ary); 
                         break;
                    case 'article':
                        $param_ary['id'] = $activity;
                        return  'http://'.C('wx_handler_server').U('Wap/Index/content', $param_ary);
                        break;
                    default:
                        return  'http://'.C('wx_handler_server').U('Wap/Index/index', $param_ary);
                }
                break;
            case 'car':
                switch($module)
                {
                    case 'index':
                         return  'http://'.C('wx_handler_server').U('Wap/Car/index', $param_ary);
                         break;
                     case 'drive':
                         return  'http://'.C('wx_handler_server').U('Wap/Car/rdrive', $param_ary);
                         break;
                     case 'maintain':
                         return  'http://'.C('wx_handler_server').U('Wap/Car/rmaintain', $param_ary);
                         break;
                     case 'tools':
                         return  'http://'.C('wx_handler_server').U('Wap/Car/tools', $param_ary);
                         break;
                     case 'care':
                         return  'http://'.C('wx_handler_server').U('Wap/Car/care', $param_ary);
                         break;
                     case 'sales':
                         return  'http://'.C('wx_handler_server').U('Wap/Car/sales', $param_ary);
                         break;
                }
                break;
            default:
                break;
        }
    }
}
?>
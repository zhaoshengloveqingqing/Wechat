<?php

/* 
 * 该类将所有于模块链接相关的公共部分封装，方便修改和服用。
 */
class LinkHelper {
    public static function constructHyperLink($token, $wecha_id, $linktype, $link_param_l1, $link_param_l2) {
        $wxref = 'mp.weixin.qq.com';
        switch($linktype){
            case 'modules':
                switch($link_param_l1){
                case 'shangcheng':
                     return  U('Wap/Shop/index',
                             array(
                                'token'     => $token,
                                'wxref'     => $wxref,
                                'wecha_id'  => $wecha_id
                             ));

                    break;
                case 'canyin':
                     return  U('Wap/Dining/rest',
                             array(
                                'token'     => $token,
                                'wxref'     => $wxref,
                                'wecha_id'  => $wecha_id
                             ));

                    break;
                case 'hotel':
                     return  U('Wap/Hotel/index',
                             array(
                                'token'     => $token,
                                'wxref'     => $wxref,
                                'wecha_id'  => $wecha_id,
                                'hid'       => $link_param_l2
                             ));

                    break;
                case 'panorama':
                    return '/index.php/3d/g/'.$token;
                    break;
                case 'xiangce':
                    return '/index.php/photo/'.$token;
                    break;
                case 'shouye':
                    return '/index.php?g=Wap&m=Index&a=index&token='.$token.'&wecha_id='.$wecha_id.'&wxref='.$wxref;
                    break;
                case 'huiyuanka':
                    $user_member_card_id = M('member_card_create')->where(array('wecha_id'=>$wecha_id,'status'=>1))->find();
                    $url =  "/index.php?g=Wap&m=Card&a=vip&token=".$token.'&wecha_id='.$wecha_id.'&wxref='.$wxref;
                    if ($user_member_card_id == false) 
                    {
                        //还没领取过会员卡
                        $url =  "/index.php?g=Wap&m=Card&a=get_card&token=".$token.'&wecha_id='.$wecha_id.'&wxref='.$wxref;
                    }
                    return $url;
                    break;
                case 'dingdan':
                    return "/index.php?g=Wap&m=Host&a=index&token=".$token.'&wecha_id='.$wecha_id.'&hid='.$link_param_l2.'&wxref='.$wxref;
                    break;
                
                case 'pinglun':
                    return "/index.php?g=Wap&m=Reply&a=index&token=".$token.'&id='.$link_param_l2.'&wecha_id='.$wecha_id.'&wxref='.$wxref;

                    break;
                case 'yingxiang':
                    return "/index.php?g=Wap&m=Impress&a=index&token=".$token.'&id='.$link_param_l2.'&wecha_id='.$wecha_id.'&wxref='.$wxref;

                    break;
                default:
                    return null;
                    break;
                }
                break;

            case 'activitys':
                switch($link_param_l1) {
                case 'dazhuanpan':
                    return "/index.php?g=Wap&m=Lottery&a=index&token=".$token.'&id='.$link_param_l2.'&wecha_id='.$wecha_id.'&wxref='.$wxref; 
                    break;
                case 'guaguaka':
                    return "/index.php?g=Wap&m=Guajiang&a=index&token=".$token.'&id='.$link_param_l2.'&wecha_id='.$wecha_id.'&wxref='.$wxref;
                    break;
                case 'youhuiquan':
                    return "/index.php?g=Wap&m=Coupon&a=index&token=".$token.'&id='.$link_param_l2.'&wecha_id='.$wecha_id.'&wxref='.$wxref;
                    break;
                case 'zajindan':
                    return "/index.php?g=Wap&m=Golden&a=index&token=".$token.'&id='.$link_param_l2.'&wecha_id='.$wecha_id.'&wxref='.$wxref; 
                    break;
                case 'toupiao':
                    return "/index.php?g=Person&m=Vote&a=showVote&token=".$token.'&id='.$link_param_l2.'&wecha_id='.$wecha_id.'&wxref='.$wxref;
                    break;
         
                default:
                    break;
                }
                break;

            case 'linkurls':
                return $link_param_l1;
                break;

            case 'car':
                switch($link_param_l1){
                    case 'index':
                         return  U('Wap/Car/index',
                                 array(
                                    'token'     => $token,
                                    'wxref'     => $wxref,
                                    'wecha_id'  => $wecha_id
                                 ));

                         break;
                     case 'drive':
                         return  U('Wap/Car/rdrive',
                                 array(
                                    'token'     => $token,
                                    'wxref'     => $wxref,
                                    'wecha_id'  => $wecha_id
                                 ));

                         break;
                     case 'maintain':
                         return  U('Wap/Car/rmaintain',
                                 array(
                                    'token'     => $token,
                                    'wxref'     => $wxref,
                                    'wecha_id'  => $wecha_id
                                 ));

                         break;
                     case 'tools':
                         return  U('Wap/Car/tools',
                                 array(
                                    'token'     => $token,
                                    'wxref'     => $wxref,
                                    'wecha_id'  => $wecha_id
                                 ));

                         break;
                     case 'care':
                         return  U('Wap/Car/care',
                                 array(
                                    'token'     => $token,
                                    'wxref'     => $wxref,
                                    'wecha_id'  => $wecha_id
                                 ));

                         break;
                     case 'sales':
                         return  U('Wap/Car/sales',
                                 array(
                                    'token'     => $token,
                                    'wxref'     => $wxref,
                                    'wecha_id'  => $wecha_id
                                 ));

                         break;
                
                }
                break;
            default:
                return null;
                break;
        }
    }
    
    
    
    // 栏目的链接类型: 如果你的应用需要额外的链接类型，请在自己的类里调用该方法并追加。尽量保证该方法的可复用。
    // follow: /Dev/Lib/Action/User/ClassifyAction.class.php
    public static function getCommonLinkTypes() {
        return array(
            array('id'=>'modules', 'name'=>'功能模块'),
            array('id'=>'activitys', 'name'=>'互动营销'),
            array('id'=>'linkurls', 'name'=>'外链地址'),
            array('id'=>'car', 'name'=>'微汽车'),
        );
    }



    public static function getModules() {
        return array(
            array('id'=>'shangcheng',   'name'=>'微商城',      'hasSub'=>'0'),
            array('id'=>'panorama',     'name'=>'全景相册',    'hasSub'=>'0'),
            array('id'=>'xiangce',      'name'=>'微相册',      'hasSub'=>'0'),
            array('id'=>'shouye',       'name'=>'微网站',      'hasSub'=>'0'),
            array('id'=>'huiyuanka',    'name'=>'会员卡',      'hasSub'=>'0'),
            array('id'=>'dingdan',      'name'=>'预约/报名/预定', 'hasSub'=>'1'),
            array('id'=>'canyin',       'name'=>'微餐饮',      'hasSub'=>'0'),
            array('id'=>'hotel',        'name'=>'微宾馆',      'hasSub'=>'1'),
            array('id'=>'pinglun',      'name'=>'微评论',      'hasSub'=>'1'),
            array('id'=>'yingxiang',    'name'=>'微印象',      'hasSub'=>'1'),
            );
    }
    
    public static function getCarModules() {
        return array(
            array('id' =>'index',   'name'=>'所有车型',      'hasSub'=>'0'),
            array('id' =>'drive',     'name'=>'预约试驾',    'hasSub'=>'0'),
            array('id' =>'maintain',      'name'=>'预约保养',      'hasSub'=>'0'),
            array('id' =>'tools',       'name'=>'实用工具',      'hasSub'=>'0'),
            array('id' =>'care',    'name'=>'车主关怀',      'hasSub'=>'0'),
            array('id' =>'sales',      'name'=>'销售服务', 'hasSub'=>'0'),
            );
    }

    public static function getActivities() {
        return array(
            array('id'=>'dazhuanpan', 'name'=>'幸运大转盘', 'hasSub'=>'1'),
            array('id'=>'guaguaka', 'name'=>'刮刮卡', 'hasSub'=>'1'),
            array('id'=>'youhuiquan', 'name'=>'优惠券', 'hasSub'=>'1'),
            array('id'=>'zajindan', 'name'=>'砸金蛋', 'hasSub'=>'1'),
            array('id'=>'toupiao', 'name'=>'微投票', 'hasSub'=>'1'),

            );
    }

    // 帮助函数： 将(id, name, xxxx)之类的数组变成ID=>(id,name, xxx)字典
    public static function buildId2RecordMap($param) {
        $tmp = array();
        foreach($param as $item) {
            $tmp[$item['id']] = $item;
        }
        
        return $tmp;
    }
    public static function getOrders($token) 
    {
        $orders = M('host')
                ->where(array('token' => $token, 'status'=>1))
                ->field('id, title, keyword,  FROM_UNIXTIME(creattime) starttime, "-" endtime')
                ->select();

        return $orders;
    }

    public static function getHotels($token) 
    {
        $orders = M('Hotel')
                ->where(array('token' => $token, 'status'=>1))
                ->field('id, title, keyword,  FROM_UNIXTIME(creattime) starttime, "-" endtime')
                ->select();

        return $orders;
    }
    
    public static function getVoteList($token) 
    {
        $now = date("Y-m-d");

        $vote = M('Vote') -> where(
                array(
                    'token' => $token, 
                    'status' => 1
                    )
                )
                ->field('id, title, keyword, starttime, endtime')
                ->select();

        return $vote;
    }
    
    public static function getLotteryList($token, $type)
    {
        $lottery = M('Lottery')
                -> where(array('token' => $token, 'status' => 1, 'type'=>$type))
                ->field('id, title, keyword, FROM_UNIXTIME(statdate) starttime, FROM_UNIXTIME(enddate) endtime')
                ->select();

        return $lottery;
    }
    
    public static function getReplyList($token) {
        $res = M('reply')
                ->where(array('token'=>$token, 'status'=>1))
                ->field('id, title, keyword,"-" starttime, "-" endtime')
                ->select();
        return $res;
    }
    
    public static function getImpressList($token) {
        $res = M('impress')
                ->where(array('token'=>$token, 'status'=>1))
                ->field('id, title, keyword,"-" starttime, "-" endtime')
                ->select();
        return $res;
    }

}

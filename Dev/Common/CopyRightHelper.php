<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CopyRightHelper {
    private static $CopyRightCustomizationFGId = -1;
    static function loadCopyRightCustomizationFgId(){
        $confItem = C('COPYRIGHT_CUSTOMIZATION_FUNC_GROUP');
        if(!empty($confItem) && self::$CopyRightCustomizationFGId < 0) {
            $res = M('function_group')->where(array('name'=>C('COPYRIGHT_CUSTOMIZATION_FUNC_GROUP'), 'status'=>'1'))->find();
            if($res) {
                self::$CopyRightCustomizationFGId = $res['id'];
            }
        }
    }
    
    public static function generateCopyRight($token) {
        $copyRightText = '';
        $wxuser = M('wxuser')->where(array('token' => $token, 'status'=>'1'))->field('company')->find();
        if(!empty($wxuser)) {
            $copyRightText = $wxuser['company'];
        }
        
        return self::generateCopyRightEx($token, $copyRightText);
    }
    
    public static function generateShopCopyRight($token) {
        $copyRightText = '';
        $shop = M('b2c_shop')->where(array('token' => $token, 'status' => 1))->field('name')->find();
        if(!empty($shop)) {
            $copyRightText = $shop['name'];
        }
        
        return self::generateCopyRightEx($token, $copyRightText);
    }
    
    private static function generateCopyRightEx($token, $copyRightText) {
        // 如果目标公共账号已经开通版权自定义功能， 则使用商户的版权，否则使用领众的版权信息
        $sql = "select ac.cfg_data, ag.role from tp_wxuser as wx left join tp_users as u on wx.uid=u.id left join tp_user as ag on u.administrator = ag.id LEFT JOIN tp_oem_cfg as ac on u.administrator = ac.agent_id where wx.token='$token';";
        $Model = new Model();
        $cfg = $Model->query($sql);

        $defaultCopyRightText = '';
        $defaultCopyRightLink = C('copyright_weixin_link');
        if ($cfg != false && $cfg[0]['role'] == 18) 
        {
            $agent_cfg = unserialize($cfg[0]['cfg_data']); 

            if (!empty($agent_cfg['company_short_name'])) 
            {
                $defaultCopyRightText = $agent_cfg['company_short_name'].'技术支持'; 
            }
            
            if (empty($agent_cfg['copyright_link'])) 
            {
                $defaultCopyRightLink = '#';
            }
            else
            {
                $defaultCopyRightLink = $agent_cfg['copyright_link'];
            }
        } 
        else
        {
            $defaultCopyRightText = C('copyright_weixin_text');
        }

        
        $hasCustomizePriviledge = false;
        if(!empty($token)) {
            self::loadCopyRightCustomizationFgId();
            // 检查目标商户是否已经开通了版权自定义权限
            $res = M('wxuser')
                    ->join('inner join tp_users on tp_wxuser.uid = tp_users.id')
                    ->join('inner join tp_user_func_group on tp_users.id = tp_user_func_group.user_id')
                    ->where(
                            array(
                                'tp_wxuser.token' => $token,
                                'tp_user_func_group.group_id' => self::$CopyRightCustomizationFGId,
                                'tp_user_func_group.status' => '1',
                                'tp_user_func_group.expire_time' => array('gt', time())
                                )
                            )
                    ->field('tp_wxuser.company company')
                    ->find();
            if($res) {
                // 该商户拥有自定义版权的权限
                $hasCustomizePriviledge = true;
            }
        }
        
        $adv = '';
        if(!$hasCustomizePriviledge) {
            $adv = '<div class="tech-support">'.
                        '<a href="'.$defaultCopyRightLink.'">'.$defaultCopyRightText.'</a> '.
                   '</div>';
        };
        
        $copyrightDiv = '';
        if(!empty($copyRightText)) {
            $copyrightDiv = '<div class="copyright">'.'©'.date('Y').' '.$copyRightText.'</div>';
        }
        return 
                $copyrightDiv.$adv;
    }

}
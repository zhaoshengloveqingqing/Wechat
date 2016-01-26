<?php
class MemberAction extends ManageAction
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    public function points()
    {
        parent::checkAction("Member-points");
        // 消费记录，且未删除  tp_member_card_sign` as point | tp_member_card_create as card
        $where = " WHERE ( point.token = '$this->token' ) "
                        ."AND ( card.token = '$this->token' )"
						."AND ( card.status = 1 )"
                        ."AND ( point.score_type = 2 ) "
                        ."AND ( point.delete = 0 )";
        if (IS_POST)
        {     
            $key = $this->_post('searchkey');
            if ($key)
            {
                $where .= " AND (card.number like '%$key%')";
            }
        } 

        $sql = "SELECT point.id as id,point.wecha_id as uid,card.number as cardnum,point.expense as expense,point.sell_expense as sell_expense,point.sign_time, point.remark "
                    ." FROM `tp_member_card_sign` as point "
					." JOIN  tp_member_card_create as card on point.wecha_id=card.wecha_id ";
        $sql .= $where;
        $sql .= ' ORDER BY sign_time desc ';
        $Model = new Model();
        $points = $Model->query($sql);
              
        $date = date("Y-m-d");
        $this->assign('points',$points);
        $this->assign('date',$date);
        
        $this->display();
    }

    public function addExchanges()
    {
        parent::checkAction("Member-points");
        //积分兑换记录
        // 检查卡号
        $cardnum = $this->_post('number');
        $where = array('token'=>$this->token,'number'=>$cardnum,'status'=>1);
        $where['wecha_id'] = array('neq', '');
        $card = M('Member_card_create')->where($where)->find();
        if (!$card) 
        {
            $this->error('卡号不存在');
        }

        $wecha_id = $card['wecha_id'];

        $userinfo = M('Userinfo')->where(array('token'=>$this->token,'wecha_id'=>$wecha_id,'status'=>1))->find();
            
        $add_expend     = floatval($this->_post('cost_score'));
        $add_expend_time= $this->_post('cost_date');
        $remark         = $this->_post('comment');

        if ($add_expend <= 0)
        {
            $this->error('兑换积分必须大于0');
        }

        if ($userinfo['total_score'] < $add_expend)
        {
            $this->error('积分余额不足');
        }

            
        $data['token']      = $this->token;
        $data['wecha_id']   = $wecha_id;
        $data['sign_time']  = strtotime($add_expend_time);
        $data['score_type'] = 3;
        $data['delete']     = 0;
        $data['expense']    = intval($add_expend);
        $data['remark']     = $remark; //备注
        $back = M('Member_card_sign')->data($data)->add();

        //总记录
        $da['total_score']  = $userinfo['total_score'] - $data['expense'];
        $da['spend_score']  = $userinfo['spend_score'] + $data['expense'];

        $back2 = M('Userinfo')->where(array('token'=>$this->token,'wecha_id'=>$wecha_id ,'status'=>1))->save($da);
        if($back && $back2)
        {
            $this->success('操作成功');
        }
        else
        {
            $this->error('服务器繁忙，请稍候再试');
        } 
    }
    
    public function exchanges()
    {
        parent::checkAction("Member-points");
        // 消费记录，且未删除  tp_member_card_create as card | tp_userinfo info 
        $where = " WHERE ( point.token = '$this->token' ) "
                        ."AND ( card.token = '$this->token') "
						."AND ( card.status = 1 ) "
						."AND ( info.status = 1 )"
                        ."AND ( info.token = '$this->token') "
                        ."AND ( point.score_type = 3 ) "
                        ."AND ( point.delete = 0 )";
        if (IS_POST)
        {     
            $key = $this->_post('searchkey');
            if ($key)
            {
                $where .= " AND (card.number like '%$key%')";
            }
        } 

        $sql = "SELECT info.total_score as total, point.id as id,point.wecha_id as uid,card.number as cardnum,point.expense as expense,point.sell_expense as sell_expense,point.sign_time, point.remark  "
                ." FROM `tp_member_card_sign` as point "
                        ."JOIN tp_member_card_create as card on point.wecha_id=card.wecha_id "
                        ."JOIN tp_userinfo info on card.wecha_id=info.wecha_id ";
        $sql .= $where;
        $sql .= ' ORDER BY sign_time desc ';
        $Model = new Model();
        $list = $Model->query($sql);
        $this->assign('exchanges',$list);
        
        $this->display();
    }

    public function delExchanges()
    {
        parent::checkAction("Member-points");
        $data['id']     = $this->_get('id');
        $data['token']  = $this->token;
		$data['status']  = 1;
        $data['score_type']  = 3;
        if(empty($data['id']))
        {
            Log::record('未收到post过来的id');
            $this->error('出错啦，系统已经记录');
        }
        
        $record = M('Member_card_sign')->where($data)->find();

        if ($record) 
        {
            $re = M('Member_card_sign')->where($data)->setField('delete', 1);
        
            // 删除userinfo里面的总积分中对应部分，可能会存在多线程不安全，不过对于并发小的情况下，概率不大
            $userinfo = M('Userinfo')->where(array('token'=>$this->token,'wecha_id'=>$record['wecha_id'],'status'=>1))->find();
    
            $da['total_score']  = $userinfo['total_score'] + $record['expense'];
            $da['spend_score']  = $userinfo['spend_score'] - $record['expense'];
        
            $back2 = M('Userinfo')->where(array('token'=>$this->token,'wecha_id'=>$record['wecha_id'],'status'=>1))->save($da);

            if (!$re || !$back2) 
            {
                $this->error('服务器繁忙，请稍候再试');
            } 
            else 
            {
                $this->success('操作成功');
            } 
        }
        else 
        {
            $this->error('记录不存在');
        } 
        
    }

    public function users()
    {
        parent::checkAction("Member-users");
		//tp_member_card_create as card | tp_wecha_user as wu | tp_userinfo as member
        $where = " WHERE ( member.token = '$this->token' ) "
				 ." AND (member.status = 1)"
				 ." AND ( wu.token = '$this->token' ) "
				 ." AND (card.status = 1)"
				 ." And (card.token= '$this->token' )";

        if (IS_POST)
        {
            $key = $this->_post('searchkey');
            if ($key)
            {
                $where .= " AND ( (wu.tel LIKE '%$key%') OR (wu.nickname LIKE '%$key%') )";
            }
        } 
        else 
        {
            $uid     = $this->_get("uid");
            if ($uid) 
            {
                $where .= " AND ( member.wecha_id= '$uid')";
            }
        }

        $sql = 'SELECT card.number as number, wu.nickname as nickname,wu.truename as truename,wu.tel as tel,wu.birthday as birthday,wu.address as address,wu.sex as sex,member.sign_score as sign_score,member.expend_score as expend_score ,member.total_score as total_score,member.spend_score as spend_score '
                  .' FROM `tp_userinfo` as member '
				  .' LEFT JOIN tp_wecha_user as wu on wu.wecha_id = member.wecha_id '
				  .' left join tp_member_card_create as card on card.wecha_id = member.wecha_id '
                  .$where;
                 
        $Model = new Model();
        $members = $Model->query($sql);
        $this->assign('members',$members);

        $this->display();    
    }
    
    
    public function del()
    {
        parent::checkAction("Member-points");
        //删除消费积分
        $data['id']     = $this->_get('id');
        $data['token']  = $this->token;
        $data['score_type']  = 2;

        $point_db = M('Member_card_sign');

        $ret_point;
        $ret_total;
		$element = $point_db->where($data)->find();
        if ($element) 
        {
            $ret_point = $point_db->where($data)->setField('delete', 1);
            if ($ret_point) 
            {
                $user_db = M('Userinfo');
                // 删除userinfo里面的总积分中对应部分，可能会存在多线程不安全，不过对于并发小的情况下，概率不大
                $userinfo = $user_db->where(array('token'=>$data['token'],'wecha_id'=>$element['wecha_id'],'status'=>1))->find();
                
                $da['total_score']   = $userinfo['total_score'] - $element['expense'];
                $da['expend_score']  = $userinfo['expend_score'] - $element['expense'];
                
                $ret_total = $user_db->where(array('token'=>$data['token'],'wecha_id'=>$element['wecha_id'],'status'=>1))->save($da);
            }
        }
		
        if (!$ret_point || !$ret_total) 
        {
            $this->error('服务器繁忙，请稍候再试');
        } 
        else 
        {
            $this->success('操作成功');
        } 
    }

    //------------------------------------------
    // 添加消费积分记录
    //------------------------------------------

    public function addsell()
    {
        parent::checkAction("Member-points");
        if (!IS_POST)
        {
            $this->error('没有提交任何东西');exit;    
        }

        // 检查卡号
        $cardnum = $this->_post('cardnum');
        $card = M('Member_card_create')->where(array('token'=>$this->token,'number'=>$cardnum,'status'=>1))->find();
        if (!$card) 
        {
            $this->error('卡号不存在');
        }

        if (!$card['wecha_id']) 
        {
            $this->error('改卡号未被领用，无法消费');
        }
        
        $wecha_id = $card['wecha_id'];
        $add_expend     = floatval($this->_post('add_expend'));
        $add_expend_time= $this->_post('add_expend_time');
        $remark         = $this->_post('remark');
        
        if($add_expend <= 0)
        {
            $this->error('消费金额必须大于0元');exit;    
        }

        $user_db = M('Userinfo');

        //获取商家消费积分设置 tp_member_card_exchange
        $card_setting = M('Member_card_exchange')->where(array('token'=>$this->token))->find();
        $ratio = 1;  //default ratio
        if ($card_setting && $card_setting['reward']) 
        {
            $ratio = $card_setting['reward'];
        }

        $data['token']    = $this->token;
        $data['wecha_id'] = $wecha_id;
        $data['sign_time'] = strtotime($add_expend_time);
        $data['score_type'] = 2;
        $data['delete'] = 0;
        $data['expense']  = intval($add_expend) * $ratio;
        $data['sell_expense'] = $add_expend; //消费金额
        $data['remark'] = $remark; //备注
        //添加消费记录
        $back = M('Member_card_sign')->data($data)->add();

        //更新消费总记录
        // 积分 = 消费总金额 * 积分比例$ratio
        $userinfo = $user_db->where(array('token'=>$this->token,'wecha_id'=>$wecha_id,'status'=>1))->find();
        $back2 = false;
        if ($userinfo) 
        {
            $da['total_score']   = $userinfo['total_score'] +  $data['expense'];
            $da['expend_score']  = $userinfo['expend_score'] + $data['expense'];
            $back2 = $user_db->where(array('token'=>$this->token,'wecha_id'=>$wecha_id,'status'=>1))->save($da);
        }
        
        if ($back && $back2)
        {
            $this->success('操作成功');
        }
        else
        {
            $this->error('服务器繁忙，请稍候再试');
        } 
    }
}
?>

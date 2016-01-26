<?php
class WallAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'xianchang';
        parent::checkOpenedFunction();
    }

    public function index()
    {
        $data = M('Wall');
        $count      = $data->where(array('token'=>$this->token, 'status'=>array("neq", 0)))->count();
        $Page       = new Page($count,15);
        $show       = $Page->show();
        $list = $data->where(array('token'=>$this->token, 'status'=>array("neq", 0)))->order("id")->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->display();
    }
    
    public function set()
    {
        $id     = $this->_get('id'); 
        $where  = array('token'=>$this->token,'id'=>$id, 'status' => 1);
        $wall   = M('Wall')->where($where)->find();
        if (empty($wall))
        {
            $this->error("没有微现场.您现在可以添加.",U('Wall/add'));
        }

        if(IS_POST)
        {
            $keyword        = $this->_post('keyword','trim');
            $name_prefix    = $this->_post('name_prefix','trim');
            $lot_prefix     = $this->_post('lot_prefix','trim');

            $data['keyword']        = $keyword;
            $data['name_prefix']    = $name_prefix;
            $data['lot_prefix']     = $lot_prefix;
            $data['title']          = $this->_post('title');
            $data['start_time']     = $this->_post('start_time');
            $data['end_time']       = $this->_post('end_time');
            $data['name']           = $this->_post('name');
            $data['logo']           = $this->_post('logo'); 
            $data['qrcode_url']     = $this->_post('qrcode_url'); 
            $data['backgroud_pic_url']     = $this->_post('backgroud_pic_url'); 
            $data['music_url']      = $this->_post('music_url');
            $data['description']    = $this->_post('description'); 
            
            $lotterys = array();
            $lottery_name   = $_POST['lottery_name'];
            $lottery_value  = $_POST['lottery_value'];
            $lottery_num    = $_POST['lottery_num'];
            $lottery_img    = $_POST['lottery_img'];
            for($i = 0; $i<count($lottery_name); $i ++) {
                if(!empty($lottery_name[$i])) {
                    array_push($lotterys, array($lottery_name[$i], $lottery_value[$i], $lottery_num[$i], $lottery_img[$i]));
                }
            }
            $data['lotterys'] = serialize($lotterys);
            
            $ret = M('Wall')->where($where)->save($data);

            if($ret)
            {
                $kwds_db = M('keyword');
                $kwd_where['token']    = $this->token;
                $kwd_where['function'] = $this->function;
                $kwd_where['pid']      = $id;
                $kwds_db->where($kwd_where)->delete();

                $da['token']    = $this->token;
                $da['function'] = $this->function;
                $da['pid']      = $id;
                $da['type']     = 2; //模糊匹配
                //更新消息前缀
                $da['module']   = 'wall';
                $da['keyword']  = $keyword;
                $kwds_db->add($da);
                //更新昵称前缀
                $da['module']   = 'nickname';
                $da['keyword']  = $name_prefix;
                $kwds_db->add($da);
                //更新兑奖提示
                $da['module']   = 'lottery';
                $da['keyword']  = $lot_prefix;
                $kwds_db->add($da);
                $this->success('修改成功',U('Wall/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Wall/index'));
            }
        } else { 
            if(!empty($wall)) {
                $lotterys = unserialize($wall['lotterys']);
                $this->assign('lotterys', $lotterys);
            }
            $this->assign('set',$wall);
            $this->display();  
        }
    }
    
    public function add()
    { 
        if (IS_POST)
        {
            $keyword        = $this->_post('keyword','trim');
            $name_prefix    = $this->_post('name_prefix','trim');
            $lot_prefix     = $this->_post('lot_prefix','trim');

            $data['keyword']    = $keyword;
            $data['name_prefix']= $name_prefix;
            $data['lot_prefix'] = $lot_prefix;
            $data['title']      = $this->_post('title');
            $data['start_time'] = $this->_post('start_time');
            $data['end_time']   = $this->_post('end_time');
            $data['name']       = $this->_post('name');
            $data['logo']       = $this->_post('logo'); 
            $data['qrcode_url']     = $this->_post('qrcode_url'); 
            $data['backgroud_pic_url']     = $this->_post('backgroud_pic_url'); 
            $data['music_url']     = $this->_post('music_url');
            $data['description']      = $this->_post('description'); 
            
            $lotterys = array();
            $lottery_name = $_POST['lottery_name'];
            $lottery_value = $_POST['lottery_value'];
            $lottery_num = $_POST['lottery_num'];
            for($i = 0; $i<count($lottery_name); $i ++) {
                if(!empty($lottery_name[$i])) {
                    array_push($lotterys, array($lottery_name[$i], $lottery_value[$i], $lottery_num[$i]));
                }
            }
            $data['lotterys'] = serialize($lotterys);
            
            $data['token']      = $this->token;
            $data['creattime']  = time();
            $data['status']  = 1;

            $wall_id = M('wall')->data($data)->add();
            if($wall_id)
            {
                $kwds_db = M('keyword');
                $kwd_where['token']    = $this->token;
                $kwd_where['function'] = $this->function;
                $kwd_where['pid']      = $wall_id;

                $kwds_db->where($kwd_where)->delete();
                $da['token']    = $this->token;
                $da['function'] = $this->function;
                $da['pid']      = $wall_id;
                $da['type']     = 2;
                //更新消息前缀
                $da['module']   = 'wall';
                $da['keyword']  = $keyword;
                $kwds_db->add($da);
                //更新昵称前缀
                $da['module']   = 'nickname';
                $da['keyword']  = $name_prefix;
                $kwds_db->add($da);
                //更新兑奖提示
                $da['module']   = 'lottery';
                $da['keyword']  = $lot_prefix;
                $kwds_db->add($da);
            }
           $this->success('修改成功',U('Wall/index'));
        }
        else
        {
            $this->display('set');
        }
    }
    
    public function del()
    {
        $id = $this->_get('id');

        if(IS_GET)
        {                              
            $where  = array('id'=>$id,'token'=>$this->token, 'status'=>array('neq', 0));
            $back=M('Wall')->where($where)->save(array('status'=>0));
            if($back==true)
            {
                M('Keyword')->where(array('pid'=>$id,'token'=>$this->token, 'function'=> $this->function))->delete();
                $this->success('操作成功',U('Wall/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Wall/index'));
            }
        }        
    }
    
    public function stop()
    {
        $id = $this->_get('id');

        if(IS_GET)
        {                              
            $where  = array('id'=>$id,'token'=>$this->token, 'status'=>1);
            $back=M('Wall')->where($where)->save(array('status'=>2));
            if($back==true)
            {
                M('Keyword')->where(array('pid'=>$id,'token'=>$this->token, 'function'=> $this->function))->delete();
                $this->success('操作成功',U('Wall/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Wall/index'));
            }
        }        
    }
    
    public function reply()
    {
        $id = $this->_get('id'); 

        $reply_db   = M('wall_reply');
        $count      = $reply_db->where(array('wall_id'=>$id, "token"=>$this->token))->count();
        $Page       = new Page($count,30);
        $show       = $Page->show();
        $this->assign('page',$show);

        $sql = " SELECT r.id,r.createtime,r.token,r.wall_id,r.text,r.`status`,r.wecha_id,u.nickname,u.userinfo, u.headimgurl "
                    ." FROM `tp_wall_reply` as r LEFT JOIN tp_wecha_user as u ON r.wecha_id = u.wecha_id AND r.token = u.token " 
                    ." WHERE ( `wall_id` = $id ) AND ( r.token = '$this->token' )     ORDER BY r.createtime desc";
        $sql .= " limit ".$Page->firstRow.','.$Page->listRows;
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $list = $Model->query($sql);
        foreach ($list as $key => $value){
        	$list[$key]['text'] =  $value['text'];
    		if (empty($list[$key]['nickname'])){
                $list[$key]['nickname'] = '匿名';
            }
            if (empty($list[$key]['headimgurl'])){
                $list[$key]['headimgurl'] = '/themes/a/images/logo.jpg';
            }
        }
        $this->assign('list',$list);
        $this->assign('wid',$id);
        $this->display();
    }
    
    public function winner()
    {
        $id = $this->_get('id'); 
        $winner_db  = M('wall_winner');
        $count      = $winner_db->where(array('wall_id'=>$id, "token"=>$this->token))->count();
        $Page       = new Page($count,30);
        $show       = $Page->show();
        $this->assign('page',$show);

        $sql = "SELECT w.id,w.lottery,w.token,w.wall_id,w.wecha_id,u.nickname,u.headimgurl,w.`status` "
                ." FROM `tp_wall_winner` as w LEFT JOIN tp_wecha_user as u on w.wecha_id = u.wecha_id AND w.token = u.token "
                ." WHERE ( `wall_id` = $id ) AND ( w.token = '$this->token' ) ORDER BY w.id ";
        $sql .= " limit ".$Page->firstRow.','.$Page->listRows;
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $list = $Model->query($sql);
        
        $this->assign('list',$list);
        $this->assign('wid',$id);
        $this->display();
    }
    
    public function onwinner()
    {
        $id = $this->_post('id');
        $wid = $this->_post('wid');
        
        if(IS_POST)
        {                              
            $back = M('wall_winner')->where(array('id'=>$id,'token'=>$this->token))->save(array('status'=>2));
            if($back==true)
            {
                $this->ajaxReturn("", "OK", 1);
            }
            else
            {
                $this->ajaxReturn("", "服务器繁忙,请稍后再试", 0);
            }
        }        
    }
    
    public function onwall()
    {
        $id     = $this->_post('id');
        $wid    = $this->_post('wid');
        
        if(IS_POST)
        {                              
            $back = M('wall_reply')->where(array('id'=>$id,'token'=>$this->token))->save(array('status'=>2));
            if($back==true)
            {
                $this->ajaxReturn("", "OK", 1);
            }
            else
            {
                $this->ajaxReturn("", "服务器繁忙,请稍后再试", 0);
            }
        }        
    }
    
    public function delreply()
    {
        $id = $this->_post('id');
        $wid = $this->_post('wid');
        
        if(IS_POST)
        {                              
            $back=M('wall_reply')->where(array('id'=>$id,'token'=>$this->token))->save(array('status'=>0));
            if($back==true)
            {
                $this->ajaxReturn("", "OK", 1);
            }
            else
            {
                $this->ajaxReturn("", "服务器繁忙,请稍后再试", 0);
            }
        }        
    }
    
    public function wall()
    {
        $id = $this->_get('id');
        
        $wall  = M('Wall')->where(array('token'=>$this->token,'id'=>$id, 'status' => 1))->find();
        if ($wall != false) 
        {
            $lotterys = unserialize($wall['lotterys']);
            $this->assign('lotterys', $lotterys);

            $this->assign('set',$wall);

            $time = time();
            if ($time > strtotime($wall['end_time'])) 
            {
                $this->error('活动已结束',U('Wall/index'));
            } 
            else
            {
                $this->display();
            }
        }
        else
        {
            $this->error('活动不存在',U('Wall/index'));
        }
    }
    
    public function lottery()
    {
        $id = $this->_get('id');
        $wall  = M('Wall')->where(array('token'=>$this->token,'id'=>$id, 'status' => 1))->find();
        if ($wall != false){
            $time = time();
            if( $time > strtotime($wall['end_time'])) {
                $this->error('活动已结束',U('Wall/index'));
            }else{
                $lotterys = unserialize($wall['lotterys']);
                $this->assign('lotterys', $lotterys);
                $this->assign('set',$wall);

                //获取已中奖名单
                $sql = "SELECT w.id,w.lottery,w.token,w.wall_id,w.wecha_id,u.nickname,u.headimgurl,w.`status` "
                        ." FROM `tp_wall_winner` as w LEFT JOIN tp_wecha_user as u on w.wecha_id = u.wecha_id AND w.token = u.token " 
                        ." WHERE ( `wall_id` = $id ) AND ( w.token = '$this->token' ) ORDER BY w.id ";
                $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
                $winners = $Model->query($sql);
                foreach ($winners as $key => $value){
                    if (empty($winners[$key]['nickname'])){
                        $winners[$key]['nickname'] = '匿名';
                    }
                    if (empty($winners[$key]['headimgurl'])){
                        $winners[$key]['headimgurl'] = '/themes/a/images/logo.jpg';
                    }
                }
                $this->assign('winners', $winners);
                $this->assign('winnernum', count($winners));
                $this->display();
            }
        }else{
            $this->error('活动不存在',U('Wall/index'));
        }
    }
    
    public function jsonreply()
    {
        $id = $this->_post('id');
        $where = array('tp_wall_reply.token'=>$this->token,'wall_id'=>$id,'tp_wall_reply.status'=>2);
        $replys = M('wall_reply')->join('left join tp_wecha_user as u on tp_wall_reply.wecha_id = u.wecha_id and tp_wall_reply.token = u.token  ')
	        ->where($where)->Distinct(true)
	        ->field('tp_wall_reply.text, tp_wall_reply.createtime,u.wecha_id, u.nickname, u.headimgurl')
	        ->order('tp_wall_reply.createtime desc')
	        ->limit(50)
	        ->select();
	     foreach ($replys as $key => $value) 
        {	
            if (empty($replys[$key]['nickname'])) 
            {
                $replys[$key]['nickname'] = '匿名';
            }

            if (empty($replys[$key]['headimgurl'])) 
            {
                $replys[$key]['headimgurl'] = '/themes/a/images/logo.jpg';
            }
        }
        $this->ajaxReturn($replys, "OK", 1);
    }
    
    public function jsonuser()
    {
        $id = $this->_post('id');

        $sql = " SELECT distinct r.wecha_id,u.nickname,u.headimgurl,u.userinfo "
                ." FROM `tp_wall_reply` as r LEFT JOIN tp_wecha_user as u on r.wecha_id = u.wecha_id AND r.token = u.token "
                ." WHERE ( `wall_id` = $id ) AND ( r.token = '$this->token' ) AND r.status = 2  ORDER BY r.createtime desc ";
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $users = $Model->query($sql);

        foreach ($users as $key => $value) 
        {
            if (empty($users[$key]['nickname'])) 
            {
                $users[$key]['nickname'] = '匿名';
            }

            if (empty($users[$key]['headimgurl'])) 
            {
                $users[$key]['headimgurl'] = '/themes/a/images/logo.jpg';
            }
        }
        $this->ajaxReturn($users, "OK", 1);
    }
    
    public function jsonwin()
    {
        $id = $this->_post('id');
        
        $wall  = M('Wall')->where(array('token'=>$this->token,'id'=>$id, 'status' => 1))->find();
        
        if(empty($wall))
        {
             $this->ajaxReturn("", "活动不存在！", 0);
        }

        $time = time();
        if( $time > strtotime($wall['end_time'])) 
        {
            $this->ajaxReturn("", "活动已经结束！", 0);
        }
        
        //获取已中奖名单
        $sql = "SELECT w.id,w.lottery,w.token,w.wall_id,w.wecha_id,u.nickname,u.headimgurl,w.`status` FROM `tp_wall_winner` as w LEFT JOIN tp_wecha_user as u on w.wecha_id = u.wecha_id  AND w.token = u.token  WHERE ( `wall_id` = $id ) AND ( w.token = '$this->token' ) ORDER BY w.id ";
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $winners = $Model->query($sql);
        
        //$this->assign('set',$wall);
        
        $lotterys = unserialize($wall['lotterys']);
        //剔除已经抽出的奖项和已中奖
        $winner_ids = array();
        foreach ($winners as $winner) {
            for($i = 0; $i < count($lotterys); $i++) {
                if ($lotterys[$i][1] == $winner["lottery"]) {
                    $lotterys[$i][2]--;
                }
            }
            array_push($winner_ids, $winner["wecha_id"]);
        }
            
        //检查是否还有剩余奖品
        $lottery = null;
        for($i = 0; $i < count($lotterys); $i++) {
            if ($lotterys[$i][2] > 0) {
                $lottery = $lotterys[$i];
                break;
            }
        }

        if (empty($lottery)) 
        {
            $this->ajaxReturn("", "奖品已经抽完！", 0);
        }
            
        // 选取未中奖用户
        $candidators = array();
        $where = array('tp_wall_reply.token'=>$this->token,'wall_id'=>$id,'tp_wall_reply.status'=>2);
        if (count($winner_ids) > 0) 
        {
            $where['tp_wall_reply.wecha_id'] =array ('not in', $winner_ids);
            
        }
        $candidators = M('wall_reply')->join('left join tp_wecha_user as u on tp_wall_reply.wecha_id = u.wecha_id and  tp_wall_reply.token = u.token')->where($where)->Distinct(true)->field('u.wecha_id, u.nickname, u.headimgurl')->select();
        if (count($candidators) <= 0) 
        {
            $this->ajaxReturn("", "现场全部人员都已中奖！", 0);
        }

        $luckers = $this->getLotterys($lottery[2], $lottery[1], $candidators);
            
        // 保存中奖纪录, 重置昵称和头像
        foreach ($luckers as $key => $lucker) 
        {
            $data["nickname"]       = $lucker["nickname"];
            $data["headimgurl"]     = $lucker["headimgurl"];
            $data["status"]         = 1;
            $data["lottery"]        = $lucker["lottery"];
            $data["wall_id"]        = $id;
            $data["wecha_id"]       = $lucker["wecha_id"];
            $data["token"]          = $this->token;
            M('wall_winner')->add($data);

            if (empty($luckers[$key]['nickname'])) 
            {
                $luckers[$key]['nickname'] = '匿名';
            }

            if (empty($luckers[$key]['headimgurl'])) 
            {
                $luckers[$key]['headimgurl'] = '/themes/a/images/logo.jpg';
            }
        }
        $this->ajaxReturn($luckers, "OK", 1);
        
    }
    
    private function getLotterys($luckNum, $lotteryName, $users) {
        
        $luckers = array();
        $winner = array();
        for ($i=0;;$i++)
        {
            $number = rand(0, count($users) - 1);
            
            if (!in_array($number, $winner)) {
                // 如果数组中没有该数，将其加入到数组
                array_push($winner, $number);
                $users[$number]["lottery"] = $lotteryName;
                array_push($luckers, $users[$number]);
                Log::record($users[$number]["nickname"]." ".$users[$number]["lottery"]."\r\n", Log::DEBUG);       
                Log::save();
            }
            if (count($luckers) == $luckNum || count($luckers) == count($users)) {
                break;
            }
        }
        return $luckers;
    }
}
?>

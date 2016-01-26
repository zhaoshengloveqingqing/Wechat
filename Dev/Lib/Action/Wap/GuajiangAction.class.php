<?php
class GuajiangAction extends BaseAction
{
    public function index()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) 
        {
            echo '此功能只能在微信浏览器中使用';
            exit;
        }
     
        $token        = $this->_get('token');
        $wecha_id     = $this->_get('wecha_id');
        $id           = $this->_get('id');

        if (empty($token) || empty($wecha_id) || empty($id)) 
        {
            exit;
        }

        $Lottery = M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>2, 'status'=>array('neq', 3)))->find(); 
        $data = array();

        $data['canrqnums']   = $Lottery['canrqnums'];
        $data['fist']        = $Lottery['fist'];
        $data['second']      = $Lottery['second'];
        $data['third']       = $Lottery['third'];
        $data['fistnums']    = $Lottery['fistnums'];
        $data['secondnums']  = $Lottery['secondnums'];
        $data['thirdnums']   = $Lottery['thirdnums'];        
        $data['info']        = $Lottery['info'];
        $data['txt']         = $Lottery['txt'];
        $data['sttxt']       = $Lottery['sttxt'];
        $data['title']       = $Lottery['title'];
        $data['statdate']    = $Lottery['statdate'];
        $data['enddate']     = $Lottery['enddate'];
        $data['isDisplayPrize']     = $Lottery['displayjpnums'];

        $data['status'] = 0;// 0、权限不够 1、还未开始 2、中过奖 3、未中奖但还可以抽，4、未中奖但不可以抽 4、奖品已发完 9、结束

        $can = $this->hasPermission($Lottery,$wecha_id);

        /****
        //会员等级
        $class_con = array('token'=>$token,'status'=>1);
        $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
        if(empty($class_info)){
            $class_info = C('MEMBER_GROUP');
        }
        $this->assign('class_info',$class_info);
        $this->assign('group',$group);
        **/
        
        if(!$can){
            $this->assign('Guajiang',$data);
            $this->display();
            exit();
        }
            

        $data['status'] = 1; // 0、权限不够 1、还未开始 2、中过奖 3、未中奖但还可以抽，4、未中奖但不可以抽 4、奖品已发完 9、结束
        
        if ($Lottery['statdate'] > time()) //未开始
        {

            $data['status'] = 1; 
            $this->assign('Guajiang',$data);  
            $this->display();
            exit();
        }

        if ($Lottery['enddate'] < time() || $Lottery['status'] == 2) //过期
        {

            $data['status'] = 9;           
            $data['usenums'] = 3;
            $data['endinfo'] = $Lottery['endinfo'];
            $this->assign('Guajiang',$data);
            $this->display();
            exit();
        }

        $record_db    = M('Lottery_record');
        $where        = array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
        $record       = $record_db->where($where)->find();        
        if ($record == null)
        {
            $record_db->add($where);
            $record = $record_db->where($where)->find();
        } 
            
        if ($record['islottery'] == 1) //抽过奖
        {
            //用户已经抽过奖
            $data['status']     = 2; 
            $data['usenums']    = 2;
            $data['sncode']     = $record['sn'];
            $data['uname']      = $record['wecha_name'];                 
            $data['winprize']   = $record['prize'];
            
        }
        else
        {
            if ($record['usenums'] >= $Lottery['canrqnums'] ) 
            {
                $data['usenums']   = 1;
                $data['winprize']  = '抽奖次数已用完';
                $data['status']    = 4; 
                $data['remainder'] = $Lottery['canrqnums'] - $record['usenums'];
            }
            else
            {
                $data['status'] = 3;
                M('Lottery_record')->where(array('id'=>$record['id']))->setInc('usenums');
                $record = M('Lottery_record')->where(array('id'=>$record['id']))->find();
                $prize_arr = array( 
                    '0' => array('id'=>1,'prize'=>'一等奖','v'=>$Lottery['fistnums'],  'l' => $Lottery['fistnums'] - $Lottery['fistlucknums']), 
                    '1' => array('id'=>2,'prize'=>'二等奖','v'=>$Lottery['secondnums'],'l' => $Lottery['secondnums'] - $Lottery['secondlucknums']),
                    '2' => array('id'=>3,'prize'=>'三等奖','v'=>$Lottery['thirdnums'], 'l' => $Lottery['thirdnums'] - $Lottery['thirdlucknums']),
                    );

                if ($Lottery['allpeople'] == 1) 
                {
                    //始终中奖
                    $arr = array();
                    $total_prize_count = 0;
                    foreach ($prize_arr as $key => $val) 
                    { 
                        $arr[$val['id']] = $val['l']; 
                        $total_prize_count += $val['l'];
                    } 
                    $rid = $this->generate_random_price($arr, $total_prize_count);
                }
                else
                {
                    $arr = array();
                    foreach ($prize_arr as $key => $val) 
                    { 
                        $arr[$val['id']] = $val['v']; 
                        $total_prize_count += $val['v'];
                    } 
                    $rid = $this->generate_random_price($arr, $Lottery['allpeople']);  
                }
                    
                
                $winprize = $prize_arr[$rid-1]['prize'];
                $zjl = false;
                    
                switch($rid)
                {
                    case 1:
                        if ($Lottery['fistlucknums'] >= $Lottery['fistnums']) 
                        {
                            $zjl      = false; 
                            $winprize = '谢谢参与';
                            $rid      = 999;
                        }
                        else
                        {
                            $zjl    = true;                        
                            M('Lottery')->where(array('id'=>$id))->setInc('fistlucknums');
                        }
                        break;
                            
                    case 2:
                        if (empty($Lottery['second']) 
                            || empty($Lottery['secondnums'])
                            || $Lottery['secondlucknums'] >= $Lottery['secondnums']) 
                        {
                            $zjl = false;
                            $winprize = '谢谢参与';
                            $rid = 999;
                        }
                        else
                        {
                            $zjl    = true;                        
                            M('Lottery')->where(array('id'=>$id))->setInc('secondlucknums');
                        }
                        break;
                    case 3:
                        if (empty($Lottery['third']) 
                                || empty($Lottery['thirdnums']) 
                                || $Lottery['thirdlucknums'] >= $Lottery['thirdnums']) 
                        {
                            $zjl = false;
                            $winprize = '谢谢参与';
                            $rid = 999;
                        }
                        else
                        {
                            $zjl    = true;                        
                            M('Lottery')->where(array('id'=>$id))->setInc('thirdlucknums');   
                        }
                        break;
                    default:
                        $zjl = false;
                        $winprize = '谢谢参与';
                        $rid = 999;
                        break;
                }
                
                $data['zjl']            = $zjl;
                $data['wecha_id']       = $record['wecha_id'];        
                $data['lid']            = $record['lid'];            
                $data['winprize']       = $winprize;
                $data['prize_level']    = $rid;
                //包含当前这次抽奖
                $data['remainder'] = $Lottery['canrqnums'] - $record['usenums'] + 1;
            } //end if;
        } // end first if; 
        
        //抽奖开始后，抽奖次数可能会变小导致出现负数剩余次数
        $data['remainder'] = $data['remainder'] > 0 ? $data['remainder'] : 0;
        $this->assign('Guajiang',$data);
        $this->display();
        
    }
    
    /**
     * @param pri_ary  假设奖品数组是按1、2、3等奖序排列的
     * @param total    预计总的抽奖人数，决定中奖的概率
     * @return 奖项级别，1,2,3 或者999不中
     */
    protected function generate_random_price($pri_ary, $total) 
    { 
        $r =  mt_rand(1, $total);

        $cur_prize = 999;  //尽量大
        $price_range = 0;

        foreach ($pri_ary as $key => $value) 
        {
            $price_range += $value;
            if ( $r <= $price_range)
            {
                $cur_prize = $key;
                break;
            }
            
        }

        return $cur_prize; 
    } 

    /**
    *验证是否有权限抽奖
    **/
    protected function hasPermission($lottery,$wecha_id){
        if($lottery['all_funs']){
            return true;
        }
        $group = unserialize($lottery['group']);

        $card = M('member_card_create')->where(array('token'=>$lottery['token'],'wecha_id'=>$wecha_id,'status'=>1))->find();
        $groupid = $card ? $card['groupid'] : -1;
        if(empty($group) && $groupid == -1){//group里没有限制，而且此wecha_id不是会员
            return false;
        }
        if(in_array($groupid, $group['groupid'])){
            return true;
        }
        return false;
    }
    

    public function add()
    {
        if ($_POST['action'] == 'add')
        {
            $lid                 = $this->_post('lid');
            $wechaid             = $this->_post('wechaid');
            $data['phone']       = $this->_post('tel');
            $data['wecha_name']  = $this->_post('wxname');
            $data['prize']       = $this->_post('prize');
            $data['islottery']   = 1;
            $data['time']        = time();
            $data['sn']          = uniqid();
            $rollback = M('Lottery_record')->where(array('lid'=> $lid,   'wecha_id'=>$wechaid))->save($data);
            echo'{"success":1,"msg":"恭喜！尊敬的'.$data['wecha_name'].'请您保持手机通畅！请您牢记的领奖号:'.$data['sn'].'"}';
            exit;
        }
        
/*
        $record = M('Lottery_record');
        $data['phone']         = $this->_post('tel');
        $data['wecha_name'] = $this->_post('wxname');
        $data['islottery']     = 1;
        $data['time']        = time();
        $data['sn']            = uniqid();
        $rollback = $record->where(array('lid'=>$this->_post('lid') ,
                'wecha_id'=>$this->_post('wechaid') ))->save($data);
                
                */
    }
    
}
?>
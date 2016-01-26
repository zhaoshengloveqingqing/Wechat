<?php
class VoteAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'toupiao';
        parent::checkOpenedFunction();
    }

    public function index()
    {
        
        $list=M('vote')->where(array('token'=>session('token'),'status'=>array('neq', 0)))->select();
        $now = date("Y-m-d");
        for($i = 0; $i < count($list); $i++) {
            if ($list[$i]["status"] == 2) {
                $list[$i]["statustext"] = "已停止";
            } else {
                if ($list[$i]["starttime"] > $now) {
                    $list[$i]["statustext"] = "未开始";
                } elseif ($list[$i]["starttime"] <= $now && $list[$i]["endtime"] >= $now) {
                    $list[$i]["statustext"] = "已开始";
                } else {
                    $list[$i]["statustext"] = "已结束";
                }
            }
        }
        
        $this->assign('list',$list);
        $this->display();
    }

    public function a()
    {
        echo 12;
    }

    public function ajaxLimitShare()
    {
        $limit = $_GET['limit'];
        $id = $_GET['id'];
        if($limit !== false && $id !== false)
        {
            $value = intval( $limit );
            if($value >=0 )
            {
                M('vote')->where(array('token'=>$this->token,'id'=>$id))->setField('share_limit',$value);
            }
        }

    }
    
    public function edit(){
        
        $text_id = $this->_get('id','intval');
        $token = session('token');
        $now = date("Y-m-d");
        
        if(IS_POST)
        {
            $vdb             = M('vote');
            $data['title']   = $_POST['title'];
            $data['content'] = $_POST['content'];
            $data['imgurl']  = $_POST['imgurl'];
            $data['type']    = $_POST['type'];
            $data['endtime'] = $_POST['endtime'];

            $icons           = $_POST['icons'];
            $options         = $_POST['options'];
            $well_opts = array();
            $well_icons = array();
            //  如果有选项才保存图标地址
            for ($i=0; $i < count($options); $i++) 
            { 
                if (!empty($options[$i])) 
                {
                    array_push($well_opts, $options[$i]);
                    if (!empty($icons[$i]))
                    {
                        array_push($well_icons, $icons[$i]);
                    }
                    
                }
            }
            $data['options'] = implode('^', $well_opts);
            if (count($well_icons) > 0) 
            {
                //如果所有的选项都没有图标，则该投票为文本投票
                $data['icons']   = implode('^', $well_icons);
            }
            
            $where['id']    = $text_id;
            $where['token'] = $token;

            if ($vdb->where($where)->save($data)) {
                $this->success('投票修改成功',U('Vote/index'));
            } else {
                $this->error('服务器繁忙,请稍候再试');
            }
        } 
        else 
        {
        
        
            $where['id']     = $text_id;
            $where['token']  = $token;
            $where['status'] = array('neq', 0);
            $vote = M('vote')->where($where)->find();
            
            $vote["options"] = $this->getVoteResult($text_id, $vote["options"],$vote['icons']);
            $vote["userNum"] = $vote["options"]["num"];
            $vote["optionNum"] = $vote["options"]["optionNum"];
            unset($vote["options"]["num"]);
            unset($vote["options"]["optionNum"]);

            if (empty($vote['icons'])) 
            {
                $this->assign('is_text_vote',1);
            }
            else
            {
                $this->assign('is_text_vote',0);
            }
            
            if ($vote['status'] == 2) {
                $vote["statustext"] = "已停止";
            } else {
                if ($vote["starttime"] < $now) {
                    $vote["statustext"] = "未开始";
                } elseif ($vote["starttime"] <= $now && $vote["endtime"] >= $now) {
                    $vote["statustext"] = "已开始";
                } else {
                    $vote["statustext"] = "已结束";
                }
            }        
            $this->assign('vote',$vote);
            $this->display();
        }
    }

    
    private function getVoteResult($id, $voteOptions, $voteIcons) {
        $voteOptions = split("\\^", $voteOptions);
        $optionsNum = count($voteOptions);

        $voteIconAry = split("\\^", $voteIcons);
        
        $vdb = M('vote_join');
        $where['vote_id'] = $id;
        $voteCount = $vdb->field(array('optionid','count(*)'=>'num'))->where($where)->group("optionid")->select();
        $userCount = $vdb->where($where)->count();
        
        $allCount = 0;
        for($j = 0; $j < count($voteCount); $j++) {
            $allCount += $voteCount[$j]["num"];
        }
        for($i = 1; $i <= $optionsNum; $i++) {
            $voteResult[$i]["option"] = $voteOptions[$i - 1];
            $voteResult[$i]["icon"]   = $voteIconAry[$i - 1];
            $voteResult[$i]["num"] = 0;
            for($j = 0; $j < count($voteCount); $j++) {
                if ($i == $voteCount[$j]["optionid"]) {
                    $voteResult[$i]["num"] = $voteCount[$j]["num"];
                    break;
                }
            }
            if ($allCount == 0) {
                $voteResult[$i]["percent"] = 0;
            } else {
                $voteResult[$i]["percent"] = (int)($voteResult[$i]["num"] * 100 / $allCount);
            }
            $voteResult[$i]["opercent"] = 100 - $voteResult[$i]["percent"];
        }
        $voteResult["num"] = $userCount;
        $voteResult["optionNum"] = $optionsNum;
        return $voteResult;
    }


    public function add()
    {
        if(IS_POST)
        {
            $now = date("Y-m-d");

            $data['status']    = 1;
            $data['date']      = $now;
            $data['token']     = $this->token;
            $data['title']     = $this->_post('title','trim');
            $data['imgurl']    = $this->_post('imgurl','trim');
            $data['content']   = $this->_post('content','trim');
            $data['starttime'] = $this->_post('starttime','trim');
            $data['endtime']   = $this->_post('endtime','trim');
            $data['keyword']   = $this->_post('keyword','trim');
            $data['type']      = $this->_post('type','intval');

            
            $icons           = $_POST['icons'];
            $options         = $_POST['options'];
            $well_opts = array();
            $well_icons = array();
            //  如果有选项才保存图标地址
            for ($i=0; $i < count($options); $i++) 
            { 
                if (!empty($options[$i])) 
                {
                    array_push($well_opts, $options[$i]);
                    if (!empty($icons[$i]))
                    {
                        array_push($well_icons, $icons[$i]);
                    }
                    
                }
            }
            $data['options'] = implode('^', $well_opts);
            if (count($well_icons) > 0) 
            {
                //如果所有的选项都没有图标，则该投票为文本投票
                $data['icons']   = implode('^', $well_icons);
            }
            
            
            
            // 检查是否存在关键词
            $where['token']     = $this->token;
            $where['keyword']   = $this->_post('keyword','trim');
            $where['status']    = 1;
            $where['starttime'] = array('elt', $now);
            $where['endtime']   = array('egt', $now);
            
            $vote_db = M('Vote');
            $check = $vote_db->where($where)->find();
            
            if (!empty($check) && $check['endtime'] > $now) 
            {
                $this->error('已经存在投票关键词'.$this->_post('keyword').'请删除或者停止同名关键词投票');
                return;
            }

            if(empty($data['keyword']))
            {
                $this->error('关键词不能为空');
                return;
            }

            if($_POST['endtime'] < $_POST['starttime'])
            {
                $this->error('结束时间不能小于开始时间');
                return;
            }
            else
            {
                $ret = $vote_db->add($data);
                if ($ret)
                {
                    $data1['pid']      = $ret;
                    $data1['module']   = 'vote';
                    $data1['token']    = session('token');
                    $data1['keyword']  = $this->_post('keyword');
                    $data1['function'] = $this->function;
                    M('Keyword')->add($data1);
                    $this->success('投票创建成功',U('Vote/index'));
                
                }
                else
                {
                    Log::record($data->getError());
                    Log::save();
                    $this->error('服务器繁忙,请稍候再试');
                }
            }
        }
        else
        {
            $this->display();
        }
    }

    public function del(){
        $text_id = $this->_get('id','intval');
        $token = session('token');

        $where['id'] = $text_id;
        $where['token'] = $token;
        $data['status'] = 0;
        $ret = M('vote')->where($where)->save($data);
        
        if ($ret >= 0) {
            M('Keyword')->where(array('pid'=>$text_id,'token'=>$token,'function'=>$this->function,'module'=>'vote'))->delete();
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/index'));
        }
    }
    
    public function end(){
        $text_id = $this->_get('id','intval');
        $token = session('token');

        $where['id'] = $text_id;
        $where['token'] = $token;
        $data['status'] = 2;
        $ret = M('vote')->where($where)->save($data);
        
        if ($ret >= 0) {
            M('Keyword')->where(array('pid'=>$text_id,'token'=>$token,'function'=>$this->function,'module'=>'vote'))->delete();
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/index'));
        }
    }
}



?>
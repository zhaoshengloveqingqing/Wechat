<?php
class VoteAction extends BaseAction
{
    public function index()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $this->display();
    }

    public function send()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if(!strpos($agent,"MicroMessenger")) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        
        $db = M('vote');
        $data["status"]  = 1;
        $data["title"]   = $this->_get('title');
        $data["content"] = $this->_get('content');
        $data["content"] = str_replace("\r\n", "<br/>", $data["content"]); 
        $data["content"] = str_replace("\n", "<br/>", $data["content"]);  
        $data["author"]  = $this->_get('author');
        $data["options"] = $this->_get('options');
        $data["type"]    = $this->_get('type','intval');
        $data["date"]    = date("Y-m-d");
        
        $rid =$db->add($data);
        $result["id"]=$rid;
        $result["title"] = $this->_get('title');
        $result["content"] = $data["content"];
        
        $this->ajaxReturn($result, "OK", 1);
    }
    
    public function showVote(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if(!strpos($agent,"MicroMessenger")) {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        
        $db = M('vote');
        $id = $this->_get('id','intval');
        $where['id'] = $id;
        $where['status'] = 1;
        $vote = $db->where($where)->find();
        if (empty($vote)) {
            echo '投票已经过期';exit;
        }
        /*$vote["options"] = split("\\^", $vote["options"]);
        $optionsNum = count($vote["options"]);
        $vote["optionsNum"] = $optionsNum;*/

        if (empty($vote['icons'])) 
        {
            $this->assign('is_text_vote',1);
        }
        else
        {
            $this->assign('is_text_vote',0);
        }
        $vote["options"]   = $this->getVoteResult($id, $vote["options"],$vote['icons']);
        $vote["userNum"]   = $vote["options"]["num"];
        $vote["optionNum"] = $vote["options"]["optionNum"];

        $vote['shared_title'] = htmlspecialchars($vote['title'], ENT_QUOTES);
        $vote['shared_title'] = preg_replace("/\s/", '',$vote['title']) ;

        $vote['shared_content'] = htmlspecialchars($vote['content'], ENT_QUOTES);
        $vote['shared_content'] = preg_replace("/\s/", '',$vote['content']) ;
        unset($vote["options"]["num"]);
        unset($vote["options"]["optionNum"]);
        
        if($vote["date"] == date("Y-m-d")) {
            $vote["date"] = "刚刚";
        } else if($vote["date"] == date("Y-m-d",strtotime("-1 day"))) {
            $vote["date"] = "昨天";
        }
        $this->assign('vote',$vote);
        
        /*$where2['hots'] = array("gt", 0);
        $where2['status'] = 1;
        $hots = $db->where($where2)->limit(3)->order('hots desc')->select();
        $this->assign('hots',$hots);*/
        
        $this->display();
    }
    
    public function join(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if(!strpos($agent,"MicroMessenger")) {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        
        $db = M('vote_join');
        $id = $this->_get('id','intval');
        $data['vote_id'] = $id;
        $data['user'] = session_id();
        $options = split(";", $this->_get('options'));
        foreach ($options as $opt) {
            if ($opt > 0) {
                $data['optionid'] = $opt;
                $db->add($data);
            }
        }
        $vdb = M('vote');
        $vwhere['id'] = $id;
        $vwhere['status'] = 1;
        $vote = $vdb->where($vwhere)->find();
        $voteResult["options"] = $this->getVoteResult($id, $vote["options"]);
        
        $this->ajaxReturn($voteResult, "OK", 1);
    }
    
    private function getVoteResult($id, $voteOptions, $voteIcons) {
        $voteOptions = split("\\^", $voteOptions);
        $voteIconAry = split("\\^", $voteIcons);
        $optionsNum = count($voteOptions);
        
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
            $voteResult[$i]["num"]    = 0;
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
}
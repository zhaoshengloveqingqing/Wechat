<?php
require_once(COMMON_PATH.'/TemplateDataLoader.php');

/* 
 * 该类用于管理前台用户的某个公共账号的功能
 */

class PublicAccountFuncManager {
    private $websiteUserId;
    private $token;
    private $uname;
    
    function __construct($websiteUserId, $uname, $token) {
        $this->websiteUserId = $websiteUserId;
        $this->token = $token;
        $this->uname = $uname;
    }
    
    /*
     * 基于该用户开通的功能组列表，自动为这个公共号打开功能项，并复制模版数据
     */
    function openFunctions() {
        // get function groups of current user
        $currTime = time();
        $funcGroups = M('user_func_group')
                ->where(array('user_id'=>$this->websiteUserId, 'status'=>1, 'expire_time' =>array('gt', $currTime)))
                ->field('group_id')
                ->select();
        
        
        // for each function group, try to open its function items automatically
        foreach($funcGroups as $funcGroupId ) {
            $this->openSingleFuncGroup($funcGroupId['group_id']);
        }
    }
    
    function  openSingleFuncGroup($fgId) {
        // get function list
        $funcsInFG =  M('function')->where(array('status'=>1, 'fgid'=>$fgId))
                ->select();

        // for each function, try to open it;
        if(is_array($funcsInFG)) {
            foreach($funcsInFG as $funcInFG) {
                    // open this function
                    $this->openSingleFunction($funcInFG);
            }

        }
    }




    /*
     * 打开单个功能，需要考虑模版数据的复制
     */
    function openSingleFunction($function) {
        $funcName = $function['funname'];
        // get opened function list for current publich account;
        $queryname = M('Token_open')->where(array('token'=>$this->token, 'uid'=>$this->websiteUserId))
                ->field('queryname')
                ->find();
        $openedFuncNames = array();
        if($queryname) {
            $openedFuncNames = array_unique(explode(',', $queryname['queryname']));
        }else{
            M('Token_open')->add(array('uid'=>$this->websiteUserId, 'token' => $this->token ));
        }
        
        if(in_array($funcName, $openedFuncNames)) {
            // ingore functions which has been opened before
            return;
        }
        array_push($openedFuncNames, $funcName);
        
        // try to load template data
        $templateDataLoader = new TemplateDataLoader($this->websiteUserId, $this->uname, $this->token);
        $templateDataLoader->loadTemplateData($funcName);
        
        // update token_open table
        M('Token_open')->where(array('uid'=>$this->websiteUserId, 'token' => $this->token))->setField('queryname', implode(',', $openedFuncNames));
        session('opened_funcs',$openedFuncNames);
        
        if (isset($function['keywords']) && !empty($function['keywords'])) {
            $keywords = explode(' ', $function['keywords'] );
            $kwds_db = M('keyword');
            $kwd_data['uid'] = $this->websiteUserId;
            $kwd_data['token'] = $this->token;
            $kwd_data['type'] = 2;
            $kwd_data['module'] = 'img';
            $kwd_data['function'] = $funcName;
            $kwd_data['pid'] = 0;
            foreach($keywords as $vo) {
                $kwd_data['keyword'] = $vo;    
                $kwds_db->add($kwd_data);
            }
        }
    }
    
}


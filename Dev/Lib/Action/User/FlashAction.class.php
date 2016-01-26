<?php
require_once(COMMON_PATH.'/LinkHelper.php');
/**
 *首页幻灯片回复
**/
class FlashAction extends UserAction{

    protected function _initialize()
    {
		parent::_initialize();
		parent::checkOpenedFunction('shouye');
    }

	public function index(){
		$db = D('Flash');
		$where['token']=session('token');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$this->generateLinkTypeDesc($info));
		$this->display();
	}

    private function  generateLinkTypeDesc($lists) {
        $tmpLinkTypes = $this->getLinkTypes();
        $linkTypes = LinkHelper::buildId2RecordMap($tmpLinkTypes);
            
        $tmpmodules = LinkHelper::getModules();
        $modules = LinkHelper::buildId2RecordMap($tmpmodules);
            
        $tmpactivities = LinkHelper::getActivities();
        $activities = LinkHelper::buildId2RecordMap($tmpactivities);
            
        $tmpCarModules = LinkHelper::getCarModules();
        $carModules = LinkHelper::buildId2RecordMap($tmpCarModules);
            
        foreach($lists as &$l) {
            switch($l['linktype']) {
                    case 'nolink':
                        $l['linkdesc'] = '无链接';
                        break;
                    case 'linkurls':
                        $l['linkdesc'] = $l['link_param_l1'];
                        break;
                    case 'modules':
                        $l['linkdesc'] = $modules[$l['link_param_l1']]['name'];
                        break;
                    case 'activitys':
                        $l['linkdesc'] = $activities[$l['link_param_l1']]['name'];
                        break;
                     case 'car':
                        $l['linkdesc'] = $carModules[$l['link_param_l1']]['name'];
                        break;
                    default:
                        $l['linkdesc'] = '未知';
                        break;
            }
        }
        return $lists;
    }
        
    private function getLinkTypes() {
        return array_merge(
                array(array('id'=>'nolink', 'name'=>'无链接')),
                LinkHelper::getCommonLinkTypes());
    }  

    public function getActivityDetail() {
        $token = session('token');
        $activityId = $_POST['activityId'];
        $flashId = intval($_POST['flashId']);
            
        if(empty($token) || empty($activityId)) {

                $this->ajaxReturn(array(), 'JSON');
            }

            $details = array();
            switch($activityId){
                case 'dingdan':
                    $details = LinkHelper::getOrders($token);
                    break;
                case 'dazhuanpan':

                    $details = LinkHelper::getLotteryList($token,1);
                    break;
                case 'guaguaka':
                    $details = LinkHelper::getLotteryList($token, 2);
                    break;
                case 'youhuiquan':
                    $details = LinkHelper::getLotteryList($token,3);
                    break;
                case 'toupiao':
                   
                    $details = LinkHelper::getVoteList($token); 
                    break;
                
                case 'hotel':
                    $details = LinkHelper::getHotels($token);
                    break;
                  
                case 'pinglun':

                    $details = LinkHelper::getReplyList($token);
                    break;
                case 'yingxiang':

                    $details = LinkHelper::getImpressList($token);
                    break;
                default:
                    break;
            }

            // load flash information
            $selectedId = '';
            if(!empty($flashId)) {
                $where['id'] = $flashId;
                $where['token'] = session('token');
                $info=M('Flash')->where($where)->find();
                if($info) {
                    if($info['link_param_l1'] == $activityId) {
                        $selectedId = $info['link_param_l2'];
                    }
                }
            }
            $res = array();
            $res['details'] = $details;
            $res['selected'] = $selectedId;
            $this->ajaxReturn($res, 'JSON');
    }

	public function add(){
        $this->assign('linktypes', $this->getLinkTypes());
        $this->assign('allActivities', LinkHelper::getActivities());
        $this->assign('allModules', LinkHelper::getModules());
        $this->assign('carModules', LinkHelper::getCarModules());
                
        $home = M('vweb_setting')->where(array('token'=>session('token')))->find();
        $allTmpls =  C('web_homepage_tmpl');
        $this->assign('homepage_template', $allTmpls[$home['tmpl_id']]);
                
		$this->display('edit');
	}

	public function edit(){
		$where['id']=$this->_get('id','intval');
		$where['token'] = $this->token;
		$res=D('Flash')->where($where)->find();
		$this->assign('info',$res);
                
        $this->assign('linktypes', $this->getLinkTypes());
        $this->assign('allActivities', LinkHelper::getActivities());
        $this->assign('allModules', LinkHelper::getModules());
        $this->assign('carModules', LinkHelper::getCarModules());
        $home = M('vweb_setting')->where(array('token'=> $this->token))->find();
        $allTmpls =  C('web_homepage_tmpl');
        $this->assign('homepage_template', $allTmpls[$home['tmpl_id']]);
                
		$this->display();
	}

	public function del()
    {
		$where['id']      = $this->_get('id','intval');
		$where['token']   = $this->token;

		if(D(MODULE_NAME)->where($where)->delete()){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	public function addNewFlash(){
		//C('TOKEN_ON',false);
		$this->all_insert();
	}
	public function upsave(){
		$this->all_save();
	}

}
?>

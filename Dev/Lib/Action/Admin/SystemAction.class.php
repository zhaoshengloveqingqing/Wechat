<?php
/**
 * 后台管理首页
 **/
class SystemAction extends BackAction
{
    
    public function index()
    {
        $where['pid'] = 1;
        $where['status'] = 1;

        //查找可访问的根节点
        $access_list = M('access')->Distinct(true)->field('node_id')->where(array('role_id' => $_SESSION['roleid'], 'pid'=>1))->select();
        if ($access_list) 
        {
            $node_arr = array();
            foreach ($access_list as $vo) 
            {
                array_push($node_arr, ''.$vo['node_id']);
            }
            $where['id'] = array('in', $node_arr); 
        }
        
        
        $order['sort'] = 'asc';
        $nav = M('node')->where($where)->order($order)->select();      
        $this->assign('nav',$nav);
        
        $systemNodes = M('node')->where(array('name' => 'System', 'status' => '1'))->field('id')->select();
        $systemNodeIdArray = array();
        foreach($systemNodes as $systemNode) {
            array_push($systemNodeIdArray, $systemNode['id']);
        }
        
        $condition = array();
        $condition['role_id'] = $_SESSION['roleid'];
        $condition['level'] = 2;
        $condition['node_id'] = array('not in', $systemNodeIdArray);
        $accessNodeList = M('access')->where($condition)->field('node_id,pid')->select();
        $this->assign('accessNodeList', $accessNodeList);
      
        $this->display();
    }
    
    public function menu()
    {
        $where['display']    = 2;
        $where['status']    = 1;
        $order['sort']        = 'asc';
        if(empty($_GET['pid']))
        {
            //用户登录后，查找一级栏目
            $pid = 1;

            $Model = new Model();
            $first_root_node = $Model->query("SELECT DISTINCT  node_id, n.title FROM tp_access as a JOIN tp_node as n on a.node_id = n.id WHERE  ( role_id = ".$_SESSION['roleid']." ) AND  a.pid = 1 and n.display=1  LIMIT 1");
            if ($first_root_node) 
            {
                $pid = $first_root_node[0]['node_id'];
                $this->assign('title', $first_root_node[0]['title']);
            }

            $where['pid']        = $pid;
            $where['level']        = 2;
            
            //获取可访问的节点
            $access_list = M('access')->Distinct(true)->field('node_id')->where(array('role_id' => $_SESSION['roleid'], 'pid'=>$pid))->select();
            if ($access_list) 
            {
                $node_arr = array();
                foreach ($access_list as $vo) 
                {
                    array_push($node_arr, ''.$vo['node_id']);
                }
                $where['id'] = array('in', $node_arr); 
            }


            $nav = M('node')->where($where)->order($order)->select();
        }
        else 
        {
            $pid = intval($_GET['pid'], 0);
            $access_list = M('access')->Distinct(true)->field('node_id')->where(array('role_id' => $_SESSION['roleid'], 'pid'=>$pid))->select();
            if ($access_list != null) 
            {
                $node_arr = array();
                foreach ($access_list as $vo) 
                {
                    array_push($node_arr, ''.$vo['node_id']);
                }
                $where['id'] = array('in', $node_arr); 
            }
            $where['pid']        = $pid;
            $where['level']        = intval($_GET['level'], 0);

            $nav = M('node')->where($where)->order($order)->select();
            $this->assign('title', htmlspecialchars_decode($_GET['title']));
        }
        $this->assign('nav',$nav);
        $this->display();
    }
    
    public function main()
    {
        $user_name = session('username');
        if (!empty($user_name)) 
        {
            $sql = "select u.username, r.name as role_name, u.balance,u.last_login_ip,u.last_login_time,u.status from tp_user as u left join tp_role as r on u.role = r.id where u.username = '$user_name'";
            $Model = new Model();
            $user = $Model->query($sql);
            $this->assign('user', $user[0]);
        }
        if(session(C('ADMIN_AUTH_KEY')) == true) {
            $this->assign('isAdmin', 1);
        }else {
            $this->assign('isAdmin', 0);
        }
        $this->display();
    }
}
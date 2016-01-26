<?php

header("Content-Type: text/html; charset=UTF-8");
define('SCRIPT_ROOT',  dirname(__FILE__).'/emay/');
require_once SCRIPT_ROOT.'/include/Client.php';

class SmsClient{
    
    public function __construct()
    {
        $this->client = new Client(
                $this->gwUrl,$this->serialNumber,$this->password,$this->sessionKey,
                $this->proxyhost,$this->proxyport,$this->proxyusername,
                $this->proxypassword,$this->connectTimeOut,$this->readTimeOut);
        $this->client->setOutgoingEncoding("UTF-8");
        //Thou shalt not construct that which is unconstructable!
    }
    
    /**
     * 网关地址 http://sdkhttp.eucp.b2m.cn/sdk/SDKService?wsdl
     */	
    private $gwUrl = 'http://sdkhttp.eucp.b2m.cn/sdk/SDKService?wsdl';


    /**
     * 序列号,请通过亿美销售人员获取
     */
    private $serialNumber = '';

    /**
     * 密码,请通过亿美销售人员获取
     */
    private $password = '';

    /**
     * 登录后所持有的SESSION KEY，即可通过login方法时创建
     */
    private $sessionKey = '880900';

    /**
     * 连接超时时间，单位为秒
     */
    private $connectTimeOut = 2;

    /**
     * 远程信息读取超时时间，单位为秒
     */ 
    private $readTimeOut = 10;

    private $proxyhost = false;
    private $proxyport = false;
    private $proxyusername = false;
    private $proxypassword = false; 

    private $client;
    
    


//----------------------------------------------------------------------
// 注: 
// 1. 下面是各接口的使用用例，Client.php 还有每一个接口更详细的参数说明
// 2. 凡是返回 $statusCode 的, 都是相关操作的状态码
// 3. 由于php是弱类型语言，当服务端没返回时，也会等同认为 $statusCode=='0', 所以在判断时应该使用 if ($statusCode!=null && $statusCode==0) 
//----------------------------------------------------------------------


    /**
     * 短信充值 用例
     */
    function chargeUp($cardId, $cardPass)
    {
            /**
             * $cardId [充值卡卡号]
             * $cardPass [密码]
             * 
             * 请通过亿美销售人员获取 [充值卡卡号]长度为20内 [密码]长度为6
             * 
             */
            $statusCode = $this->client->chargeUp($cardId,$cardPass);
            return $statusCode;
    }


    /**
     * 查询单条费用 用例
     */
    function getEachFee()
    {
            $fee = $this->client->getEachFee();
            return $fee;
    }


    /**
     * 短信发送 用例
     */
    function sendSMS($mobiles,$content,$smsId)
    {
        /**
         * 下面的代码将发送内容为 test 给 159xxxxxxxx 和 159xxxxxxxx
         * $this->client->sendSMS还有更多可用参数，请参考 Client.php
         */
        $statusCode = $this->client->sendSMS($mobiles,$content,'', '', 'UTF-8',5, $smsId);
        return $statusCode;
    }

    /**
     * 余额查询 用例
     */
    function getBalance()
    {
            $balance = $this->client->getBalance();
            return $balance;
    }
    

    /**
     * 接口调用错误查看 用例
     */
    function chkError()
    {
        $err = $this->client->getError();
        return $err;
    }

    

    /**
     * 获取版本号 用例
     */
    function getVersion()
    {
            return $this->client->getVersion();

    }

    
    /**
     * 登录 用例
     */
    function login()
    {
            /**
             * 下面的操作是产生随机6位数 session key
             * 注意: 如果要更换新的session key，则必须要求先成功执行 logout(注销操作)后才能更换
             * 我们建议 sesson key不用常变
             */
            $statusCode = $this->client->login();
            return $statusCode;

    }

    /**
     * 注销登录 用例
     */
    function logout()
    {
        $statusCode = $this->client->logout();
        return $statusCode;
    }
    
    /**
     * 企业注册 用例
     */
    function registDetailInfo()
    {
            $eName = "领众科技";
            $linkMan = "潘俊华";
            $phoneNum = "0755-36989386";
            $mobile = "18576431106";
            $email = "jikang@lingzhtech.com";
            $fax = "0755-36989386";
            $address = "深圳白石洲国际市长交流中心1901";
            $postcode = "111111";

            /**
             * 企业注册  [邮政编码]长度为6 其它参数长度为20以内
             * 
             * @param string $eName 	企业名称
             * @param string $linkMan 	联系人姓名
             * @param string $phoneNum 	联系电话
             * @param string $mobile 	联系手机号码
             * @param string $email 	联系电子邮件
             * @param string $fax 		传真号码
             * @param string $address 	联系地址
             * @param string $postcode  邮政编码
             * 
             * @return int 操作结果状态码
             * 
             */
            $statusCode = $this->client->registDetailInfo($eName,$linkMan,$phoneNum,$mobile,$email,$fax,$address,$postcode);
            return $statusCode;

    }
    
    
       /**
     * 更新密码 用例
     */
    function updatePassword($pwd)
    {

            /**
             * [密码]长度为6
             * 
             * 如下面的例子是将密码修改成: 654321
             */
            $statusCode = $this->client->updatePassword($pwd);
            return $statusCode;
    }
}

?>

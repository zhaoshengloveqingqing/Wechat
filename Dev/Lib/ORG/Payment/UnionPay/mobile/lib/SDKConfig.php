<?php
	// ######(以下配置为PM环境：入网测试环境用，生产环境配置见文档说明)#######
	
	// cvn2加密 1：加密 0:不加密
	define('SDK_CVN2_ENC',  0);
	// 有效期加密 1:加密 0:不加密
	define('SDK_DATE_ENC',  0);
	// 卡号加密 1：加密 0:不加密
	define('SDK_PAN_ENC',  0);;
	
	// 签名证书路径
	//define('SDK_SIGN_CERT_PATH',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/test/700000000000001.pfx');
	define('SDK_SIGN_CERT_PATH',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/pinet_unionpay.pfx');
	
	// 签名证书密码
	 //define('SDK_SIGN_CERT_PWD',  '000000');
	 define('SDK_SIGN_CERT_PWD',  'pinet');
	
	// 验签证书
	//define('SDK_VERIFY_CERT_PATH',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/test/700000000000001.cer');
	define('SDK_VERIFY_CERT_PATH',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/UPOP_VERIFY.cer');
	
	// 密码加密证书
	//define('SDK_ENCRYPT_CERT_PATH',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/test/encrypt.cer');
	define('SDK_ENCRYPT_CERT_PATH',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/RSA2048_PROD_index_22.cer');
	
	// 验签证书路径
	//define('SDK_VERIFY_CERT_DIR',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/test/');
	define('SDK_VERIFY_CERT_DIR',  LIB_PATH.'ORG/Payment/UnionPay/mobile/certs/');
	
	// 前台请求地址
	//define('SDK_FRONT_TRANS_URL',  'https://101.231.204.80:5000/gateway/api/frontTransReq.do');
	define('SDK_FRONT_TRANS_URL',  'https://gateway.95516.com/gateway/api/frontTransReq.do');
	
	// 后台请求地址
	//define('SDK_BACK_TRANS_URL',  'https://101.231.204.80:5000/gateway/api/backTransReq.do');
	define('SDK_BACK_TRANS_URL',  'https://gateway.95516.com/gateway/api/backTransReq.do');
	
	// 批量交易
	//define('SDK_BATCH_TRANS_URL',  'https://101.231.204.80:5000/gateway/api/batchTrans.do');
	define('SDK_BATCH_TRANS_URL',  'https://gateway.95516.com/gateway/api/batchTrans.do');
	
	//单笔查询请求地址
	//define('SDK_SINGLE_QUERY_URL',  'https://101.231.204.80:5000/gateway/api/backTransReq.do');
	define('SDK_SINGLE_QUERY_URL',  'https://gateway.95516.com/gateway/api/backTransReq.do');
	
	//文件传输请求地址
	//define('SDK_FILE_QUERY_URL',  'https://101.231.204.80:9080/');
	define('SDK_FILE_QUERY_URL',  'https://filedownload.95516.com/');
	
	//有卡交易地址
	//define('SDK_CARD_REQUEST_URL',  'https://101.231.204.80:5000/gateway/api/cardTransReq.do');
	define('SDK_CARD_REQUEST_URL',  'https://gateway.95516.com/gateway/api/cardTransReq.do');
	
	//App交易地址
	//define('SDK_APP_REQUEST_URL',  'https://101.231.204.80:5000/gateway/api/appTransReq.do');
	define('SDK_APP_REQUEST_URL',  'https://gateway.95516.com/gateway/api/appTransReq.do');
	
	// 前台通知地址 (商户自行配置通知地址)
	define('SDK_FRONT_NOTIFY_URL',  C('site_url').'/index.php/unionpay/front_notify');
	// 后台通知地址 (商户自行配置通知地址)
	define('SDK_BACK_NOTIFY_URL',  C('site_url').'/index.php/unionpay/back_notify');
	
	//文件下载目录 
	define('SDK_FILE_DOWN_PATH',  RUNTIME_PATH.'unionpay/file');
	
	//日志 目录 
	define('SDK_LOG_FILE_PATH',  RUNTIME_PATH.'unionpay/logs');
	
	//日志级别
	define('SDK_LOG_LEVEL',  'INFO');
?>
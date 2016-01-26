<?php
/**
 *网站路由配置
 *@package YiCms
 *@author YiCms
 **/
return array(
	/*路由设置*/
	'URL_MODEL' 			=>	0,				//URL访问模式
	'URL_ROUTER_ON'   		=> true, 			//开启路由
	'URL_HTML_SUFFIX'		=> 'shtml',			//伪静态后缀
	'URL_ROUTE_RULES' 		=>  array( 			//定义路由规则
		'api/:token'        => 'Weixin/Weixin/index',
		'show/:token'       => 'Home/Adma/index',
		'upload/image'        => 'User/UploadImg/upload',
		'alipay/return_url'        => 'Wap/ShopPay/return_url',
		'alipay/notify'        => 'Wap/ShopPay/notify',
                'merchantpay/return_url'        => 'User/MerchantPay/m_alipay_return',
				'merchantpay/notify'        => 'User/MerchantPay/m_alipay_notify',
            
                // 微信支付
                'wxpay/pay'        => 'Wap/Shop/wxpay', // 支付授权页
                'wxpay/notify'        => 'Wap/ShopWxpay/notify', // 支付通知页
                'wxpay/alarm'        => 'Wap/ShopWxpay/alarm', // 支付报警页面
                'wxpay/feedback'        => 'Wap/ShopWxpay/feedback', // 用户维权页面
                
                // 财付通
                'cftpay/pay'        => 'Wap/Shop/cftpay', // 发起支付请求页面
                'cftpay/notify'        => 'Wap/ShopCftPay/notify', // 支付通知页
                'cftpay/callback'        => 'Wap/ShopCftPay/callback', // 支付callback页面
            	
				//翼支付
				'wingpay/pay'  => 'Wap/Shop/wingpay',
				'wingpay/notify'  => 'Wap/Shop/wingpay_notify',

				//银联支付
				'unionpay/pay'  => 'Wap/Shop/unionpay',
				'unionpay/notify'  => 'Wap/Shop/unionpay_notify',

				'unionpay/front_notify'  => 'Wap/Shop/front_notify',
				'unionpay/back_notify'  => 'Wap/Shop/back_notify',
				

				
            
                'photo/:token'        => 'Wap/Photo/index',
                '3d/gm/:token/:galleryid\d' => 'Wap/Panorama/gallerymeta',
                '3d/p/:token/:galleryid\d/:panoramaid\d' => 'Wap/Panorama/panorama',
				'3d/po/:token/:panoramaid\d' => 'Wap/Panorama/panorama_one',
                '3d/g/:token/:galleryid\d' => 'Wap/Panorama/gallery',
                '3d/g/:token' => 'Wap/Panorama/index',
                '3d/pm/:panoramaid\d/:token' => 'Wap/Panorama/meta',
	),
);

<?php

/* 
 * 微网站模版：首页，栏目，详细页
 */
return array(
    'web_homepage_tmpl' =>
        array(
        13 => array(
            'id' => 13,
            'view' => 'index_hotel',
            'name' => '通用模版1',
            'preview_img' => '/themes/a/images/previews/index_hotel_preview.png',
            'tips' => '左右双栏模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图片建议使用310*122或近似等比例图片；可自定义背景色、栏目背景色以及字体颜色。',
            'free' => 1,
            'show' => 1,
            'enabled' => 1,
            //支持自定义背景色
            'support_diy_bg_color'          => 1,
            'default_bg_color'              => '#eee',
            //支持自定义背景图片
            //'support_diy_bg_pic' => 0,
            //支持自定义首页栏目背景色以及字体色
            'support_diy_classify_color'    => 1,
            'default_classify_bg_color'     => '#fff',
            'default_classify_font_color'   => '#444',
            ),

        1 => array(
            'id' => 1,
            'view' => 'index_common',
            'name' => '通用模板2',
            'preview_img' => '/themes/a/images/previews/index_common2_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 1,
            'show' => 1,
            'enabled' => 1,
            ),
            
             14 => array(
            'id' => 14,
            'view' => 'index_diet',
            'name' => '甜品/餐饮一',
            'preview_img' => '/themes/a/images/previews/index_diet_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 1,
            'show' => 1,
            'enabled' => 1,
            ),
            
            23 => array(
            'id' => 23,
            'view' => 'index_diet_tianpin',
            'name' => '甜品/餐饮二',
            'preview_img' => '/themes/a/images/previews/index_diet_tianpin.png',
            'tips' => '左右图文式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片， 如使用正方形图片会使得页面不美观； 分类图片建议使用106px*106px或近似等比例图片，请不要使用高度大于或接近宽度的图片；左侧分类栏目显示栏目标题及栏目简介。',
            'free' => 1,
            'show' => 1,
            'enabled' => 1,
             //支持自定义背景色
            'support_diy_bg_color'          => 1,
            'default_bg_color'              => '#E1E0DE',
            //支持自定义背景图片
            //'support_diy_bg_pic' => 0,
            //支持自定义首页栏目背景色以及字体色
            'support_diy_classify_color'    => 1,
            'default_classify_bg_color'     => '#f1f1f1',
            'default_classify_font_color'   => '#000',
            ),

            22 => array(
            'id' => 22,
            'view' => 'index_brand',
            'name' => '品牌/餐饮',
            'preview_img' => '/themes/a/images/previews/index_brand.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请上传1:1比例的图片或选择系统图标,此模板可自定背景色、栏目背景色以及字体颜色。',
            'free' => 1,
            'show' => 1,
            'enabled' => 1,
            //支持自定义背景色
            'support_diy_bg_color'          => 1,
            'default_bg_color'              => '#E1E0DE',
            //支持自定义背景图片
            //'support_diy_bg_pic' => 0,
            //支持自定义首页栏目背景色以及字体色
            'support_diy_classify_color'    => 1,
            'default_classify_bg_color'     => '#3B9FE6',
            'default_classify_font_color'   => '#000',
            ),
            
            21 => array(
            'id' => 21,
            'view' => 'index_wedding',
            'name' => '婚庆/婚礼',
            'preview_img' => '/themes/a/images/previews/index_wedding.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),

        5 => array(
            'id' => 5,
            'view' => 'index_coffee',
            'name' => '餐饮/咖啡/茶楼',
            'preview_img' => '/themes/a/images/previews/index_cafe_eat_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),

        6 => array(
            'id' => 6,
            'view' => 'index_edu',
            'name' => '教育/培训/文化',
            'preview_img' => '/themes/a/images/previews/index_education_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),

        10 => array(
            'id' => 10,
            'view' => 'index_ktv',
            'name' => '酒店/桑拿/KTV一',
            'preview_img' => '/themes/a/images/previews/index_ktv_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),
			
			30 => array(
            'id' => 30,
            'view' => 'index_window',
            'name' => '酒店/桑拿/KTV二',
            'preview_img' => '/themes/a/images/previews/index_saunaa_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),

        9 => array(
            'id' => 9,
            'view' => 'index_travel',
            'name' => '旅游/活动/拓展',
            'preview_img' => '/themes/a/images/previews/index_travel_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),

        7 => array(
            'id' => 7,
            'view' => 'index_trade',
            'name' => '电子商务/贸易',
            'preview_img' => '/themes/a/images/previews/index_ebusiness_clothes.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),
 


        3 => array(
            'id' => 3,
            'view' => 'index_car',
            'name' => '汽车4s行业',
            'preview_img' => '/themes/a/images/previews/index_car_preview.png',
            'tips' => '左右图文式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；    分类图片建议使用430*100或近似等比例图片，请不要使用高度大于或接近宽度的图片。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),

        4 => array(
            'id' => 4,
            'view' => 'index_corp',
            'name' => '公司/集团/会展',
            'preview_img' => '/themes/a/images/previews/index_hotel_preview.png',
            'tips' => '',
            'free' => 0,
            'show' => 0,
            'enabled' => 0,
            ),

        100 => array(
            'id' => 100,
            'view' => 'index_lingzhtech',
            'name' => '领众科技专属模板',
            'preview_img' => '/themes/a/images/previews/index_lingzhtech_preview.png',
            'tips' => '',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            'exlusive' => array('lingzhtech','lingzhmedia'), //商户token，仅对该公众号显示
            ),


          15 => array(
            'id' => 15,
            'view' => 'index_sweet',
            'name' => '甜品/餐饮二',
            'preview_img' => '/themes/a/images/previews/index_sweet_preview.png',
            'tips' => '',
            'free' => 0,
            'show' => 0,
            'enabled' => 0,
            ),

           16 => array(
            'id' => 16,
            'view' => 'index_tourist',
            'name' => '旅游/酒店/机票预订一',
            'preview_img' => '/themes/a/images/previews/index_tourist_preview.png',
            'tips' => '',
            'free' => 0,
            'show' => 0,
            'enabled' => 0,
            ),

           19 => array(
            'id' => 19,
            'view' => 'index_mall',
            'name' => '购物/商城',
            'preview_img' => '/themes/a/images/previews/index_mall_preview.png',
            'tips' => '左右图文式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图片建议使用长2：宽1或近似等比例图片。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            //支持自定义背景色
            'support_diy_bg_color'          => 0,
            'default_bg_color'              => '#fff',
            //支持自定义背景图片
            //'support_diy_bg_pic' => 1,
            //支持自定义首页栏目背景色以及字体色
            'support_diy_classify_color'    => 1,
            'default_classify_bg_color'     => '#245889',
            'default_classify_font_color'   => '#fff',
            ),
        17 => array(
            'id' => 17,
            'view' => 'index_book',
            'name' => '旅游/酒店/机票预订',
            'preview_img' => '/themes/a/images/previews/index_book_preview.png',
            'tips' => '左右图文式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图片建议使用320px*250px或近似等比例图片，请不要使用高度大于或接近宽度的图片；左侧分类栏目显示栏目标题及对应文章标题（文章标题最多显示四个）。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),

       /* 18 => array(
            'id' => 18,
            'view' => 'index_bar',
            'name' => '酒吧/ktv',
            'preview_img' => '/themes/a/images/previews/index_bar_preview.png',
            'tips' => '',
            'free' => 0,
            'show' => 1,
            'enabled' => 0,
            ),*/

        20 => array(
            'id' => 20,
            'view' => 'index_company',
            'name' => '公司/企业一',
            'preview_img' => '/themes/a/images/previews/index_company_preview.png',
            'tips' => '此模板需要上传背景图，尺寸为320px宽*480px高或等比例尺寸的背景图。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),      
            
        34 => array(
            'id' => 34,
            'view' => 'index_company',
            'name' => '公司/企业二',
            'preview_img' => '/themes/a/images/previews/index_enterprise_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),      
            
            11 => array(
            'id' => 11,
            'view' => 'index_coffe_preview',
            'name' => '餐饮/咖啡',
            'preview_img' => '/themes/a/images/previews/index_coffe_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 0,
            ),
            
             24 => array(
            'id' => 24,
            'view' => 'index_travel_reservations',
            'name' => '旅行/预定',
            'preview_img' => '/themes/a/images/previews/index_travel_reservations.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),


        25 => array(
            'id' => 25,
            'view' => 'index_window',
            'name' => '展示/门窗/家居一',
            'preview_img' => '/themes/a/images/previews/index_windows_doors.png',
            'tips' => '左右图文式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；栏目图片尺寸宽为3,高为2的比例效果最佳。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),
			
			32=> array(
            'id' => 32,
            'view' => 'index_window',
            'name' => '展示/门窗/家居二',
            'preview_img' => '/themes/a/images/previews/index_material_science.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 1,
            'enabled' => 1,
            ),			
            
        28 => array(
            'id' => 28,
            'view' => 'index_window',
            'name' => '桑拿/瘦身/足浴',
            'preview_img' => '/themes/a/images/previews/index_sauna_preview.png',
            'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
            'free' => 0,
            'show' => 0,
            'enabled' => 0,
            ),

		
	33=> array(
            'id' => 33,
            'view' => 'index_house',
            'name' => '展示/门窗/家居三',
            'preview_img' => '/themes/a/images/previews/index_real_estate.png',
            'tips' => '此模板需要上传背景图，尺寸为320px宽*480px高或等比例尺寸的背景图，分类图标请选择系统图标或上传正方形透明底图标；可自定义栏目背景色以及字体颜色。',
            'free' => 1,
            'show' => 1,
            'enabled' => 1,
			//支持自定义背景色
            'support_diy_bg_color'          => 1,
            'default_bg_color'              => '#eee',
            //支持自定义背景图片
            //'support_diy_bg_pic' => 0,
            //支持自定义首页栏目背景色以及字体色
            'support_diy_classify_color'    => 1,
            'default_classify_bg_color'     => '#fff',
            'default_classify_font_color'   => '#444',
            ),
									
	30 => array(
			'id' => 30,
			'view' => 'index_window_n',
			'name' => '房型展示',
			'preview_img' => '/themes/a/images/previews/index_material_science.png',
			'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
			'free' => 1,
			'show' => 1,
			'enabled' => 1,
			//支持自定义背景色
            'support_diy_bg_color'          => 1,
            'default_bg_color'              => '#eee',
            //支持自定义背景图片
            //'support_diy_bg_pic' => 0,
            //支持自定义首页栏目背景色以及字体色
            'support_diy_classify_color'    => 1,
            'default_classify_bg_color'     => '#fff',
            'default_classify_font_color'   => '#444',
			),
		
		31 => array(
			'id' => 31,
			'view' => 'index_indoor',
			'name' => '地产/住房/房产',
			'preview_img' => '/themes/a/images/previews/index_home_furnishing.png',
			'tips' => '此模板需要上传背景图，尺寸为320px宽*480px高或等比例尺寸的背景图，分类图标请选择系统图标或上传正方形透明底图标；可自定义栏目背景色以及字体颜色。',
			'free' => 1,
			'show' => 1,
			'enabled' => 1,
			//支持自定义背景色
            'support_diy_bg_color'          => 1,
            'default_bg_color'              => '#eee',
            //支持自定义背景图片
            //'support_diy_bg_pic' => 0,
            //支持自定义首页栏目背景色以及字体色
            'support_diy_classify_color'    => 1,
            'default_classify_bg_color'     => '#fff',
            'default_classify_font_color'   => '#444',
			),
		34 => array(
			'id' => 34,
			'view' => 'index_massage',
			'name' => '休闲场所',
			'preview_img' => '/themes/a/images/previews/index_saunaa_preview.png',
			'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
			'free' => 1,
			'show' => 1,
			'enabled' => 1,
			),
		39 => array(
			'id' => 39,
			'view' => 'index_news',
			'name' => '传媒',
			'preview_img' => '/themes/a/images/previews/index_news.png',
			'tips' => '图标式模板，顶部幻灯片尺寸为宽640像素，高425像素或近似等比例图片，如使用正方形图片会使得页面不美观；分类图标请选择系统图标或上传正方形透明底图标。',
			'free' => 1,
			'show' => 1,
			'enabled' => 1,
			),
		
    ),


    'web_classify_tmpl' => array(
        6 => array(
            'id'        => 6, 
            'file_name' => 'list_pic_gray',   
            'tmpl_name' => '图文列表型-通用',
            'is_default'=> 1,
             'show' => 1,
            'preview_img' => '/images/wxsite/list1_gray.png'
            ),

        5 => array(
            'id' => 5, 
            'file_name' => 'list_pic',        
            'tmpl_name'=>'图文列表型-蓝',
             'show' => 1,
            'preview_img' => '/images/wxsite/list1.png'
            ),
        
        7 => array(
            'id' => 7, 
            'file_name' => 'list_pic_black',  
            'tmpl_name'=>'图文列表型-黑',
             'show' => 1,
            'preview_img' => '/images/wxsite/list1_black.png'
            ),
        8 => array(
            'id' => 8, 
            'file_name' => 'list_pic_kd',     
            'tmpl_name'=>'图文列表型-墨绿',
             'show' => 1,
            'preview_img' => '/images/wxsite/list1_green.png'
            ),
        9 => array(
            'id' => 9, 
            'file_name' => 'list_pic_brown',  
            'tmpl_name'=>'图文列表型-褐色',
             'show' => 1,
            'preview_img' => '/images/wxsite/list1_brown.png'
            ),

        4 => array(
            'id' => 4, 
            'file_name' => 'list_text',       
            'tmpl_name'=>'纯文字型',
             'show' => 1,
            'preview_img' => '/images/wxsite/list4.png'
            ),
        1 => array(
            'id' => 1, 
            'file_name' => 'list_preview',    
            'tmpl_name'=>'内容预览型',
             'show' => 1,
            'preview_img' => '/images/wxsite/list_preview_preview.png'
            ),
        
        10 => array(
            'id' =>10, 
            'file_name' => 'list_pic_brown_simple',
            'tmpl_name'=>'文字列表型',
             'show' => 1,
            'preview_img' => '/images/wxsite/list1_pic_browm.png'
            ),
        11 => array(
            'id' =>11, 
            'file_name' => 'list_pic_silver',
            'tmpl_name'=>'图文列表-图片展示型',
             'show' => 1,
            'preview_img' => '/images/wxsite/list1_pic_silver.png'
            ),
    ),
   
);


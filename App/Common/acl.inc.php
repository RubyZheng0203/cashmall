<?php
/**权限配置文件
 * array(菜单名，菜单url参数，是否显示)
 * $acl_inc[$i]['low_leve']['global']  global是model
 * 每个action前必须添加eqaction_前缀'eqaction_websetting'  => 'at1','at1'表示唯一标志,可独自命名,eqaction_后面跟的action必须统一小写
 */
 
$acl_inc =  array();
$i=0;

//全局设置
$acl_inc[$i]['low_title'][]         = '全局设置';
$acl_inc[$i]['low_leve']['global']  = array(
    "网站设置"       => array(
                                "列表"                  => 'at1',
    						    "增加"                  => 'at2',
    						    "删除"                  => 'at3',
    						    "修改"                  => 'at4',
    						),
    "友情链接"       => array(
                                "列表"                  => 'at5',
    						    "增加"                  => 'at6',
    						    "删除"                  => 'at7',
    						    "修改"                  => 'at8',
    						    "搜索"                  => 'att8',
                            ),
    "所有缓存"       => array(
					            "清除"                  => 'at22',
				             ),
    "后台操作日志"    => array(
					            "列表"                  => 'at23',
					            "删除"                  => 'at24',
					            "删除一月前操作日志"      => 'at25',
				            ),
    "data"          => array(
        						'eqaction_websetting'  => 'at1',
        						'eqaction_doadd'       => 'at2',
        						'eqaction_dodelweb'    => 'at3',
        						'eqaction_doedit'      => 'at4',
        						'eqaction_cleanall'    => 'at22',
        						'eqaction_adminlog'    => 'at23',
        						'eqaction_dodeletelog' => 'at24',
        						'eqaction_dodellogone' => 'at25',//删除近期一个月内的后台操作日志
        					)
    );
    
$i++;
$acl_inc[$i]['low_leve']['ad']= array( 
                                "广告管理" =>array(
                                "列表" 		=> 'ad1',
                                "增加" 		=> 'ad2',
                                "删除" 		=> 'ad4',
                                "修改" 		=> 'ad3',
                            ),
                        "data" => array(
                                //网站设置
                                'eqaction_index'        => 'ad1',
                                'eqaction_add'          => 'ad2',
                                'eqaction_doadd'        => 'ad2',
                                'eqaction_edit'         => 'ad3',
                                'eqaction_doedit'       => 'ad3',
                                'eqaction_swfupload'    => 'ad3',
                                'eqaction_dodel'        => 'ad4',
                                )
        );

//权限管理
$i++;
$acl_inc[$i]['low_title'][]     = '权限管理';
$acl_inc[$i]['low_leve']['acl'] = array( 
    "权限管理"  => array(
    				       "列表" 		          => 'at73',
    					   "增加" 		          => 'at74',
    					   "删除" 		          => 'at75',
    					   "修改" 		          => 'at76',
    				   ),
    "data"     => array(
                	       'eqaction_index'      => 'at73',
                		   'eqaction_doadd'      => 'at74',
                		   'eqaction_add'        => 'at74',
                		   'eqaction_dodelete'   => 'at75',
                		   'eqaction_doedit'     => 'at76',
                		   'eqaction_edit'   	 => 'at76',
                       )
    );
    
//管理员管理
$i++;
$acl_inc[$i]['low_title'][]             = '管理员管理';
$acl_inc[$i]['low_leve']['adminuser']   = array( 
    "管理员管理" => array(
						    "列表" 		=> 'at77',
						    "增加" 		=> 'at78',
						    "删除" 		=> 'at79',
						    "上传头像"	=> 'at99',
						    "修改" 		=> 'at80',
						),
   	  "data"    => array(
                   		 //权限管理
                		    'eqaction_index'                => 'at77',
                		    'eqaction_dodelete'             => 'at79',
                		    'eqaction_header'               => 'at99',
                		    'eqaction_memberheaderuplad'    => 'at99',
		                    'eqaction_addadmin'             => array(
		                                                                'at78' => array(//增加
						                                                                   'POST' => array(
                                							                                                  "uid" =>'G_NOTSET',
                                							                                              ),
                                						                               ),	
						                                                'at80' => array(//修改
							                                                               'POST' => array(
								                                                                              "uid"=>'G_ISSET',
							                                                                              ),
						                                                               ),	
                                		                            ),
                        )
    );

$i++;
$acl_inc[$i]['low_title'][]         = '图片上传';
$acl_inc[$i]['low_leve']['kissy']   = array(
    "图片上传" => array(
                          "图片上传" 		=> 'at81',
                	  ),
    "data"    => array(
   		               //权限管理
		                  'eqaction_index'  => 'at81',
	                  )
    );
    

$i++;
$acl_inc[$i]['low_title'][]          = '授信通过率';
$acl_inc[$i]['low_leve']['credit']   = array(
    "授信通过率" => array(
        "列表" 		=> 'cr1',
        "添加" 		=> 'cr2',
        "修改" 		=> 'cr3',
        "删除" 		=> 'cr4',
    ),
    "data"    => array(
        //权限管理
        'eqaction_index'    => 'cr1',
        'eqaction_add'      => 'cr2',
        'eqaction_doadd'    => 'cr2',
        'eqaction_edit'     => 'cr3',
        'eqaction_doedit'   => 'cr3',
        'eqaction_del'      => 'cr4',
    )
);

$i++;
$acl_inc[$i]['low_title'][]         = '扩展管理';
$acl_inc[$i]['low_leve']['scan']    = array( 
    "安全检测" => array(
                          "安全检测" => 'scan1',
                      ),
    "data"    => array(
                          //权限管理
                          'eqaction_index'        => 'scan1',
                          'eqaction_scancom'      => 'scan1',
                          'eqaction_updateconfig' => 'scan1',
                          'eqaction_filefilter'   => 'scan1',
                          'eqaction_filefunc'     => 'scan1',
                          'eqaction_filecode'     => 'scan1',
                          'eqaction_scanreport'   => 'scan1',
                          'eqaction_view'         => 'scan1',
                        )
    );
    
$acl_inc[$i]['low_leve']['mfields'] = array( 
    "文件管理" =>array(
					     "文件管理" 		          => 'at82',
					     "空间检查"		          =>'at83',
					 ),
    "data" => array(
                	   //文件管理
                       'eqaction_index'           => 'at82',
                	   'eqaction_checksize'       => 'at83',
                   )
    );

$acl_inc[$i]['low_leve']['bconfig'] = array(
    "业务参数管理" => array(
						      "查看" 		      => 'fb1',
						      "修改" 		      => 'fb2',
				          ),
    "data"       => array(
	                         //网站设置
	                         'eqaction_index'     => 'fb1',
	                         'eqaction_save'      => 'fb2',
                          )
    );
$acl_inc[$i]['low_leve']['leve']   = array( 
    "信用级别管理" => array(
							 "查看" 		=> 'jb1',
							 "修改" 		=> 'jb2',
						  ),
    "投资级别管理" => array(
                    	     "查看" 		=> 'jb3',
                    	     "修改" 		=> 'jb4',
                    	 ),
    "data"       => array(
                   		     //网站设置
                		     'eqaction_index'         => 'jb1',
                		     'eqaction_save'          => 'jb2',
                		     'eqaction_invest'        => 'jb3',
                		     'eqaction_investsave'    => 'jb4',
	                     )
    );
    
$acl_inc[$i]['low_leve']['age'] = array( 
    "会员年龄别称" => array(
						     "查看" 		          => 'bc1',
						     "修改" 		          => 'bc2',
						 ),
    "data"       => array(
                       	     //网站设置
                    		 'eqaction_index'     => 'bc1',
                    		 'eqaction_save'      => 'bc2',
	                     )
    );


$acl_inc[$i]['low_leve']['msgonline'] = array( 
    "通知信息接口管理" => array(
							     "查看" 		=> 'jk3',
							     "修改" 		=> 'jk4',
							 ),
	"通知信息模板管理" => array(
							     "查看" 		=> 'jk5',
							     "修改" 		=> 'jk6',
						     ),
    "data"           => array(
                    	   		 //网站设置
                    		     'eqaction_index'           => 'jk3',
                    			 'eqaction_save'            => 'jk4',
                    			 'eqaction_templet'         => 'jk5',
                    			 'eqaction_templetsave'     => 'jk6',
                    	         'eqaction_oil'             => 'jk3',
                    	         'eqaction_oilsave'         => 'jk4',
                                 'eqaction_traffic'         => 'jk3',
                                 'eqaction_trafficsave'     => 'jk4',
                    	         'eqaction_cartraffic'      => 'jk3',
                    	         'eqaction_cartrafficsave'  => 'jk4',
                    		  )
    );
    
$i++;
$acl_inc[$i]['low_title'][] = '会员管理';
$acl_inc[$i]['low_leve']['members']= array( "会员列表"   =>array(
                                            "列表" 		=> 'mall1',
                                            "查看" 	    => 'mall2',
                                            "导出" 	    => 'mall3',
                                            "黑名单" 	=> 'mb1',
                                            "认证" 	    => 'mall4',
                                        ),
                                            "借款积分" =>array(
                                            "列表" 		=> 'mi1',
                                            "查看" 		=> 'mi2',
                                        ),
                                            "设备指纹" =>array(
                                            "列表" 		=> 'me1',
                                            "查看" 		=> 'me2',
                                        ),
                                            "统计报表" =>array(
                                                "查看" 		=> 'list1',
                                                "导出" 		=> 'list2',
                                                "进度查看" 	=> 'list3',
                                            ),
                                        "data" => array(
                                        //网站设置
                                        'eqaction_index'        => 'mall1',
                                        'eqaction_mb_export'    => 'mall3',
                                        'eqaction_member'       => 'mall2',
                                        'eqaction_black'        => 'mb1',
                                        'eqaction_doblack'      => 'mb1',
                                        'eqaction_integral'     => 'mi1',
                                        'eqaction_event'        => 'me1',
                                        'eqaction_eventview'    => 'me2',
                                        'eqaction_dacount'      => 'list1',
                                        'eqaction_daaddress'    => 'list1',
                                        'eqaction_dasex'        => 'list1',
                                        'eqaction_daage'        => 'list1',
                                        'eqaction_apply'        => 'list3',
                                        'eqaction_doapply'      => 'list3',
                                        'eqaction_unaccount'    => 'mall3',
                                        'eqaction_unbindingaccount'    => 'mall4',
                                            
                                    )
);

$i++;
$acl_inc[$i]['low_title'][] = '借款优惠券';
$acl_inc[$i]['low_leve']['coupon']= array( "优惠券"   =>array(
                                           "列表" 	   => 'cou1',
                                           "增加" 	   => 'cou2',
                                           "删除" 	   => 'cou3',
                                           "修改" 	   => 'cou4',
                                        ),
                                        "data" => array(
                                            //网站设置
                                            'eqaction_index'            => 'cou1',
                                            'eqaction_send'             => 'cou2',
                                            'eqaction_dosend'           => 'cou2',
                                            'eqaction_deletecoupon'     => 'cou3',
                                            'eqaction_disabled'         => 'cou4',
                                        )
);


$i++;
$acl_inc[$i]['low_title'][] = '借款';
$acl_inc[$i]['low_leve']['item']= array( "借款产品" =>array(
                                        "列表" 		=> 'item1',
                                        "添加" 		=> 'item2',
                                        "编辑" 		=> 'item3',
                                        "删除" 		=> 'item4',
                                        ),
                                        "data" => array(
                                            //网站设置
                                            'eqaction_index'           => 'item1',
                                            'eqaction_itemsec'         => 'item1',
                                            'eqaction_changeon'        => 'item3',
                                            'eqaction_add'             => 'item2',
                                            'eqaction_doadd'           => 'item2',
                                            'eqaction_edit'            => 'item3',
                                            'eqaction_doedit'          => 'item3',
                                        )
);

$acl_inc[$i]['low_leve']['borrow']= array( "借款申请"   =>array(
                                            "列表" 	   => 'bow1',
                                            "增加" 	   => 'bow2',
                                            "删除" 	   => 'bow3',
                                            "修改" 	   => 'bow4',
                                            "初审审批"  => 'bow5',
                                            "签约审批"  => 'bow6',
                                            "放款审批"  => 'bow7',
                                            "逾期扣款"  => 'bow8',
                                            "财务分析"  => 'bow9',
                                            "已放款  "   => 'bow10',
                                            "支付平台交易记录 "   => 'bow11',
											"待还款线下申请" => 'bow12',
											"已逾期线下申请" => 'bow13',
                                            "借款导出"      => 'bow14',
                                            "借款优惠券"    => 'bow15'
    
                                        ),
                                        "data" => array(
                                            //网站设置
                                            'eqaction_index'            => 'bow1',
                                            'eqaction_approval'         => 'bow5',
                                            'eqaction_doapproval'       => 'bow5',
                                            'eqaction_signing'          => 'bow1',
                                            'eqaction_appsigning'       => 'bow6',
                                            'eqaction_dosigning'        => 'bow6',
                                            'eqaction_pending'          => 'bow7',
                                            'eqaction_apppending'       => 'bow7',
                                            'eqaction_dopending'        => 'bow7',
                                            'eqaction_cancled'          => 'bow1',
                                            'eqaction_pendng'           => 'bow1',
                                            'eqaction_dofirst'          => 'bow4',
                                            'eqaction_pendingddit'      => 'bow4',
                                            'eqaction_doPendingddit'    => 'bow4',
                                            'eqaction_doPending'        => 'bow1',
                                            'eqaction_notapply'         => 'bow1',
                                            'eqaction_firsttrial'       => 'bow1',
                                            'eqaction_zhima'            => 'bow1',
                                            'eqaction_bingbank'         => 'bow1',
                                            'eqaction_idverify'         => 'bow1',
                                            'eqaction_risk'             => 'bow1',
                                            'eqaction_signed'           => 'bow1',
                                            'eqaction_unidverify'       => 'bow1',
                                            'eqaction_pended'           => 'bow10',
                                            'eqaction_repayment'        => 'bow9',
                                            'eqaction_repaymented'      => 'bow9',
                                            'eqaction_due'              => 'bow9',
                                            'eqaction_cancle'           => 'bow4',
                                            'eqaction_docancle'         => 'bow4',
                                            'eqaction_cancel'           => 'bow4',
                                            'eqaction_dee'              => 'bow8',
                                            'eqaction_updatetime'       => 'bow8',
                                            'eqaction_tradeing'         => 'bow11',
                                            'eqaction_trades'           => 'bow11',
                                            'eqaction_tradessina'       => 'bow11',
                                            'eqaction_tradeshuichao'    => 'bow11',
                                            'eqaction_tradesno'         => 'bow11',
                                            'eqaction_tradessinano'     => 'bow11',
                                            'eqaction_tradeshuichaono'  => 'bow11',
											'eqaction_linedownapply'    => 'bow12',
											'eqaction_doapply'          => 'bow12',
											'eqaction_lineapply'        => 'bow13',
											'eqaction_dolineapply'      => 'bow13',
                                            'eqaction_repaymentexport'  => 'bow14',
                                            'eqaction_send'             => 'bow15',
                                            'eqaction_dosend'           => 'bow15',
                                            
                                        )
);

$acl_inc[$i]['low_leve']['huichaopay'] = array("汇潮支付"   =>array(
                                                                    "列表" 	   => 'bow1',
                                                                    "增加" 	   => 'bow2',
                                                                    "删除" 	   => 'bow3',
                                                                    "修改" 	   => 'bow4',
                                                                ),
                                                "data" => array(
                                                    //网站设置
                                                    'eqaction_index'            => 'bow1',
                                                    'eqaction_trades'           => 'bow1',
                                                    'eqaction_tradesno'         => 'bow1',
                                                )
);

$i++;
$acl_inc[$i]['low_title'][] = '借款统计';
$acl_inc[$i]['low_leve']['borrowda']= array( "报表统计"   =>array(
                                                                "查看" 	   => 'bda1',
                                                                "导出" 	   => 'bda2',
                                                            ),
                                            "data" => array(
                                                //网站设置
                                                'eqaction_daapply'            => 'bda1',
                                                'eqaction_daapplyad'          => 'bda1',
                                                'eqaction_daapplysex'         => 'bda1',
                                                'eqaction_daapplyage'         => 'bda1',
                                                'eqaction_daapproval'         => 'bda1',
                                                'eqaction_daapprovalad'       => 'bda1',
                                                'eqaction_daapprovalsex'      => 'bda1',
                                                'eqaction_daapprovalage'      => 'bda1',
                                                'eqaction_daloan'             => 'bda1',
                                                'eqaction_daloanad'           => 'bda1',
                                                'eqaction_daloansex'          => 'bda1',
                                                'eqaction_daloanage'          => 'bda1',
                                                'eqaction_dadue'              => 'bda1',
                                                'eqaction_daduead'            => 'bda1',
                                                'eqaction_daduesex'           => 'bda1',
                                                'eqaction_dadueage'           => 'bda1',
                                            )
);

$i++;
$acl_inc[$i]['low_title'][] = '逾期统计';
$acl_inc[$i]['low_leve']['collection']= array( "报表统计"   =>array(
                                                                "查看" 	   => 'cda1',
                                                                "导出" 	   => 'cda2',
                                                                "导出(联系人)" 	   => 'cda3',
                                                                "照片" 	   => 'cda4',
                                                            ),
                                                "data" => array(
                                                    //网站设置
                                                    'eqaction_index'             => 'cda1',
                                                    'eqaction_dueexport'         => 'cda2',
                                                    'eqaction_dueallexport'      => 'cda2',
                                                    'eqaction_duedetailexport'   => 'cda3',
                                                    'eqaction_getpic'            => 'cda4',
                                                    'eqaction_dopic'             => 'cda4',
                                                )
);


$i++;
$acl_inc[$i]['low_title'][] = '黑名单属性';
$acl_inc[$i]['low_leve']['black']= array( "设置" =>array(
                                            "列表" 		=> 'black1',
                                            "添加" 		=> 'black2',
                                            "编辑" 		=> 'black3',
                                            "删除" 		=> 'black4',
                                        ),
                                            "data" => array(
                                                //网站设置
                                                'eqaction_random'           => 'black1',
                                                'eqaction_deleterandom'     => 'black4',
                                                'eqaction_setup'            => 'black1',
                                                'eqaction_changestatus'     => 'black3',
                                                'eqaction_changeinterval'   => 'black3',
                                                'eqaction_changenumday'     => 'black3',
                                                'eqaction_changenum'        => 'black3',
                                            )
);

$i++;
$acl_inc[$i]['low_title'][] = 'SMS';
$acl_inc[$i]['low_leve']['message']= array( "SMS模板" =>array(
                                            "列表" 		=> 'sms1',
                                            "添加" 		=> 'sms2',
                                            "编辑" 		=> 'sms3',
                                            "删除" 		=> 'sms4',
                                            "发送" 		=> 'sms5',
                                            "设置" 		=> 'sms6',
                                        ),
                                        "data" => array(
                                            //网站设置
                                            'eqaction_index'        => 'sms6',
                                            'eqaction_templet'      => 'sms1',
                                            'eqaction_templetsave'  => 'sms3',
                                            'eqaction_send'         => 'sms5',
                                            'eqaction_dosend'       => 'sms5',
                                            'eqaction_save'         => 'sms6',
                                        )
);

$i++; 
$acl_inc[$i]['low_title'][] = '微信';
$acl_inc[$i]['low_leve']['wechat']= array( "微信模板" =>array(
                                            "列表" 		=> 'wxlist',
                                            "添加" 		=> 'wxadd',
                                            "编辑" 		=> 'wxedit',
                                            "删除" 		=> 'wxdel',
                                        ),
                                            "data" => array(
                                                //网站设置
                                                'eqaction_index'    => 'wxlist',
                                                'eqaction_add'      => 'wxadd',
                                                'eqaction_doadd'    => 'wxadd',
                                                'eqaction_edit'     => 'wxedit',
                                                'eqaction_doedit'   => 'wxedit',
                                                'eqaction_delete'   => 'wxdel',
                                            )
);

$i++;
$acl_inc[$i]['low_title'][] = 'App推送';
$acl_inc[$i]['low_leve']['apppush']= array( "App推送模板" =>array(
                                            "列表"        => 'txlist',
                                            "编辑"        => 'txedit',
                                        ),
                                            "data" => array(
                                                //网站设置
                                                'eqaction_index'    => 'txlist',
                                                'eqaction_save'     => 'txedit',
                                                'eqaction_edit'     => 'txlist',
                                                'eqaction_delete'     => 'txedit',
                                                'eqaction_send'     => 'txlist',
                                                'eqaction_dosend'     => 'txedit',
                                            )
);

$i++;
$acl_inc[$i]['low_title'][] = '线下还款';
$acl_inc[$i]['low_leve']['payoff']= array( "线下还款审核" =>array(
                                                            "列表" 		=> 'po1',
                                                            "审核" 		=> 'po2',
                                                        ),
                                                        "data" => array(
                                                            //网站设置
                                                            'eqaction_index'    => 'po1',
                                                            'eqaction_edit'     => 'po2',
                                                            'eqaction_doedit'   => 'po2',
															'eqaction_renewal_new' => 'po2',
                                                            'eqaction_updatemoney' => 'po2',
                                                            'eqaction_updateday'   => 'po2',
                                                        )
);

$i++;
$acl_inc[$i]['low_title'][] = '福米上标';
$acl_inc[$i]['low_leve']['bid']= array( "福米上标" =>array(
                                                        "列表" 		=> 'bid1',
                                                        "上标" 		=> 'bid2',
                                                        "费用" 		=> 'bid3',
                                                        "审核"       => 'bid4',
                                                        "复审"		=> 'bid5',
                                                    ),
                                                    "data" => array(
                                                        //网站设置
                                                        'eqaction_recheck'      => 'bid1',
                                                        'eqaction_index'        => 'bid1',
                                                        'eqaction_renewal'      => 'bid1',
                                                        'eqaction_uplist'       => 'bid1',
                                                        'eqaction_transferlist' => 'bid1',
                                                        'eqaction_review'       => 'bid5',
                                                        'eqaction_reviews'      => 'bid1',
                                                        'eqaction_reviews2'     => 'bid1',
                                                        'eqaction_reviews3'     => 'bid1',
                                                        'eqaction_reviews4'     => 'bid1',
                                                        'eqaction_upbid'        => 'bid2',
                                                        'eqaction_upbatchbid'   => 'bid2',
                                                        'eqaction_transfer'     => 'bid3',
														'eqaction_check'        => 'bid4',
                                                        'eqaction_docheck'      => 'bid4',
                                                        'eqaction_showrecheck'  => 'bid4',
                                                        'eqaction_dorecheck'    => 'bid4',
                                                        'eqaction_checkrisk'    => 'bid4',
                                                        'eqaction_cancel'       => 'bid4',
                                                        'eqaction_revsave'      => 'bid5',
                                                        'eqaction_checksave'    => 'bid5',
                                                        'eqaction_showreview'   => 'bid4',
                                                        //'eqaction_balance'      => 'bid1',
                                                        //'eqaction_unbalance'    => 'bid1',
                                                        //'eqaction_sumbill'      => 'bid3',
                                                        //'eqaction_sumamount'    => 'bid3',
                                                    )
);

$i++;
$acl_inc[$i]['low_title'][] = '拍拍信查询';
$acl_inc[$i]['low_leve']['ppclist']= array( "拍拍信查询" =>array(
                                                            "查询"        => 'po1',
                                                        ),
                                                            "data" => array(
                                                            //网站设置
                                                            'eqaction_doppc'    => 'po1',
                                                            'eqaction_index'    => 'po1',
                                                        )
);

$i++;
$acl_inc[$i]['low_title'][] = '统计';
$acl_inc[$i]['low_leve']['statistics'] = array("注册信息" => array(
	"列表" => 'regi1',
),
	"申请信息" => array(
		"列表" => 'regi2',
	),

	"data" => array(
		'eqaction_register' => 'regi1',
		'eqaction_apply' => 'regi2',
	)
);
$i++;
$acl_inc[$i]['low_title'][] = '财务报表';
$acl_inc[$i]['low_leve']['cashbill'] = array("待还款" => array(
	"列表" => 'cashbill1',
	"导出" => 'cashbill3'
),
	"已还款" => array(
		"列表" => 'cashbill2',
		"导出" => 'cashbill4'
	),

	"data" => array(
		'eqaction_wait' => 'cashbill1',
		'eqaction_has' => 'cashbill2',
		'eqaction_export_wait' => 'cashbill3',
		'eqaction_export_has' => 'cashbill4',
	)
);
$i++;
$acl_inc[$i]['low_title'][] = '芝麻分查询';
$acl_inc[$i]['low_leve']['zhimascore'] = array("芝麻分" => array(
    "查询" => 'zhimascore1',
),
    "data" => array(
        'eqaction_index' => 'zhimascore1',
    )
);
$i++;
$acl_inc[$i]['low_title'][] = '黑白灰名单';
$acl_inc[$i]['low_leve']['wbgray'] = array("黑白灰名单" => array(
	"列表" => 'wbgray1',
	"添加" => 'wbgray2',
	"导入" => 'wbgray3',
	"删除" => 'wbgray4',
),
	"data" => array(
		'eqaction_index' => 'wbgray1',
		'eqaction_add' => 'wbgray2',
		'eqaction_doadd' => 'wbgray2',
		'eqaction_import' => 'wbgray3',
		'eqaction_dodelete' => 'wbgray4',
	)
);
?>
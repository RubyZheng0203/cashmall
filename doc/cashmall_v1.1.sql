/*分组权限表*/
CREATE TABLE `ml_acl` (
  `controller` text CHARACTER SET utf8,
  `group_id` int(10) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*管理员帐户表*/
CREATE TABLE `ml_ausers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `user_pass` varchar(50) NOT NULL,
  `u_group_id` smallint(6) NOT NULL,
  `real_name` varchar(20) NOT NULL DEFAULT '匿名',
  `last_log_time` int(10) NOT NULL DEFAULT '0',
  `last_log_ip` varchar(30) NOT NULL DEFAULT '0',
  `is_ban` int(1) NOT NULL DEFAULT '0',
  `area_id` int(11) NOT NULL,
  `area_name` varchar(10) NOT NULL,
  `is_kf` int(10) unsigned NOT NULL DEFAULT '0',
  `qq` varchar(20) NOT NULL COMMENT '管理员qq',
  `phone` varchar(20) NOT NULL COMMENT '客服电话',
  `user_word` varchar(100) NOT NULL COMMENT '密码口令',
  `photo` varchar(150) NOT NULL COMMENT '相片',
  `sex` int(2) NOT NULL COMMENT '性别',
  `card` varchar(20) NOT NULL COMMENT '身份证号码',
  `email` varchar(50) NOT NULL COMMENT '邮箱',
  `province` int(11) NOT NULL COMMENT '省',
  `city` int(11) NOT NULL COMMENT '城市',
  `area` int(11) NOT NULL COMMENT '区',
  `address` varchar(150) NOT NULL COMMENT '地址',
  `content` varchar(200) NOT NULL COMMENT '描述',
  `parent` int(10) NOT NULL COMMENT '上级',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `guanlian_time` int(10) NOT NULL DEFAULT '0' COMMENT '关联时间',
  PRIMARY KEY (`id`),
  KEY `is_kf` (`is_kf`,`area_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*借款申请*/
CREATE TABLE `ml_borrow_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '借款人ID',
  `money` decimal(15,2) NOT NULL COMMENT '借款金额',
  `duration` int(3) NOT NULL COMMENT '借款期限',
  `repayment type` tinyint(2) NOT NULL COMMENT '借款类型 1，按天 2，按周 3，按月 4，按季度 5，按年',
  `rate` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '借款利率 百分比表示',
  `interest` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '利息',
  `audit_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '信息审核费',
  `deposit` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '借款保证金',
  `borrow_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '借款管理费',
  `renewal_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '续期费用',
  `renewal_id` int(11) DEFAULT NULL COMMENT '续期借款ID',
  `due_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '逾期罚息',
  `late_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '催收费',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态  0，未提交申请',
  `audit_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '审核状态 0，初审中 1；已初审 2：已确认银行卡 3；已身份确认 4：已签约 5：已放款',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '申请时间',
  `len_time` int(10) NOT NULL DEFAULT '0' COMMENT '放款时间',
  `deadline` int(10) NOT NULL DEFAULT '0' COMMENT '还款时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*借款申请放款修改表*/
CREATE TABLE `ml_borrow_apply_pendingedit` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL COMMENT '借款ID',
  `uid` int(11) NOT NULL COMMENT '借款人ID',
  `money` decimal(15,2) NOT NULL COMMENT '借款金额',
  `duration` int(3) NOT NULL COMMENT '借款期限',
  `repayment_type` tinyint(2) NOT NULL COMMENT '借款类型 1，按天 2，按周 3，按月 4，按季度 5，按年',
  `rate` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '借款利率 百分比表示',
  `interest` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '利息',
  `audit_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '信息审核费',
  `deposit` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '借款保证金',
  `security_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '风险保障金',
  `borrow_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '借款管理费',
  `renewal_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '续期费用',
  `renewal_id` int(11) DEFAULT NULL COMMENT '续期借款ID',
  `due_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '逾期罚息',
  `late_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '催收费',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态  0，未提交申请',
  `audit_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '审核状态 0，初审中 1；已初审 2：已确认银行卡 3；已身份确认 4：已签约 5：已放款',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '备份时间',
  `add_uid` varchar(10) NOT NULL DEFAULT '0' COMMENT '备份者',
  `reason` varchar(200) NOT NULL COMMENT '更改原因',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 
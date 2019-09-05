<?php
/**
 * 财务报表查询
 * @author Ruby
 *
 */
class CashBillAction extends ACommonAction
{
    public function wait()
    {

        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $timespan               = strtotime(urldecode($_REQUEST['start_time'])) . "," . strtotime(urldecode($_REQUEST['end_time']));
            $map['a.len_time']      = array("between", $timespan);
            $search['start_time']   = strtotime(urldecode($_REQUEST['start_time']));
            $search['end_time']     = strtotime(urldecode($_REQUEST['end_time']));
        } elseif (!empty($_REQUEST['start_time'])) {
            $xtime                  = strtotime(urldecode($_REQUEST['start_time']));
            $map['a.len_time']      = array("gt", $xtime);
            $search['start_time']   = $xtime;
        } elseif (!empty($_REQUEST['end_time'])) {
            $xtime                  = strtotime(urldecode($_REQUEST['end_time']));
            $map['a.len_time']      = array("lt", $xtime);
            $search['end_time']     = $xtime;
        }

        if ($_REQUEST['uid']) {
            $map['b.uid'] = $_REQUEST['uid'];
            $search['uid'] = $map['b.uid'];
        }
        //待还款
        $map['b.status'] = 0;
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->where($map)->count();

        $p = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        $search['first'] = $p->firstRow;
        $search['end'] = $p->listRows;
        //分页处理
        $field = "a.len_time,a.uid,a.money,a.loan_money,a.duration,a.loan_account,a.repayment_type,a.rate,a.coupon_amount,a.created_fee,a.enabled_fee,a.audit_fee,b.interest,b.renewal_id,b.borrow_id,b.uid,b.deadline,b.id,b.capital";
        $list = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->field($field)->where($map)->limit($Lsql)->order('b.deadline')->select();
        foreach ($list as &$v) {
            //借款人姓名
            $member = M('member_info')->field('real_name')->where("uid = {$v['uid']}")->find();
            $v['real_name'] = $member['real_name'] ? $member['real_name'] : '空';
            //放款金额
            $v['loan_money'] = $v['loan_money'] ? $v['loan_money'] : '0.00';
            //借款换成天

            if ($v['repayment_type'] == 1) {
                $v['duration_time'] = $v['duration'];
            } else if ($v['repayment_type'] == 2) {
                $v['duration_time'] = $v['duration'] * 7;
            } else if ($v['repayment_type'] == 3) {
                $v['duration_time'] = $v['duration'] * 30;
            } else if ($v['repayment_type'] == 4) {
                $v['duration_time'] = $v['duration'] * 120;
            } else {
                $v['duration_time'] = $v['duration'] * 365;
            }
            $v['is_interest'] = $v['interest'];
            //是否续期
            $pay    = M('transfer_order_pay')->field("id")->where("borrow_id = {$v['borrow_id']} and scene=2 and status = 1")->count('id');
            if ($pay['id'] >0) {
                $v['is_renewal'] = '是';
            } else {
                $v['is_renewal'] = '否';
            }
            
        }
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        $this->display();

    }

    public function has()
    {
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $timespan               = strtotime(urldecode($_REQUEST['start_time'])) . "," . strtotime(urldecode($_REQUEST['end_time']));
            $map['a.len_time']      = array("between", $timespan);
            $search['start_time']   = strtotime(urldecode($_REQUEST['start_time']));
            $search['end_time']     = strtotime(urldecode($_REQUEST['end_time']));
        } elseif (!empty($_REQUEST['start_time'])) {
            $xtime                  = strtotime(urldecode($_REQUEST['start_time']));
            $map['a.len_time']      = array("gt", $xtime);
            $search['start_time']   = $xtime;
        } elseif (!empty($_REQUEST['end_time'])) {
            $xtime                  = strtotime(urldecode($_REQUEST['end_time']));
            $map['a.len_time']      = array("lt", $xtime);
            $search['end_time']     = $xtime;
        }
        
        if (!empty($_REQUEST['hstart_time']) && !empty($_REQUEST['hend_time'])) {
            $timespan                = strtotime(urldecode($_REQUEST['hstart_time'])) . "," . strtotime(urldecode($_REQUEST['hend_time']));
            $map['b.repayment_time'] = array("between", $timespan);
            $search['hstart_time']   = strtotime(urldecode($_REQUEST['hstart_time']));
            $search['hend_time']     = strtotime(urldecode($_REQUEST['hend_time']));
        } elseif (!empty($_REQUEST['hstart_time'])) {
            $xtime                   = strtotime(urldecode($_REQUEST['hstart_time']));
            $map['b.repayment_time'] = array("gt", $xtime);
            $search['hstart_time']   = $xtime;
        } elseif (!empty($_REQUEST['hend_time'])) {
            $xtime                   = strtotime(urldecode($_REQUEST['hend_time']));
            $map['b.repayment_time'] = array("lt", $xtime);
            $search['hend_time']     = $xtime;
        }

        if ($_REQUEST['uid']) {
            $map['b.uid']  = $_REQUEST['uid'];
            $search['uid'] = $map['b.uid'];
        }
        //待还款
        $map['b.status'] = 1;
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->where($map)->count();

        $p = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        $search['first'] = $p->firstRow;
        $search['end'] = $p->listRows;
        //分页处理
        $field = "a.len_time,a.money,a.loan_money,a.duration,a.repayment_type,b.renewal_id,b.due_fee,b.renewal_fee,b.late_fee,b.capital,b.interest,b.deadline,b.repayment_time,b.id,b.borrow_id,b.uid,a.len_time";
        $list = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->field($field)->where($map)->limit($Lsql)->order('b.repayment_time desc')->select();
        foreach ($list as &$v) {
            //借款人姓名
            $member = M('member_info')->field('real_name')->where("uid = {$v['uid']}")->find();
            $v['real_name'] = $member['real_name'] ? $member['real_name'] : '空';
            //放款金额
            $v['loan_money'] = $v['loan_money'] ? $v['loan_money'] : '0.00';
            //借款换成天
            if ($v['repayment_type'] == 1) {
                $v['duration_time'] = $v['duration'];
            } else if ($v['repayment_type'] == 2) {
                $v['duration_time'] = $v['duration'] * 7;
            } else if ($v['repayment_type'] == 3) {
                $v['duration_time'] = $v['duration'] * 30;
            } else if ($v['repayment_type'] == 4) {
                $v['duration_time'] = $v['duration'] * 120;
            } else {
                $v['duration_time'] = $v['duration'] * 365;
            }
            //应还利息
            $v['is_interest'] = $v['interest'];
            
            //是否续期
            /*$payoff = M('payoff_apply')->field("type")->where("detail_id = {$v['id']}")->find();
            if ($payoff['type'] == 2) {
                $v['is_renewal'] = '是';
            } else {
                $v['is_renewal'] = '否';
            }*/
            
            $pay    = M('transfer_order_pay')->field("id")->where("borrow_id = {$v['borrow_id']} and scene=2 and status = 1")->count('id');
            if ($pay['id'] >0) {
                $v['is_renewal'] = '是';
            } else {
                $v['is_renewal'] = '否';
            }
            
            //逾期天数
            $v['due_day'] = ($v['repayment_time'] - $v['deadline']) / 86400;

            $deadline  = $v['deadline'];
            $deadtime  = strtotime("+1 day", strtotime(date("Y-m-d", $deadline) . " 00:00:00"));
            $starttime = strtotime(date("Y-m-d", $v['repayment_time']) . " " . date("H:i:s", $deadline));
            $endtime   = strtotime(date("Y-m-d", $v['repayment_time']) . " 23:59:59");
            if ($v['repayment_time'] < $endtime && $v['repayment_time'] > $starttime) {
                $v['due_day'] = ceil(($v['repayment_time'] - $deadline) / 3600 / 24 - 1);
            } else {
                $v['due_day'] = ceil(($v['repayment_time'] - $deadline) / 3600 / 24);
            }
            if ($v['due_day'] <= 0) {
                $v['due_day'] = 0;
            }
        }
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        $this->display();

    }

    /*
     * 导出待还款
     */
    public function export_wait()
    {
        import("ORG.Io.Excel");
        $map = array();
        wqbLog("--".$_REQUEST['start_time']);
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $timespan           = $_REQUEST['start_time'] . "," . $_REQUEST['end_time'];
            $map['a.len_time']  = array("between", $timespan);
        } elseif (!empty($_REQUEST['start_time'])) {
            $xtime              = ($_REQUEST['start_time']);
            $map['a.len_time']  = array("gt", $xtime);
        } elseif (!empty($_REQUEST['end_time'])) {
            $xtime              = ($_REQUEST['end_time']);
            $map['a.len_time']  = array("lt", $xtime);
        }


        if ($_REQUEST['uid']) {
            $map['b.uid'] = intval($_REQUEST['uid']);
        }
        //待还款
        $map['b.status'] = 0;
        //接收分页开始
        $first = $_REQUEST['first'];
        //接收分页结束
        $end = $_REQUEST['end'];
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->where($map)->count();
        $p = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        //如果有条件则全部导出
        if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time']) || !empty($_REQUEST['uid'])) {
            $Lsql = "";
        } else {
            $Lsql = "{$first},{$end}";
        }

        //分页处理
        $field = "a.len_time,a.uid,a.money,a.loan_money,a.duration,a.loan_account,a.repayment_type,a.rate,a.coupon_amount,a.created_fee,a.enabled_fee,a.audit_fee,b.interest,b.renewal_id,b.borrow_id,b.uid,b.deadline,b.id,b.capital";
        $list = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->field($field)->where($map)->order('b.deadline')->select();

        foreach ($list as &$v) {
            //借款人姓名
            $member = M('member_info')->field('real_name')->where("uid = {$v['uid']}")->find();
            $v['real_name'] = $member['real_name'] ? $member['real_name'] : '空';
            //放款金额
            $v['loan_money'] = $v['loan_money'] ? $v['loan_money'] : '0.00';
            //借款换成天

            if ($v['repayment_type'] == 1) {
                $v['duration_time'] = $v['duration'];
            } else if ($v['repayment_type'] == 2) {
                $v['duration_time'] = $v['duration'] * 7;
            } else if ($v['repayment_type'] == 3) {
                $v['duration_time'] = $v['duration'] * 30;
            } else if ($v['repayment_type'] == 4) {
                $v['duration_time'] = $v['duration'] * 120;
            } else {
                $v['duration_time'] = $v['duration'] * 365;
            }
            //应还利息
            $v['is_interest'] = $v['interest'];
            
            //是否续期
            $pay    = M('transfer_order_pay')->field("id")->where("borrow_id = {$v['borrow_id']} and scene=2 and status = 1")->count('id');
            if ($pay['id'] >0) {
                $v['is_renewal'] = '是';
            } else {
                $v['is_renewal'] = '否';
            }
        }

        $row = array();
        $row[0] = array('账单ID', '借款人UID', '借款人姓名', '申请金额(元)', '放款金额(元)', '借款期限(天)', '还款利息(元)', '出借人UID', '优惠券(元)', '开户费(元)', '动用费(元)', '信审费(元)', '放款日期', '应还日期', '应还本金(元)', '应还利息(元)', '是否续期');
        $i = 1;
        foreach ($list as $val) {
            $row[$i]['id'] = $val['id'];
            $row[$i]['uid'] = $val['uid'];
            $row[$i]['real_name'] = $val['real_name'];
            $row[$i]['money'] = $val['money'];
            $row[$i]['loan_money'] = $val['loan_money'];
            $row[$i]['duration_time'] = $val['duration_time'];
            $row[$i]['interest'] = $val['interest'];
            $row[$i]['loan_account'] = $val['loan_account'];
            $row[$i]['coupon_amount'] = $val['coupon_amount'];
            $row[$i]['created_fee'] = $val['created_fee'];
            $row[$i]['enabled_fee'] = $val['enabled_fee'];
            $row[$i]['audit_fee'] = $val['audit_fee'];
            $row[$i]['len_time'] = date('Y-m-d H:i:s', $val['len_time']);
            $row[$i]['deadline'] = date('Y-m-d H:i:s', $val['deadline']);
            $row[$i]['capital'] = $val['capital'];
            $row[$i]['is_interest'] = $val['is_interest'];
            $row[$i]['is_renewal'] = $val['is_renewal'];
            $i++;
        }
        $xls = new Excel_XML('UTF-8', false, 'datalist');
        $xls->addArray($row);
        $xls->generateXML("export_wait");
    }

    /*
     * 导出已还款
     */
    public function export_has()
    {
        import("ORG.Io.Excel");
        $map = array();
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $timespan           = $_REQUEST['start_time'] . "," . $_REQUEST['end_time'];
            $map['a.len_time']  = array("between", $timespan);
        } elseif (!empty($_REQUEST['start_time'])) {
            $xtime              = ($_REQUEST['start_time']);
            $map['a.len_time']  = array("gt", $xtime);
        } elseif (!empty($_REQUEST['end_time'])) {
            $xtime              = ($_REQUEST['end_time']);
            $map['a.len_time']  = array("lt", $xtime);
        }
        
        if (!empty($_REQUEST['hstart_time']) && !empty($_REQUEST['hend_time'])) {
            $timespan                = urldecode($_REQUEST['hstart_time']) . "," . urldecode($_REQUEST['hend_time']);
            $map['b.repayment_time'] = array("between", $timespan);
        } elseif (!empty($_REQUEST['hstart_time'])) {
            $xtime                   = urldecode($_REQUEST['hstart_time']);
            $map['b.repayment_time'] = array("gt", $xtime);
        } elseif (!empty($_REQUEST['hend_time'])) {
            $xtime                   = urldecode($_REQUEST['hend_time']);
            $map['b.repayment_time'] = array("lt", $xtime);
        }
        if ($_REQUEST['uid']) {
            $map['b.uid'] = intval($_REQUEST['uid']);
        }
        //待还款
        $map['b.status'] = 1;
        //接收分页开始
        $first = $_REQUEST['first'];
        //接收分页结束
        $end = $_REQUEST['end'];
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->where($map)->count();
        $p = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        //如果有条件则全部导出
        if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time']) || !empty($_REQUEST['uid'])) {
            $Lsql = "";
        } else {
            $Lsql = "{$first},{$end}";
        }
        //分页处理
        $field = "b.borrow_id,a.len_time,a.money,a.loan_money,a.duration,a.repayment_type,b.renewal_id,b.due_fee,b.renewal_fee,b.late_fee,b.capital,b.interest,b.deadline,b.repayment_time,b.id,b.uid,a.len_time,a.audit_fee,a.created_fee,a.enabled_fee,a.pay_fee";
        $list = M('borrow_detail b')->join("{$this->pre}borrow_apply a  ON b.borrow_id=a.id")->field($field)->where($map)->order('b.repayment_time desc')->select();

        foreach ($list as &$v) {
            //借款人姓名
            $member = M('member_info')->field('real_name')->where("uid = {$v['uid']}")->find();
            $v['real_name'] = $member['real_name'] ? $member['real_name'] : '空';
            //放款金额
            $v['loan_money'] = $v['loan_money'] ? $v['loan_money'] : '0.00';
            //借款换成天
            if ($v['repayment_type'] == 1) {
                $v['duration_time'] = $v['duration'];
            } else if ($v['repayment_type'] == 2) {
                $v['duration_time'] = $v['duration'] * 7;
            } else if ($v['repayment_type'] == 3) {
                $v['duration_time'] = $v['duration'] * 30;
            } else if ($v['repayment_type'] == 4) {
                $v['duration_time'] = $v['duration'] * 120;
            } else {
                $v['duration_time'] = $v['duration'] * 365;
            }
         
            $pay    = M('transfer_order_pay')->field("id")->where("borrow_id = {$v['borrow_id']} and scene=2 and status = 1")->count('id');
            if ($pay['id'] >0) {
                $v['is_renewal'] = '是';
            } else {
                $v['is_renewal'] = '否';
            }
            //实际还利息
            $v['is_interest'] = $v['interest'];
            //逾期天数
            $v['due_day'] = ($v['repayment_time'] - $v['deadline']) / 86400;

            $deadline = $v['deadline'];
            $deadtime = strtotime("+1 day", strtotime(date("Y-m-d", $deadline) . " 00:00:00"));
            $starttime = strtotime(date("Y-m-d", $v['repayment_time']) . " " . date("H:i:s", $deadline));
            $endtime = strtotime(date("Y-m-d", $v['repayment_time']) . " 23:59:59");
            if ($v['repayment_time'] < $endtime && $v['repayment_time'] > $starttime) {
                $v['due_day'] = ceil(($v['repayment_time'] - $deadline) / 3600 / 24 - 1);
            } else {
                $v['due_day'] = ceil(($v['repayment_time'] - $deadline) / 3600 / 24);
            }
            if ($v['due_day'] <= 0) {
                $v['due_day'] = 0;
            }
        }

        $row = array();
        $row[0] = array('账单ID', '借款人UID', '借款人姓名', '申请金额(元)', '放款金额(元)', '借款期限(天)', '还款利息(元)', '实际还款日期', '实际还款本金(元)', '实际还款利息(元)', '认证费', '账户管理费','贷后管理费（日利率）','支付服务费','逾期天数(天)', '逾期利息(元)','催收费', '是否续期', '续期费(元)', '放款日期');
        $i = 1;
        foreach ($list as $val) {
            $row[$i]['id'] = $val['id'];
            $row[$i]['uid'] = $val['uid'];
            $row[$i]['real_name'] = $val['real_name'];
            $row[$i]['money'] = $val['money'];
            $row[$i]['loan_money'] = $val['loan_money'];
            $row[$i]['duration_time'] = $val['duration_time'];
            $row[$i]['interest'] = $val['interest'];
            $row[$i]['repayment_time'] = date('Y-m-d H:i:s', $val['repayment_time']);
            $row[$i]['capital'] = $val['capital'];
            $row[$i]['is_interest'] = $val['is_interest'];
            $row[$i]['created_fee'] = $val['created_fee'];
            $row[$i]['enabled_fee'] = $val['enabled_fee'];
            $row[$i]['audit_fee'] = $val['audit_fee'];
            $row[$i]['pay_fee'] = $val['pay_fee'];
            $row[$i]['due_day'] = $val['due_day'];
            $row[$i]['due_fee'] = $val['due_fee'];
            $row[$i]['late_fee'] = $val['late_fee'];
            $row[$i]['is_renewal'] = $val['is_renewal'];
            $row[$i]['renewal_fee'] = $val['renewal_fee'];
            $row[$i]['len_time'] = date('Y-m-d H:i:s', $val['len_time']);
            $i++;
        }
        $xls = new Excel_XML('UTF-8', false, 'datalist');
        $xls->addArray($row);
        $xls->generateXML("export_has");
    }

}
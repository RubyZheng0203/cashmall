<?php

/**
 * Created by PhpStorm.
 * User: 14370
 * Date: 2017/10/24
 * Time: 14:57
 */
class ZhimascoreAction extends ACommonAction
{
    public function index()
    {
        //接收类型 1会员uid 2手机号 3身份证号
        $type = intval($_POST['type1']);
        if ($type) {
            $where['type1'] = urldecode($_REQUEST['type1']);
            $search['type1'] = $where['type1'];
        }
        //接收内容
        $content = trim($_POST['uid_card']);
        if ($content) {
            $where['uid_card'] = urldecode($_REQUEST['uid_card']);
            $search['uid_card'] = $where['uid_card'];
        }
        if ($type && $content) {
            switch ($type) {
                case 1:
                    $where = " uid = {$content}";
                    break;
                case 2:
                    $info = M('member_info')->where("iphone = '{$content}'")->order('id desc')->find();
                    $where = " uid = {$info['uid']}";
                    break;
                case 3:
                    $info = M('member_info')->where("id_card = '{$content}'")->order('id desc')->find();
                    $where = " uid = {$info['uid']}";
                    break;
            }
            //分页处理
            import("ORG.Util.Page");
            $count = M('zhima_score')->where($where)->count('id');
            $p = new Page($count, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $Lsql = "{$p->firstRow},{$p->listRows}";//limit($Lsql)
            $list = M('zhima_score')->field(true)->where($where)->order("score_time DESC")->select();
        } else {
            $list = array();
        }
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("xaction", ACTION_NAME);
        $this->display();
    }

}
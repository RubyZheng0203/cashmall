<?php

/**
 * SMS
 * @author Rubyzheng
 *
 */
class ApppushAction extends ACommonAction
{
    //模板列表
    public function index(){
        //分页处理
        import("ORG.Util.Page");
        $count = M('ad_msg_tpl')->count('id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        
        //sql查询
        $info  = M('ad_msg_tpl')->field(true)->limit($Lsql)->order('id DESC')->select();
        $scene  = C('WECHAT_SCENE');
        $this->assign('info',$info);
        $this->assign("pagebar", $page);
        $this->display();
    }

    //编辑页
    public function edit()
    {
        if(!empty($_GET['id'])){
            $where['id'] = $_GET['id'];
            $list = M("ad_msg_tpl")->where($where)->field(true)->find();
            $this->assign('vo',$list);
        }
        $this->display();
    }

    //编辑推送模板
    public function save()
    {   
        $data['ticker'] = $_POST['ticker'];
        $data['title'] = $_POST['titlev'];
        $data['text'] = $_POST['textv'];
        $data['after_open'] = $_POST['after_open'];
        $data['url'] = $_POST['url'];
        $data['chaining'] = $_POST['chaining'];

        if(empty($_POST['id'])){//添加
            $demo = M("ad_msg_tpl")->add($data);
            if($demo){
                $this->success("操作成功",__URL__."/index/");
            }
        }else{//修改
            $where['id'] = $_POST['id'];
            $demo = M("ad_msg_tpl")->where($where)->save($data);
            if($demo){
                $this->success("操作成功",__URL__."/index/");
            }else{
                $this->success("操作成功",__URL__."/index/");
            }
        }
    }

    //删除模板
    public function delete(){
        $demo = M('ad_msg_tpl')->delete($_POST['id']);
        if ($demo!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }

    //主动发送安卓消息
    public function send(){
        $this->display();
    }

    //开始主动发送消息
    public function dosend(){
        $ticker     = $_POST['ticker'];
        $title      = $_POST['titlev'];
        $text       = $_POST['textv'];
        $after_open = $_POST['after_open'];
        $url        = $_POST['url'];
        $chaining   = $_POST['chaining'];
        $uids       = $_POST['uids'];
        $sendall    = $_POST['sendall'];//是否勾选全部
        $uidsi = strpos($uids,",");

        if($sendall == 1){//给所有人发送推送
            $all = M('member_umeng')->field(true)->select();
            $tokenall = array();//token
            $uidall = array();//uid
            $i = 0;
            foreach ($all as $k => $v) {
                $tokenall[$i] = $v['token'];
                $uidall[$i] = $v['uid'];
                $i++;
            }
            $tokenall = implode(',',$tokenall);
            $uidall = implode(',',$uidall);
            $go_url = "";
            $activity = "";
            if($after_open == 'go_url'){//跳转H5页面
                $go_url = $url;
            }
            if($after_open == 'go_activity'){//跳转APP页面
                $activity = $url;
            }
            $param = array(
                     "uid"          => $uidall,
                     "bid"          => 0,//多个推送的时候为0，单个推送的时候需要填写申请单号
                     "chaining"     => $chaining,
                     "ticker"       => $ticker,  //通知栏提示文字
                     "title"        => $title,//通知标题
                     "text"         => $text,//通知文字描述
                     "after_open"   => $after_open,//点击通知的后续行为
                     "url"          => $go_url,//go_url时跳转到URL
                     "activity"     => $activity,//go_activity时打开特定的activity
                     "tokens"       => $tokenall //发送对象的token
            );
            $return = ad_listcast($param);//多个推送
        }else{
            if($uidsi === false){//单独推送
                //组装token
                $tokens = M('member_umeng')->where("uid={$uids}")->field(true)->find();
                $go_url = "";
                $activity = "";
                if($after_open == 'go_url'){//跳转H5页面
                    $go_url = $url;
                }
                if($after_open == 'go_activity'){//跳转APP页面
                    $activity = $url;
                }
                $param = array(
                         "uid"          => $uids,
                         "bid"          => 0,//多个推送的时候为0，单个推送的时候需要填写申请单号
                         "chaining"     => $chaining,
                         "ticker"       => $ticker,  //通知栏提示文字
                         "title"        => $title,//通知标题
                         "text"         => $text,//通知文字描述
                         "after_open"   => $after_open,//点击通知的后续行为
                         "url"          => $go_url,//go_url时跳转到URL
                         "activity"     => $activity,//go_activity时打开特定的activity
                         "tokens"       => $tokens['token'] //发送对象的token
                );
                $return = ad_unicast($param);//单个推送
            }else{//多个推送
                //组装token
                $tokens = M('member_umeng')->where("uid in ({$uids})")->field(true)->select();
                $tokenall = array();
                $i = 0;
                foreach ($tokens as $k => $v) {
                    $tokenall[$i] = $v['token'];
                    $i++;
                }
                $tokenall = implode(',',$tokenall);
                $go_url = "";
                $activity = "";
                if($after_open == 'go_url'){//跳转H5页面
                    $go_url = $url;
                }
                if($after_open == 'go_activity'){//跳转APP页面
                    $activity = $url;
                }
                $param = array(
                         "uid"          => $uids,
                         "bid"          => 0,//多个推送的时候为0，单个推送的时候需要填写申请单号
                         "chaining"     => $chaining,
                         "ticker"       => $ticker,  //通知栏提示文字
                         "title"        => $title,//通知标题
                         "text"         => $text,//通知文字描述
                         "after_open"   => $after_open,//点击通知的后续行为
                         "url"          => $go_url,//go_url时跳转到URL
                         "activity"     => $activity,//go_activity时打开特定的activity
                         "tokens"       => $tokenall //发送对象的token
                );
                $return = ad_listcast($param);//多个推送
            }
        }
        $this->success("推送成功",__URL__."/send/");
    }
}
?>
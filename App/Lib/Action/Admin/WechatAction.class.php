<?php
class WechatAction extends ACommonAction{
    /**
     * 微信模板消息配置首页
     */
    function index(){
        //分页处理
        import("ORG.Util.Page");
        $count = M('wechat_msg m')->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        
        //sql查询
        $info  = M('wechat_msg m')->field(true)->limit($Lsql)->order('m.id DESC')->select();
        $scene  = C('WECHAT_SCENE');
        $row = array();
        foreach($info as $key=>$v){
            $v['use_scene']        = $scene[$v['scene']];
            $row[$key]=$v;
        }
        $info = $row;
        $this->assign('info',$info);
        $this->assign("pagebar", $page);
        $this->display();
    }
    
    /**
     * 添加模板消息页面
     */
    function add(){
        $scene  = C('WECHAT_SCENE');
        $this->assign('scene',$scene);
        
        $this->display();
    }
    
    /**
     * 添加微信模板消息方法
     */
    function doAdd(){
        $model  = M("wechat_msg");
        if (!$model->create()){
            $this->error("数据错误");
        }
        $model->addtime = time();
        $result = $model->add();
        if ($result){
            $this->success('ok');
        }
    }
    
    /**
     * 微信模板删除方法
     */
    function delete(){
        $id     = $_POST['id'];
        $result = M("wechat_msg")->where("id = {$id}")->delete();
        if ($result!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }
    
    /**
     * 微信模板修改页面
     */
    function edit(){
        $scene  = C('WECHAT_SCENE');
        $this->assign('scene',$scene);
        
        $id       = $_GET['id'];
        $info     = M("wechat_msg")->field(true)->where(" id = {$id} ")->find();
        
        $this->assign('type',$info['type']);
        $this->assign('info',$info);
        $this->display();
    }
    
    
    /**
     * 微信模板消息修改方法
     */
    function doEdit(){
        $model  = M("wechat_msg");
        if (!$model->create()) {
            $this->error("错误数据");
        }
        $model->motime = time();
        $result = $model->save();
        if ($result!==false){
            $this->success('更改成功');
        }else {
            $this->error('更改失败');
        }
    }
}
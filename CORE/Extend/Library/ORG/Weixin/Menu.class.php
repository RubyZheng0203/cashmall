<?php 
  require_once("Common.class.php");
 class Menu extends Common{
	public function setCreateMenu($menudata){	
	 $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->getAccessToken();
     $result =$this->https_request($url, $menudata);
     return $result;
	    }
     //以上是创建菜单..................................
  	public function getMenu(){ //获取菜单..................................................................
	  
       $url="https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$this->getAccessToken();
       $menulist=$this->https_request($url,$data=null);
	   return $menulist ;//传送过来的是Json数据，需要进一步格式化................
	   }
    public function getMenuData(){
	    $info=$this->getMenu();
		$rs =json_decode($info);
		$str="";
		foreach($rs->menu->button as $k=>$v)
	     {
	         $str.="<li>".$v->name."</li>";
			foreach($v->sub_button as $k1=>$v1)
			   {
			     $str.="<li>---<a href='".$v1->url."'>".$v1->name."</a></li>";
			     
			   }
	     } 
	  return $str; 
	  
	 }	 
   
   
	public function delMenu(){  //删除菜单...................................................................
       $url="https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$this->getAccessToken();
       $info =$this->https_request($url,$data=null);
       $res = json_decode($info);//这个地方的数据有用。。。。。。。。。。。。。。。。。。。。。。。。。。
       if($res->errcode == "0"){
            return true;
          }else{
            return false;
            }
	 
	    } 
   public function checkSignature(){ //检查Sinature..........................................................
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
        	echo $echoStr;     
        	exit;
          }
      }	
   public function getMenuJson()  //获取文本菜单json数据........................................................
    {
	    $menulist=M("weixinmenu")->select();
		$fathermenu=M("weixinmenu")->where("is_father=1")->order("listrange desc ")->select();
		$totalfathermenu=count($fathermenu);
		$strmenustart='{ "button":[';
		$strmenuend=']}';
		$strmid='';
		$i=0;
	    foreach($fathermenu as $k=>$v)
		 { $i++;
		   $strmid.="{";
		   $strmid.='"name":"'.$v['name'].'",';
		   $strmid.='"sub_button":[';
		   $sonmenu=M("weixinmenu")->where("is_father<>1 and son={$v['id']}")->order("listrange desc ")->select();
		   $j=0;
		   foreach($sonmenu as $k1=>$v1)
		     { $j++;
			  $strmid.='{';
			  $strmid.='"type":"'.$v1['type'].'",';
			  $strmid.='"name":"'.$v1['name'].'",';
			  $strmid.='"url":"'.$v1['url'].'"';
			  $strmid.='}';   //处理这个的这个","让在最后一个是不显示
			  if($j<count($sonmenu))
			   $strmid.=',';
		     }
		   $strmid.=']}';  //处理最后的这个","让他在最后一个时不显示
		   if($i<$totalfathermenu)
		   $strmid.=',';
		  }
	    $str=$strmenustart.$strmid.$strmenuend;
	    return $str;
	}
  public function getMenuView()   //获取菜单列表视图.........................................................
     {
	   $viewlist=M("weixinmenu")->where("is_father=1")->select();
	   $viewnum=count($viewlist);
	   $str="<ul>";
	   $addview=array();
	   $i=0;
	   foreach($viewlist as $k=>$v)
	     {  
		   $addview[$i]['id']=$v['id'];
		   $addview[$i]['name']=$v['name'];
		   $str.="<li>".$v['name']."&nbsp;&nbsp;&nbsp;<span style='float:right;'><a href='__URL__/editeMenu?id=".$v['id']."'>编辑</a>&nbsp;&nbsp;<a href='__URL__/delmenulist?id=".$v['id']."' onclick='return confirm(确定要删除);' >删除</a></span><li>";
		   $sonviewlist=M("weixinmenu")->where("is_father<>1 and son=".$v['id']."")->select();
		   $str.="<ul>";
			foreach($sonviewlist as $k2=>$v2)
			  {
			   $str.="<li><a href={$v2['url']} >".$v2['name']."</a>&nbsp;&nbsp;&nbsp;<span style='float:right;'><a href='__URL__/editeMenu?id=".$v2['id']."'>编辑</a>&nbsp;&nbsp;<a href='__URL__/delmenulist?id=".$v2['id']."' onclick='return confirm(确定删除?);' >删除</a></span></li>";
			  }
		   $str.="</ul>";  
		   $i++;
		   
		  }
	    $str.="</ul>";	
	    $menu['list']=$str;
	    $menu['listnum']=$viewnum;
	    $menu['addview']=$addview;
	    return $menu;
	 
	 }	
	 
  //以下是获取关注者向公共平台发送来的消息
   public function getPubliMsg()
     {
       $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->AppId."&secret=".$this->AppSecret; 
       $rs=$this->https_request($url,$data=null);
	   $txt='{
	     "touser":"gh_fb9e0507f71c",
		 "msgtype":"text",
		 "text":{
		   "content":"ceshikanyikan"
		 }
	   
	   }';
	 $url1="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->getAccessToken();
	 echo $url1;
	 $result=$this->https_request($url1,$txt);
	 var_dump($result);   
	   
     }	

	  	 	  
		  
 }
?>
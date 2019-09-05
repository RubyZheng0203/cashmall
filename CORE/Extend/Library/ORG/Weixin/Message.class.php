<?php 
 require("Common.class.php");
  class Message extends Common{
     public function vaild() //实现地址回复.......................................................................
	   { 
	      $echoStr = $_GET["echostr"];
         if($this->checkSignature()){
            echo $echoStr;
            exit;
            }
		 
	    }
	 private function checkSignature()  //实现链接地址配置验证.....................................................
        {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token =$this->Token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
          }
       }
	   
	  //验证客户们执行的是什么操作例如：关注，信息回复 （这里只是文本回复）................

     public function handleEvent()
      {  
	      $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
          if(!empty($postStr)){
		    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		    $contentStr = "";
            switch ($postObj->Event)
             {
            case "subscribe":
                 $contentStr ="感谢您关注【绿麻雀】"."\n"."微信号：lvmaque";
                 break;
            default :
                 $contentStr = '您好！我们是绿麻雀,我们一直都在更新!';
                 break;
             }
        $resultStr =$this->response_text($postObj, $contentStr);
		echo $resultStr;
		  }
       
     } 
	 
	 //以下是实现文本回复的代码..............................................................
   public function response_text($object,$content){  //实现回复消息的这是一个父类方法................................
        $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>%d</FuncFlag>
                </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
     }	
   
  //实现文本回复结束...........................................................

      public  function  response_morepic($object, $newsContent)  //实现多图文回复
         { 
	      
        $newsTplHead = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>";
        $newsTplBody = "<item>
                <Title><![CDATA[%s]]></Title> 
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>";
        $newsTplFoot = "</Articles>
                <FuncFlag>0</FuncFlag>
                </xml>";
  
        $header = sprintf($newsTplHead, $object->FromUserName, $object->ToUserName, time());
		$body="";
        $j=0;
		foreach($newsContent as $k=>$v)
		   {
		      $j++;
			  if($j>=10) break;
		      $body.=sprintf($newsTplBody,$v['title'],$v['description'],$v['picUrl'],$v['url']); 
		   }

        $FuncFlag = 0;
        $footer = sprintf($newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
  
     } 
	
   public function  setSignText($openid,$msg){  //其中一个关注者发送文本信息....................................
         $txt='{
	      "touser":"'.$openid.'",
	      "msgtype":"text",
	      "text":
	         {
		       "content":"'.$msg.'";
		     }
          }';	
      
	     $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->getAccessToken();
	     $info =$this->https_request($url,$txt);
         $res = json_decode($info);//这个地方的数据有用..............................................
		 if($res->errmsg=='ok')
		  {
		    return true;
		  }else{
		  
		    return false;
		  } 
		  
       }
   
    public function setSignPic($openid,$media_id){ //向关注者发送图片信息...............................
	    $img = '{
           "touser":"'.$openid.'",
           "msgtype":"image",
           "image":
            {
               "media_id":"'.$media_id.'";
             }
          }';
 
	   $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->getAccessToken();
	   $info=$this->https_request($url,$img);
	   $res = json_decode($info);
	  if($res->errmsg=='ok')
	    {
		  return true;
		}else{
		
		  return false;
		}
		
	  }	 
   	
    public function setUploadMedia($picname){  //实现上传图片到微信服务器............................
      $type="image";
	   $filepath=$picpath; //这里的链接地址，一定要注意.............
	   $filedata=array("media"=>"@".$filepath);
	   $url="http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->getAccessToken()."&type=$type";
	   $info=$this->https_request($url, $filedata);
	   $res = json_decode($info);
	   return $res->media_id;
	  } 

  public function getUploadMedia($media_id){ //下载上传文件
     $url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=$media_id";
	 $filedate=$this->https_request($url,$data=null);
	 return $filedate;
   }
   
  
   public function  setAllTextPic($news,$groupid){  //实现多图文回复....................................
	   $str="";	 
	   $totalnum=count($news);
	   $j=0;
	   foreach($news as $k5=>$v5)
	     {  $j++;
		   $str.='{
	  
       "articles": [
	    	 {
              "thumb_media_id":"'.$v5['thumb_media_id'].'",
              "author":"'.$v5['author'].'",
			  "title":"'.$v5['title'].'",
			  "content_source_url":"'.$v5['content_source_url'].'",
			  "content":"'.$v5['content'].'",
			  "digest":"'.$v5['digest'].'",
              "show_cover_pic":"1"
		       }
            ]
          }';
		   
		   if($j!=$totalnum)
		    $str.=",";
		  
		} 
		
		 return $str;  //正式上线方能开通此功能
		
	 /**	
	   $url="https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=".$this->getAccessToken();	
	   $info=$this->https_request($url,$TextPic);
	   $res =json_decode($info);
	   
	   $msg=array('filter'=>array('group_id'=>$groupid));
	   $msg['mpnews']=array('media_id'=>$res->media_id);
	   $msg['msgtype']='mpnews';
	   $fsd=urldecode(json_encode($msg));
	   /*******
	   $url1="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$this->getAccessToken();
	   $info=$obj->https_request($url1,$fsd);
	   $rs=json_decode($info)
	   if($rs->errcode=='0')
	     {
		   return true;
		 }else{
		 
		   return false;
		 }
	   *********/
	   
	   }
 public function setAllText($groupid,$content){ //实现文本群发............................
	   $msg=array('filter'=>array('group_id'=>$groupid));
	   $msg['text']=array('content'=>$content);
	   $msg['msgtype']="text";
	   $senddata=urldecode(json_encode($msg));
	   $url1="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$this->getAccessToken();
	   //$info=$this->https_request($url1,$senddata); 
	   $rs=json_decode($info);
	    if($rs->errcode=='0')
		{
		 return true;
		  }else{
		 return false;
		}
      }	
    	    
  public function getGroup(){
      $url="https://api.weixin.qq.com/cgi-bin/groups/get?access_token=".$this->getAccessToken();
	  $info=$this->https_request($url,"");
	  $rs=json_decode($info);
	  $list=array();
	  $list1=array();
	  $i=0;
	  foreach($rs->groups as $k=>$v)
	   {  
	     $str.="<li>组名称：".$v->name."&nbsp;&nbsp;组成员数：".$v->count."</li>";
		 $list1['groupid']=$v->id;
		 $list1['name']=$v->name;
		 $list1['count']=$v->count;
		 $list[$i++]=$list1;
	  }
	  
	 return $list; 
    
     }   
   	  
	  
	  
  }


?>
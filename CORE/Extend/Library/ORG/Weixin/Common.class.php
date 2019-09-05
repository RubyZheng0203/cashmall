<?php 
 class Common {
    //var $AppId="wxe26f075ea28bfab7";
   // var $AppSecret="0edc0c563478bd9080fb0db116fc40a3";
    //var $Token="lvmaque";
	  var $AppId="";
	  var $AppSecret="";
	  var $Token="";
	  var $OldId="";
	function __construct(){
		   $this->AppId="wxd5f89ac203363c80";
		  $this->AppSecret="2c288191f9e15ce4f95538b38c6d9b14";
		  $this->Token="jinrongtj";
		  $this->OldId="gh_68d040d204c2";
	    }
   //以上是公共平台参数.......................................................................
     public function https_request($url,$data = null){
         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
         if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
           }
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
         $output = curl_exec($curl);
         curl_close($curl);
         return $output;
      }
	//获取token代码...................................
   public function getAccessToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->AppId."&secret=".$this->AppSecret;
	    $output=$this->https_request($url,$data=null);
        $jsoninfo = json_decode($output, true);
        $access_token = $jsoninfo["access_token"];	
        return  $access_token;
     }  
    //获取access_token结束................................................................	  
   
   
   
   
 
   
 }
?>
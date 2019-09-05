<?php
 	if ($_SERVER['HTTPS'] != "on") {
 		$index = strstr($_SERVER['REQUEST_URI'],"index.php");
 		if($index){
 			$str = preg_replace('/\/index.php/', '', $_SERVER['REQUEST_URI']);
			
 			if (strpos($str, '/pay/wqbnotice') !== false || strpos($str, '/notify') !== false) {
 			    $url = "http://" . $_SERVER["SERVER_NAME"] . $str;
 			} else {
 			    $url = "https://" . $_SERVER["SERVER_NAME"] . $str;
 			}
 			header("location:".$url);
 		}
 		/*else{
 		    $old_url = $_SERVER["REQUEST_URI"];
 		    //检查链接中是否存在 ?
 		    $check  = strpos($old_url, '?');
 		    $checkv = strpos($old_url, 'v=20180411');
 		    echo "------".$checkv;
 		    //如果存在?
 		    if($check >0){
 		        if($checkv >0){
 		            $new_url = $old_url;
 		        }else{
 		            //如果?后面没有参数，
 		            if(substr($old_url, $check+1) == ''){
 		                //可以直接加上附加参数
 		                $new_url = $old_url.'v=20180411';
 		            }else{ //如果有参数，
 		                $new_url = $old_url.'&v=20180411';
 		            }
 		        }
 		        
 		    }else{//如果不存在 ?
 		        $new_url = $old_url.'?v=20180411';
 		    }
 		    $url ="http://" . $_SERVER["SERVER_NAME"] .":8013".$new_url;
 		    echo $url;
 		    header("location:"."http://" . $_SERVER["SERVER_NAME"] .":8013".$new_url);
 		}*/
 	}else{
 	    
 	}
 	
	if(isset($_SERVER['HTTP_X_REWRITE_URL'])){
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
		$___s = explode(".",$_SERVER['REQUEST_URI']);
		$____s = explode("?",$_SERVER['REQUEST_URI']);
		$_SERVER['PATH_INFO'] = $____s[0];
		$GLOBALS['is_iis'] = true;
	}

    if ( isset($_REQUEST["PHPSESSID"]) ) {
        session_id($_REQUEST["PHPSESSID"]);
    }
   
    define('THINK_PATH',dirname(__FILE__).'/CORE/');
    define('APP_NAME',dirname(__FILE__).'App');
    define('APP_PATH',dirname(__FILE__).'/App/');
   	define('APP_DEBUG',1);
    define('APP_PUBLIC_PATH',dirname(__FILE__).'/Public');

	define('BUILD_DIR_SECURE',true); 
	define('DIR_SECURE_FILENAME', 'default.html'); 
	define('DIR_SECURE_CONTENT', 'deney Access!'); 
    
    require "vendor/autoload.php";
    $weiqianbaoConfig = require(APP_PATH . "/Conf/weiqianbao.php");
    \App\Library\Weiqianbao\Weiqianbao::setConfig($weiqianbaoConfig);
    
    $fuiouConfig = require(APP_PATH . "/Conf/fuiou.php");
    \App\Library\Fuiou\Fuiou::setConfig($fuiouConfig);

    require(THINK_PATH.'/Core.php');
    

?>
<?php
use App\Library\Weiqianbao\Weiqianbao;
use App\Library\Weiqianbao\Protocol\UnbindingVerify\Request as UnbindingVerifyRequest;
use App\Library\Weiqianbao\Protocol\UnbindingVerify\Response as UnbindingVerifyResponse;
/**
 * 拍拍信
 * @author Rubyzheng
 *
 */
class PpclistAction extends ACommonAction
{	
	//ppc查询
	public function doppc()
	{
	    if($_POST['mobile']||$_POST['id_card']){
	        if($_POST['mobile']){
	            $mobile_no   = $_POST['mobile'];
	            $sql   = "SELECT * FROM rs_ppc_list WHERE mobile_no = '{$mobile_no}'";
	            $info  = M('member_info')->field("iphone,id_card,real_name")->where("iphone = '{$mobile_no}'")->find();
	        }
	        /*if($_POST['name']){
	            $real_name   = $_POST['name'];
	            $sql   = "SELECT * FROM rs_ppc_list WHERE real_name = '{$real_name}'";
	        }*/
	        if($_POST['id_card']){
	            $id_card   = $_POST['id_card'];
	            $sql   = "SELECT * FROM rs_ppc_list WHERE id_card = '{$id_card}'";
	            $info  = M('member_info')->field("iphone,id_card,real_name")->where("id_card = '{$id_card}'")->find();
	        }
	        
	        getppc($info['id_card'],$info['iphone'],$info['real_name']);
	        
	        //连接风控数据库
	        $dataname = C('DB_NAME_RISK');
	        $db_host  = C('DB_HOST_RISK');
	        $db_user  = C('DB_USER_RISK');
	        $db_pwd   = C('DB_PWD_RISK');
	        $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
	        $bdb->beginTransaction();
	        $list = $bdb->query($sql);

	        //组合数据
		    $html  = "<table id='area_list' width='100%' border='0' cellspacing='0' cellpadding='0'><tr><th class='line_l'>姓名</th><th class='line_l'>手机号</th><th class='line_l'>身份证号</th><th class='line_l'>信用模型评分</th><th class='line_l'>风险等级</th><th class='line_l'>违约概率</th><th class='line_l'>设备行为系数</th><th class='line_l'>社交行为系数</th><th class='line_l'>拉取时间</th></tr>";
		    foreach($list as $key=>$v){
		        $data = "<tr overstyle='on'>";
		        $data = $data."<td>".$v['real_name']."</td><td>".$v['mobile_no']."</td><td>".$v['id_card']." </td><td>".$v['scoresma']."</td><td>".$v['riskrank']."</td><td>".$v['probability']."</td><td>".$v['device_confficient']."</td><td>".$v['socialact_confficient']."</td><td>".date('Y-m-d H:m:s',$v['add_time'])."</td>";
		        $data = $data."</tr>";
		        $html = $html.$data;
		    }
		    $html = $html."</table>";
		    ajaxmsg($html, 0);
	    }else{
		    ajaxmsg("", 0);
		}
	}
	
}
?>
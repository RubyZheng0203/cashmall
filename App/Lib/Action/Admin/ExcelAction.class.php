<?php
class ExcelAction extends Action {
    public function __construct(){
        import('ORG.Util.ExcelToArrary');//导入excelToArray类
    }
    public function add(){
        $tmp_file = $_FILES['file_stu']['tmp_name'];
        $file_types = explode (".",$_FILES['file_stu']['name']);
        $file_type = $file_types[count($file_types)-1];
        /*判别是不是.xls文件，判别是不是excel文件*/
        if ($file_type!='xls'&&$file_type!='xlsx')
        {
            $this->error("错误文件格式");
        }

        /*设置上传路径*/
        $savePath = 'UF/Uploads/Excel/';
        /*以时间来命名上传的文件*/
        $str = date ( 'Ymdhis' );
        $file_name = $str . "." . $file_type;
        /*是否上传成功*/
        if (! copy ( $tmp_file, $savePath . $file_name ))
        {
            $this->error ( '上传失败' );
        }
        
        $addData        = M('random_lending');//M方法
        $ExcelToArrary  = new ExcelToArrary();//实例化
        $field  = M()->query("select COLUMN_NAME from information_schema.COLUMNS where table_name = 'ml_random_lending'");
        $res    = $ExcelToArrary->read($savePath.$file_name,"UTF-8",$file_type);//传参,判断office2007还是office2003
        $number = 0;
        for ($i=1;$i<count($res);$i++){
            $data['money'] = $res[$i+1]['0'];
            $data['date']  = strtotime(date("Y-m-d",time())." 00:00:00");
            $addData->add($data);
            $number++;
        }
       if (($number+1)>=count($res)){
           unlink($savePath.$file_name);
           header("Location: {$_SERVER['HTTP_REFERER']}");
       }
    }
}
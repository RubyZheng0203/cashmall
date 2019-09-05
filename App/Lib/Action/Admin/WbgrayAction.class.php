<?php

/**
 * Created by PhpStorm.
 * User: 14370
 * Date: 2017/11/2
 * Time: 9:25
 */
class WbgrayAction extends ACommonAction
{
    //列表
    public function index()
    {
        $map = array();
        if ($_REQUEST['iphone']) {
            $map['m.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone'] = $map['m.iphone'];
        }

        //分页处理
        import("ORG.Util.Page");
        $count = M('member_type m')->join("{$this->pre}member_info mi ON mi.iphone=m.iphone")->where($map)->count('m.id');
        $p = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.iphone,m.type,m.add_time,mi.uid";
        $list = M('member_type m')->join("{$this->pre}member_info mi ON mi.iphone=m.iphone")->field($field)->where($map)->limit($Lsql)->order('m.add_time desc,m.id DESC')->select();
        foreach ($list as &$v) {
            switch ($v['type']) {
                case 1 :
                    $v['type_name'] = '黑名单';
                    break;
                case 2 :
                    $v['type_name'] = '白名单';
                    break;
                case 3 :
                    $v['type_name'] = '灰名单';
                    break;
            }
        }
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        $this->display();

    }

    //导入
    public function import()
    {
        import('ORG.Util.ExcelToArrary');//导入excelToArray类
        $tmp_file = $_FILES['file_stu']['tmp_name'];
        $file_types = explode(".", $_FILES['file_stu']['name']);
        $file_type = $file_types[count($file_types) - 1];
        if(empty($file_type)){
            $this->error("请选择文件");
        }
        /*判别是不是.xls文件，判别是不是excel文件*/
        if ($file_type != 'xls' && $file_type != 'xlsx') {
            $this->error("错误文件格式");
        }

        /*设置上传路径*/
        $savePath = 'UF/Uploads/Excel/';
        /*以时间来命名上传的文件*/
        $str = date('Ymdhis');
        $file_name = $str . "." . $file_type;
        /*是否上传成功*/
        if (!copy($tmp_file, $savePath . $file_name)) {
            $this->error('上传失败');
        }

        $addData = M('member_type');//M方法
        $ExcelToArrary = new ExcelToArrary();//实例化
        $field = M()->query("select COLUMN_NAME from information_schema.COLUMNS where table_name = 'ml_member_type'");
        $res = $ExcelToArrary->read($savePath . $file_name, "UTF-8", $file_type);//传参,判断office2007还是office2003
        $number = 0;
        $c = 0;
        $d = 0;
        for ($i = 1; $i < count($res); $i++) {
            $data['iphone'] = $res[$i + 1]['0'];
            switch ($res[$i + 1]['1']) {
                case '黑名单':
                    $data['type'] = 1;
                    break;
                case '白名单':
                    $data['type'] = 2;
                    break;
                case '灰名单':
                    $data['type'] = 3;
                    break;
                default:
                    $data['type'] = 0;
                    break;
            }
            $data['add_time'] = time();
            $info = M('member_type')->where("iphone = '{$data['iphone']}'")->find();
            if ($info) {
                $c += 1;
            } else {
                if(in_array($data['type'],[1,2,3])){
                    $addData->add($data);
                }else{
                    $d += 1;
                }
            }
            $number++;
        }

        if (($number + 1) >= count($res)) {
            unlink($savePath . $file_name);
            //如果全是重复的数据
            if ($c == count($res) - 1) {
                $this->error("数据重复,导入失败");
            }elseif($d + $c == count($res) - 1){
                $this->error("数据不对,导入失败");
            } else {
                $this->success("导入成功");
            }
            header("Location: {$_SERVER['HTTP_REFERER']}");
        }
    }

    public function add()
    {
        $this->display();

    }

    public function doAdd()
    {
        //手机号
        $data['iphone'] = $_POST['iphone'];
        $data['type'] =  $_POST['type'];
        //查找手机号是否存在相同数据
        $res = M('member_type')->where("iphone = {$data['iphone']}")->find();
        if($res){
            $this->error("数据重复,添加失败");
        }
        $data['add_time'] = time();
        if($data['iphone'] && $data['type']){
            M('member_type')->add($data);
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }

    }

    public function doDelete()
    {
        $id = $_REQUEST['idarr'];
        $delnum = M('member_type')->where("id = {$id}")->delete();
        if ($delnum) {
            $this->success("名单删除成功", '', $id);
        } else {
            $this->success("名单删除失败");
        }

    }
}
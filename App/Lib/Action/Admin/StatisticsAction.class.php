<?php

/**
 * Created by PhpStorm.
 * User: 14370
 * Date: 2017/9/25
 * Time: 11:26
 */
class StatisticsAction extends ACommonAction
{
    //注册信息
    public function register()
    {
        //接收时间
        $start_time = strtotime($_REQUEST['start_time']);
        $end_time = strtotime($_REQUEST['end_time']);
        //接收分层
        $category1 = intval($_REQUEST['category1']);
        //判断有没有选择时间
        if ($start_time && empty($end_time)) {
            $sql_time = " a.reg_time >= {$start_time}";
        } elseif (empty($start_time) && $end_time) {
            $sql_time = " a.reg_time <= {$end_time}";
        } else {
            $sql_time = " a.reg_time BETWEEN {$start_time} AND {$end_time}";
        }

        if (!empty($category1)) {
            switch ($category1) {
                case 1:
                    $sql = "SELECT COUNT(a.id) as count FROM ml_members a WHERE {$sql_time}";
                    $new_data = M()->query($sql);
                    foreach ($new_data as &$v) {
                        $v['register'] = '总数';
                    }
                    break;
                case 2:
                    $sql = "SELECT a.id,id_card
                            FROM ml_members a LEFT JOIN ml_member_info b ON a.id = b.uid
                            WHERE  {$sql_time} ";
                    $sex = M()->query($sql);
                    foreach ($sex as $v) {
                        $idcard = $v['id_card'];
                        if ($idcard == null) {
                            $data[2]['count'] += 1;
                            $data[2]['sex_group'] = '未知';
                        } else {
                            if(strlen($idcard) == 15){
                                $sex1 = substr($idcard,-2,1) % 2;
                            }elseif(strlen($idcard) == 18){
                                $sex1 = substr($idcard,-2,1) % 2;
                            }
                            switch ($sex1) {
                                case 0:
                                    $data[0]['count'] += 1;
                                    $data[0]['sex_group'] = '女';
                                    break;
                                case 1:
                                    $data[1]['count'] += 1;
                                    $data[1]['sex_group'] = '男';
                                    break;
                            }
                        }
                    }
                    $new_data = array_values($data);
                    if (empty($new_data)) {
                        $new_data = array(
                            0 => array('count' => 0,
                                'sex_group' => '总数')
                        );
                    }
                    break;
                case 3:
                    $sql = "SELECT a.id,b.id_card,CURDATE() as  time1
                            FROM ml_members a LEFT JOIN ml_member_info b ON a.id = b.uid
                            WHERE {$sql_time}";
                    $age = M()->query($sql);
                    foreach ($age as &$v) {
                        $time = explode('-', $v['time1']);
                        $v['year'] = $time[0];
                        //获取年龄
                        $v['age'] = substr($v['id_card'], 6, 4);
                        $v['age_group'] = $v['year'] - $v['age'];
                        $tmp = floor($v['age_group'] / 10);
                        switch ($tmp) {
                            case 0:
                            case 1:
                                $data[1]['count'] += 1;
                                $data[1]['age_group'] = '小于20';
                                break;
                            case 2:
                                $data[2]['count'] += 1;
                                $data[2]['age_group'] = '20-29';
                                break;
                            case 3:
                                $data[3]['count'] += 1;
                                $data[3]['age_group'] = '30-39';
                                break;
                            case 4:
                                $data[4]['count'] += 1;
                                $data[4]['age_group'] = '40-49';
                                break;
                            case 5:
                                $data[5]['count'] += 1;
                                $data[5]['age_group'] = '50-59';
                                break;
                            case 6:
                                $data[6]['count'] += 1;
                                $data[6]['age_group'] = '大于等于60';
                                break;
                            default:
                                $data[0]['count'] += 1;
                                $data[0]['age_group'] = '未知';
                                break;
                        }
                    }
                    ksort($data);
                    $new_data = array_values($data);
                    if (empty($new_data)) {
                        $new_data = array(
                            0 => array('count' => 0,
                                'age_group' => '总数')
                        );
                    }
                    break;
                case 4:
                    $sql = "SELECT COUNT(a.id) as count,a.reg_address
                            FROM ml_members a
                            WHERE  {$sql_time} GROUP BY a.reg_address";
                    $address = M()->query($sql);
                    $total = 0;
                    foreach ($address as $key => $val) {
                        if ($val['reg_address'] == '') {
                            $total += $val['count'];
                            unset($address[$key]);
                        }
                    }
                    $new_arr = array(
                        0 => array('count' => $total,
                            'reg_address' => '未知')
                    );
                    $new_data = array_merge($new_arr, $address);
                    if ($new_data == $new_arr) {
                        $new_data = array(
                            0 => array('count' => 0,
                                'reg_address' => '总数')
                        );
                    }
                    break;
                case 5:
                    $sql = "SELECT a.id,b.education
                            FROM ml_members a LEFT JOIN ml_member_info b ON a.id = b.uid WHERE  {$sql_time} ";
                    $school = M()->query($sql);
                    foreach ($school as $v) {
                        switch ($v['education']) {
                            case 'PRE_HIGH_SCHOOL':
                                $data[1]['count'] += 1;
                                $data[1]['education_group'] = '高中以下';
                                break;
                            case 'HIGH_SCHOOL':
                                $data[2]['count'] += 1;
                                $data[2]['education_group'] = '高中／中专';
                                break;
                            case 'JUNIOR_COLLEGE':
                                $data[3]['count'] += 1;
                                $data[3]['education_group'] = '大专';
                                break;
                            case 'UNDER_GRADUATE':
                                $data[4]['count'] += 1;
                                $data[4]['education_group'] = '本科';
                                break;
                            case 'POST_GRADUATE':
                                $data[5]['count'] += 1;
                                $data[5]['education_group'] = '研究生';
                                break;
                            default:
                                $data[0]['count'] += 1;
                                $data[0]['education_group'] = '未知';
                                break;
                        }
                    }
                    ksort($data);
                    $new_data = array_values($data);
                    if (empty($new_data)) {
                        $new_data = array(
                            0 => array('count' => 0,
                                'education_group' => '总数')
                        );
                    }
                    break;
                case 6:
                    $sql = "SELECT a.id,b.year_income
                            FROM ml_members a LEFT JOIN ml_member_company b ON a.id = b.uid WHERE  {$sql_time} ";
                    $income = M()->query($sql);
                    foreach ($income as $v) {
                        switch ($v['year_income']) {
                            case '10000以下':
                                $data[1]['count'] += 1;
                                $data[1]['income_group'] = '10000以下';
                                break;
                            case '10000-50000':
                                $data[2]['count'] += 1;
                                $data[2]['income_group'] = '10000-50000';
                                break;
                            case '50000-100000':
                                $data[3]['count'] += 1;
                                $data[3]['income_group'] = '50000-100000';
                                break;
                            case '100000-200000':
                                $data[4]['count'] += 1;
                                $data[4]['income_group'] = '100000-200000';
                                break;
                            case '200000以上':
                                $data[5]['count'] += 1;
                                $data[5]['income_group'] = '200000以上';
                                break;
                            default:
                                $data[0]['count'] += 1;
                                $data[0]['income_group'] = '未知';
                                break;
                        }
                    }
                    ksort($data);
                    $new_data = array_values($data);
                    if (empty($new_data)) {
                        $new_data = array(
                            0 => array('count' => 0,
                                'income_group' => '总数')
                        );
                    }
                    break;
                //7绑卡通过人次
                case 7:
                    $sql = "SELECT COUNT(b.id) as count  FROM  ml_member_bank b LEFT JOIN ml_members a ON b.uid = a.id WHERE  b.type = 1 AND {$sql_time}";
                    $new_data = M()->query($sql);
                    foreach ($new_data as &$v) {
                        $v['register'] = '总数';
                    }
                    break;
                //8到达身份验证人次
                case 8:
                    $sql = "SELECT COUNT(b.id) as count  FROM  ml_member_info b  LEFT JOIN ml_members a ON b.uid = a.id WHERE  b.id_card <> '' AND {$sql_time}";
                    $new_data = M()->query($sql);
                    foreach ($new_data as &$v) {
                        $v['register'] = '总数';
                    }
                    break;
                default:
                    $count = 0;
                    break;
            }

            $count = $new_data;
            $count = json_encode($count,JSON_UNESCAPED_UNICODE);
            $msg = "{$count}";
                        wqbLog($count);
            ajaxmsg($msg, 1);
        } else {
            $count = 0;
        }

        $this->display();
    }

    //申请信息
    public function apply()
    {
        //接收时间
        $start_time = strtotime(urldecode($_REQUEST['start_time']));
        $end_time = strtotime(urldecode($_REQUEST['end_time']));
        $category1 = intval($_REQUEST['category1']);
        //判断有没有选择时间
        if (!empty($category1)) {
            switch ($category1) {
                //1提交申请人次
                case 1:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " add_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " add_time <= {$end_time}";
                    } else {
                        $sql_time = " add_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count FROM ml_borrow_apply  WHERE   {$sql_time}";
                    break;
                //2白骑士通过人次
                case 2:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " mid_tree_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " mid_tree_time <= {$end_time}";
                    } else {
                        $sql_time = " mid_tree_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count FROM ml_member_status WHERE  mid_tree = 1 AND {$sql_time}";
                    break;
                //3白骑士拒绝人次
                case 3:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " mid_tree_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " mid_tree_time <= {$end_time}";
                    } else {
                        $sql_time = " mid_tree_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count FROM ml_member_status WHERE mid_tree = 2 AND  {$sql_time}";
                    break;
                //4初审通过人次
                case 4:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " first_trial_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " first_trial_time <= {$end_time}";
                    } else {
                        $sql_time = " first_trial_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  first_trial = 1 AND {$sql_time}";
                    break;
                //5初审拒绝人次
                case 5:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " first_trial_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " first_trial_time <= {$end_time}";
                    } else {
                        $sql_time = " first_trial_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  first_trial = 2 AND {$sql_time}";
                    break;
                //6签约通过人次
                case 6:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " signed_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " signed_time <= {$end_time}";
                    } else {
                        $sql_time = " signed_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count FROM  ml_member_status  WHERE  signed = 1 AND {$sql_time}";
                    break;
                /*//5芝麻认证通过人次
                case 5:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " zhima_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " zhima_time <= {$end_time}";
                    } else {
                        $sql_time = " zhima_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  zhima_auth = 1 AND {$sql_time}";
                    break;*/
                //7签约拒绝人次
                case 7:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " signed_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " signed_time <= {$end_time}";
                    } else {
                        $sql_time = " signed_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count FROM  ml_member_status  WHERE  signed = 2 AND {$sql_time}";
                    break;
                //8授信通过人次
                case 8:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " recheck_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " recheck_time <= {$end_time}";
                    } else {
                        $sql_time = " recheck_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  is_recheck = 1 AND {$sql_time}";
                    break;
                //9拍拍信通过人次
                case 9:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " ppc_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " ppc_time <= {$end_time}";
                    } else {
                        $sql_time = " ppc_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  is_ppc = 1 AND {$sql_time}";
                    break;
                //10拍拍信拒绝人次
                case 10:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " ppc_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " ppc_time <= {$end_time}";
                    } else {
                        $sql_time = " ppc_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  is_ppc = 2 AND {$sql_time}";
                    break;
                //11人脸通过人次
                case 11:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " id_verify_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " id_verify_time <= {$end_time}";
                    } else {
                        $sql_time = " id_verify_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  id_verify = 1 AND {$sql_time}";
                    break;
                //12人脸拒绝人次
                case 12:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " id_verify_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " id_verify_time <= {$end_time}";
                    } else {
                        $sql_time = " id_verify_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  id_verify = 2 AND {$sql_time}";
                    break;
                //13复审通过人次
                case 13:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " review_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " review_time <= {$end_time}";
                    } else {
                        $sql_time = " review_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  is_review = 1 AND {$sql_time}";
                    break;
                //14复审拒绝人次
                case 14:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " review_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " review_time <= {$end_time}";
                    } else {
                        $sql_time = " review_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count  FROM  ml_member_status  WHERE  is_review = 2 AND {$sql_time}";
                    break;
                /*//7决策树通过人次
                case 7:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " tree_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " tree_time <= {$end_time}";
                    } else {
                        $sql_time = " tree_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count FROM  ml_member_status  WHERE  tree = 1 AND {$sql_time}";
                    break;
                //8决策树拒绝人次
                case 8:
                    if ($start_time && empty($end_time)) {
                        $sql_time = " tree_time >= {$start_time}";
                    } elseif (empty($start_time) && $end_time) {
                        $sql_time = " tree_time <= {$end_time}";
                    } else {
                        $sql_time = " tree_time BETWEEN {$start_time} AND {$end_time}";
                    }
                    $sql = "SELECT COUNT(id) as count FROM  ml_member_status  WHERE  tree = 2 AND {$sql_time}";
                    break;*/
            }
            $count = M()->query($sql);
            foreach ($count as &$value) {
                $value['apply'] = '总数';
            }
            $count = json_encode($count, JSON_UNESCAPED_UNICODE);
            $msg = "{$count}";
            ajaxmsg($msg, 1);
        } else {
            $count = 0;
        }
        $this->display();
    }
}
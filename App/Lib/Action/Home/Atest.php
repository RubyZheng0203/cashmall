<?php

class AtestAction extends HCommonAction {

    public function phone(){
        $phone ="15021031142";
        echo $phone;
        /*$type_t ="";
        $txt=$this->get('https://www.so.com/s?q='.$phone);
        $info=$this->get_tag("class","mohe-mobileInfoContent",$txt,"td");
        $where=$this->get_tag("class","gclearfix mh-detail",$info[0],"div");
        if(count($where)==0){//不是骚扰电话
            $where=get_tag("class","mh-detail",$info[0],"p");
        }
        $info_txt= strip_tags ($where[0]);
        $info_array=explode("  ",$info_txt);
        $phone_t=$info_array[0];
        $where_t=$info_array[1];
        $cmcc_t=$info_array[2];
        $type=$this->get_tag("class","mohe-ph-mark",$info[0],"span");
        if(count($type)!=0){
            $type_t=$type[0];
        }
    
        $result=new Result();
        $result->phone=$phone_t;
        $result->where=$where_t;
        $result->cmcc=$cmcc_t;
        $result->type=$type_t;
    
        $json=json_encode($result,JSON_UNESCAPED_UNICODE);
        echo $json;*/
    }
    function get($url = '') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    function get_tag( $attr, $value, $xml, $tag=null ) {
        if( is_null($tag) )
            $tag = '\w+';
        else
            $tag = preg_quote($tag);
    
        $attr = preg_quote($attr);
        $value = preg_quote($value);
    
        $tag_regex = "/<(".$tag.")[^>]*$attr\s*=\s*".
            "(['\"])$value\\2[^>]*>(.*?)<\/\\1>/";
    
        preg_match_all($tag_regex,
            $xml,
            $matches,
            PREG_PATTERN_ORDER);
    
        return $matches[3];
    }
}

?>
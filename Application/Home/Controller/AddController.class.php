<?php
namespace Home\Controller;

class AddController extends LoginCheckController{
    public function index()
    {
        $this -> display();
    }
    
    public function add()
    {
        $_website = D('website');

        if(IS_POST){
            $website = I("post.website");
            if(empty($website)){
                return $this->error('你是不是忘记输入URL了呢');
            }

            $website = $this->correctURL(I("post.website"));
            if(!$website){
                return $this->error('URL格式不正确');
            }
            $checkin_type = I("post.checkin_type");
            $suser = I("post.suser");
            $spass = I("post.spass");
            $cookies = I("post.cookies");
        }else{
           return $this->error('非法请求');
        }

        switch ($checkin_type){
            case 1:
            if(empty($suser) || empty($spass)){
               return $this->error('你是不是忘记填写帐号或密码了呢？');
            }
            break;

            case 2:
            if(empty($cookies)){
                return $this->error('你是不是忘记填写Cookies了呢？');
            }
            break;

            default:
            return $this->error('非法请求');
        }

        $result = $_website->where(array('uid'=>session('uid'),'website'=>$website,'username'=>$suser,'password'=>$spass))->field('sid')->find();
        if($result){
            $this->error('请勿重复添加签到任务');
        }

        $result = $_website->add(array(
            'uid' => session('uid'),
            'checkin_type' => $checkin_type,
            'website' => $website,
            'username' => $suser,
            'password' => $spass,
            'cookies' => $cookies
        ));

        if($result){
            $this->success('添加成功','/Home/');
        }else{
            $this->error('添加失败','/Home/');
        }
    }

    protected function correctURL($url){
        $url = strtolower($url);
        if((strpos($url,'http://') !== 0) && (strpos($url,'https://') !== 0)){
            return false;
        }

        while (substr_count($url,'/')>2) {
            $url = substr($url,0,strrpos($url,'/'));
        }

        return $url;
    }
}
<?php
namespace Home\Controller;

class AddController extends LoginCheckController{
    public function index()
    {
        $this -> display();
    }
    
    public function add()
    {
        if(IS_POST){
           $website = I("post.website");
           $checkin_type = I("post.checkin_type");
           $suser = I("post.suser");
           $spass = I("post.spass");
           $cookies = I("post.cookies");
        }else{
           return $this->error('非法请求');
        }

        if(empty($website)){
            return $this->error('你是不是忘记填写URL了呢？');
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
            $this->error('非法请求');
        }

        $result = D('website')->add(array(
            'checkin_type' => 1,
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
}
<?php
namespace Home\Controller;

use Think\Controller;

class LoginCheckController extends Controller
{
    function __construct()
    {
        parent::__construct();

        if(empty(session('uid'))){
            redirect('/Home/Login');
            return;
        }

        $username = session('username');
        $password = session('password');
        $uid = session('uid');
        
        $result = D('user')->where(array('uid' => $uid,'username' => $username,'password' => $password))->find();

        if(empty($result)){
            session(null);
            redirect('/Home/Login');
        }
    }

    public function index()
    {
        redirect('Index/');
    }
}
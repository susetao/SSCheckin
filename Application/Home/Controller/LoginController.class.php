<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    public function index()
    {
        if (session('login')) {
            redirect('/Home/Index/');
        } else {
            $this->display();
        }
    }

    public function login()
    {
        if (IS_POST) {
            $username = I('post.username');
            $password = md5(I('post.password'));
            if(empty($username) || empty($password)){
                $this->display();
            }
            $result = D('user')->where(array('username' =>$username , 'password' => $password))->field('uid')->find();
            if ($result) {
                session('uid', $result['uid']);
                session('username', $username);
                session('password', $password);
                $this->success('登录成功', '/Home/Index/');
            } else {
                $this->error('帐号或密码错误');
            }
        } else {
            $this->display();
        }
    }
}
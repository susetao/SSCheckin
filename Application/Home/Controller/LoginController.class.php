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
            $password = I('post.password');
            if (D('settings')->where(array('key' => 'password', 'value' => $password))->find()) {
                session('login', '1');
                $this->success('登录成功', '/Home/Index/');
            } else {
                $this->error('密码错误', '/Home/Login/');
            }
        } else {
            $this->display();
        }
    }
}
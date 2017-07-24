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
            $user_count = D('user')->count();
            $website_count = D('website')->count();

            $this->assign('count',array(
                'user' => $user_count,
                'website' => $website_count
            ));

            $this->display();
        }
    }

    public function verify()
    {
        $Verify = new \Think\Verify(array(
            'fontSize' => 40,
            'length' => 4
        ));
        $Verify->entry();
    }

    private function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    public function signup()
    {
        if(IS_POST){
            $rules = array(
                array('username','require','帐号必须！','1',),
                array('password','require','密码必须！','1'),
                // array('code','require','验证码必须！','1'),
                array('password','repassword','两次输入的密码不一致！','1','confirm'),
                array('username','','这个用户名已经被注册了，换一个吧',0,'unique'),
            );

            // var_dump(I('session.'));
            // var_dump($this->check_verify($code));

            // die();
            // if(!$this->check_verify($code)){
            //     return $this->error('验证码错啦');
            // }
            $_user = D('user');

            if($_user->validate($rules)->create()){
                $_user->password = md5($_user->password);
                $_user->add();
                $this->success('注册成功');
            }else{
                $this->error($_user->getError());
            }

        }else{
            $this->error('非法请求');
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
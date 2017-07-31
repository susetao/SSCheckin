<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_website = D('website');
        $this->_user = D('user');
    }

    public function index()
    {
        if (session('login')) {
            redirect('/Home/Index/');
        } else {
            $user_count = $this->_user->count();
            $website_count = $this->_website->count();

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

            if($this->_user->validate($rules)->create()){
                $this->_user->password = md5($this->_user->password);
                $this->_user->register_time = date('Y-m-d H:i:s');
                $uid = $this->_user->add();
                if($uid){
                    $this->ip($uid);
                    $this->success('注册成功');
                }else{
                    $this->error('注册失败了');
                }
            }else{
                $this->error($this->_user->getError());
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
            $result = $this->_user->where(array('username' =>$username , 'password' => $password))->field('uid')->find();
            if ($result) {
                session('uid', $result['uid']);
                session('username', $username);
                session('password', $password);
                $this->_user->where('uid='.$result['uid'])->save(array('login_time' => date('Y-m-d H:i:s')));
                $this->ip($result['uid']);
                $this->success('登录成功', '/Home/Index/');
            } else {
                $this->error('帐号或密码错误');
            }
        } else {
            $this->display();
        }
    }

    protected function ip($uid){
        $ip_json = $this->_user->where('uid='.$uid)->getField('ip');
        $ip_arr = json_decode($ip_json,true);

        $current_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        if(empty($current_ip)) $current_ip = $_SERVER['REMOTE_ADDR'];

        if(array_key_exists($current_ip,$ip_arr)){
            $ip_arr[$current_ip] ++;
        }else{
            $ip_arr[$current_ip] = 1;
        }

        $ip_json = json_encode($ip_arr);
        $this->_user->where('uid='.$uid)->save(array('ip'=>$ip_json));
    }
}
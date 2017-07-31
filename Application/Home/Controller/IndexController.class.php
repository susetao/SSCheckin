<?php
namespace Home\Controller;
class IndexController extends LoginCheckController
{
    public function index()
    {
        $table_data = array();
        foreach (D('website')->where('uid='.session('uid'))->select() as $value) {
            $website_name = $value['website_name'];
            if (empty($website_name)) $website_name = $value['website'];

            array_push($table_data, array(
                'sid'          => $value['sid'],
                'website'      => $value['website'].'/user',
                'website_name' => $website_name,
                'username'     => $value['username'],
                'last_result'  => $value['last_result'],
                'last_time'    => $value['last_time'],
                'data_remain'  => $value['data_remain']
            ));
        }
        $this->assign('table_data',$table_data);
        $this->display();
    }

    public function userRequire()
    {
        if(empty(I('get.id'))){
            return $this->error('非法请求');
        }
        $uid = I('get.id');

        $result = D('user')->where('uid='.$uid)->field('require')->find();
        if($result['require']){
            echo '不要重复提交哦';
            return;
        }

        if(D("user")->where("uid=".$uid)->save(array('require' => 1))){
            echo '向服务器发送请求成功，你可以稍后回来再查看结果';
        }else{
            echo '操作失败';
        }
    }
}
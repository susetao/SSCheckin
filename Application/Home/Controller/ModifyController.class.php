<?php
namespace Home\Controller;

class ModifyController extends LoginCheckController{
    public function index()
    {
        $this->chcek();
        $sid = I("get.id");

        $result = D('website')->where('sid='.$sid)->find();
        $this->assign('result',$result);
        $this -> display();
    }

    public function del()
    {
        $this->chcek();
        $sid = I('get.id');
        if(D("website")->where("sid=".$sid)->delete()){
            $this->success('删除成功','/Home/');
        }else{
            $this->error('删除失败');
        }
    }

    public function mod()
    {
        $this->chcek();
        if(IS_POST){
            $sid = I('get.id');
            $checkin_type = I('post.checkin_type');
            $website_name = I('post.website_name');
            $website = I('post.website');
            $suser = I('post.suser');
            $spass = I('post.spass');
            $cookies = I('post.cookies');
            
            switch ($checkin_type){
                case 1:
                if(empty($suser) || empty($spass)){
                return $this->error('你是不是忘记填写某些字段了呢？');
                }
                break;

                case 2:
                if(empty($cookies)){
                    return $this->error('你是不是忘记填写某些字段了呢？');
                }
                break;

                default:
                return $this->error('非法请求');
            }

            $result = D('website')->where('sid='.$sid)->save(array(
                'checkin_type' => $checkin_type,
                'website' => $website,
                'website_name' => $website_name,
                'username' => $suser,
                'password' => $spass,
                'cookies' => $cookies
            ));
            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }

        }else{
            return $this->error('非法请求');
        }
    }

    public function log()
    {
        $this->chcek();
        $sid = I("get.id");
        $result = D('log')->where('sid='.$sid)->order('id DESC')->limit(30)->select();
        echo '<table class="table"><tr><th>时间</th><th>日志</th></tr>';
        foreach ($result as $key => $value) {
            # code...
            echo '<tr>';
            echo '<td>';
            echo $value['time'];
            echo '</td>';
            echo '<td>';
            echo $value['result'];
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    public function clearCache()
    {
        $this->chcek();
        $sid = I("get.id");
        $result = D('website')->where('sid='.$sid)->field('checkin_type')->find();
        $checkin_type = $result['checkin_type'];
        switch ($checkin_type) {
            case '1':
                # code...
                $result = D('website')->where('sid='.$sid)->save(array(
                    'website_name' => '',
                    'cookies' => '',
                    'site_type' => ''
                ));
                break;

            case '2':
            # code...
                $result = D('website')->where('sid='.$sid)->save(array(
                    'website_name' => '',
                    'username' => '',
                    'site_type' => ''
                ));
            break;
            
            default:
                return $this->error('非法请求');
                break;
        }

        if ($result) {
            # code...
            $this->success('清除缓存成功');
        }else{
            $this->error('清除缓存失败');
        }
    }

    private function chcek(){
        if(empty(I("get.id"))){
            return $this->error('非法请求');
        }

        $result = D('website')->where(array('uid'=>session('uid'),'sid'=>I("get.id")));
        if(empty($result)){
            return $this->error('非法请求');
        }
    }
}
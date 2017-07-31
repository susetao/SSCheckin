<?php
namespace Home\Controller;

class ModifyController extends LoginCheckController{
    public function __construct(){
        parent::__construct();
        if(empty(I("get.id"))){
            return $this->error('非法请求');
        }        

        $this->sid = I("get.id");
        $this->uid = session('uid');

        $result = D('website')->where(array('uid'=>$this->uid,'sid'=>$this->sid))->find();
        if(empty($result)){
            return $this->error('非法请求');
        }
    }

    public function index()
    {
        $result = D('website')->where(array('sid'=>$this->sid))->find();
        $this->assign('result',$result);
        $this -> display();
    }

    public function del()
    {
        if(D("website")->where(array('sid'=>$this->sid))->delete()){
            D('log')->where(array('sid'=>$this->sid))->delete();
            $this->success('删除成功','/Home/');
        }else{
            $this->error('删除失败');
        }
    }

    public function mod()
    {
        if(IS_POST){
            $website = I("post.website");
            if(empty($website)){
                return $this->error('你是不是忘记输入URL了呢');
            }

            $website = $this->correctURL(I("post.website"));
            if(!$website){
                return $this->error('URL格式不正确');
            }

            $checkin_type = I('post.checkin_type');
            $website_name = I('post.website_name');
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

            $result = D('website')->where(array('sid'=>$this->sid))->save(array(
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
        $result = D('log')->where(array('sid'=>$this->sid))->order('id DESC')->limit(5)->select();
        echo '<table class="table"><tr><th>时间</th><th>日志</th></tr>';
        foreach ($result as $value) {
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
        $result = D('website')->where('sid='.$this->sid)->field('checkin_type')->find();
        $checkin_type = $result['checkin_type'];
        switch ($checkin_type) {
            case '1':
                $result = D('website')->where('sid='.$this->sid)->save(array(
                    'website_name' => '',
                    'cookies' => '',
                    'site_type' => ''
                ));
                break;

            case '2':
                $result = D('website')->where('sid='.$this->sid)->save(array(
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
            $this->success('清除缓存成功');
        }else{
            $this->error('清除缓存失败');
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
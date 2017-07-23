<?php
namespace Home\Controller;

use Think\Controller;

class TaskController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_website = D('website');
        $this->_log = D('log');
        $this->_user = D('user');
    }

    public function index()
    {
        $num_once = 5;

        //读上一次的位置
        $result = $this->_log->where('sid=0 and result!="userRequire"')->order('id DESC')->find();
        $last_id = $result['result'];
        $last_time = $result['time'];
        if($last_time){
            if(strtotime($last_time.' +1 minute') > strtotime('now')){            
                echo '午时未到ヽ(●-`Д´-)ノ';
                return;
            }
        }

        if(empty($last_id)){
            $last_id = 0;
        }

        $result = $this->_website->where('sid>'.$last_id)->field('sid')->limit($num_once)->select();

        if(count($result) < $num_once){
            $result_ = $this->_website->field('sid')->limit($num_once - count($result))->select();
            $checkin_query = array_merge($result,$result_);
        }else{
            $checkin_query = $result;
        }

        $this->_log->add(array(
            'time' => date('Y-m-d H:i:s'),
            'sid' => 0,
            'result' => $checkin_query[count($checkin_query) - 1]['sid']
        ));

        foreach ($checkin_query as $value) {
            $this->checkin($value['sid']);
        }
    }

        public function userRequire()
        {
            //读上一次的时间
            $result = $this->_log->where('sid=0 and result="userRequire"')->order('id DESC')->find();
            $last_time = $result['time'];
            if($last_time){
                if(strtotime($last_time.' +2 minute') > strtotime('now')){            
                    echo '午时未到ヽ(●-`Д´-)ノ';
                    return;
                }
            }

            $this->_log->add(array(
                'time' => date('Y-m-d H:i:s'),
                'sid' => 0,
                'result' => 'userRequire'
            ));

            $result = $this->_user->where('`require`=1')->field('uid')->find();
            $uid = $result['uid'];
            if($uid){
                $result = $this->_website->where('uid='.$uid)->field('sid')->select();
                foreach ($result as $value) {
                    $this->checkin($value['sid']);
                }
                $this->_user->where('uid='.$uid)->save(array('require'=>0));
            }
        }

        protected function checkin($sid = 0)
    {
        $this->sid = $sid;

        ini_set('max_execution_time', 300);
        
        if($this->sid == 0){
            echo '<table class="table">';
            foreach ($this->_website->field('sid')->select() as $value) {
                echo '<tr><td>';
                $this->checkin($value['sid']);
                echo '</td></tr>';
            }
            echo '</table>';
            return;
        }

        $value = $this->_website->where(array('sid' => $this->sid))->find();

        $this->website = $value['website'];
        $this->checkin_type = $value['checkin_type'];
        $this->username = $value['username'];
        $this->password = $value['password'];
        $this->cookies = $value['cookies'];
        $this->website_name = $value['website_name'];
        $this->site_type = $value['site_type'];
        $this->tried = $value['tried'];
        if($this->tried>=30){
            $this->$_website->where(array('sid' => $this->sid))->delete();
            $this->$_log->where(array('sid' => $this->sid))->delete();
            return 0;
        }

        if(empty($this->site_type)){
            $this->site_type = $this->getType();
            $this->_website->where(array('sid' => $this->sid))->save(array('site_type'=>$this->site_type));
        }

        if(empty($this->website)){
            return;
        }

        echo $this->sid.':';
        
        //获取网站访问状态
        $headers = get_headers($this->website);
        $responce_code = $this->getResponceCode($headers);
        if($responce_code != 200){
            echo '网站不能正常访问，停止执行签到任务.';
            $this->saveLog('网站不能正常访问，停止执行签到任务;[Responce_Code]:' . $responce_code . ';[HTTP_header]:' . implode(' ',$headers));
            $this->_website->where(array('sid' => $this->sid))->save(array('last_result' => 0));
        }else{
            //网站可访问检查完成，开始执行签到任务
            echo '网站可访问->';
            if(empty($this->cookies)){
                //cookies为空
                echo 'cookies为空->';
                if($this->checkin_type == 1){
                    echo 'checkin_type为帐号密码,用帐号密码登录->';
                    if($this->_login()){
                        //登录成功
                        echo '登录成功->';
                        if($this->_checkin()){
                            //签到成功
                            echo '签到成功,cookies存起来下次用.';
                            $this->_website->where(array('sid' => $this->sid))->save(array(
                                'tried' => 0,
                                'last_result' => 1,
                                'cookies'     => $this->cookies //cookies存起来下次用
                                ));
                        }else{
                            //签到失败
                            echo '签到失败,详情请看日志.';
                            $this->_website->where(array('sid' => $this->sid))->save(array('last_result' => 0));
                            $this->_website->where(array('sid' => $this->sid))->setInc('tried');
                        }
                    }else{
                        //登录失败
                        echo '登录失败,详情请看日志.';
                        $this->_website->where(array('sid' => $this->sid))->save(array('last_result' => 0));
                        $this->_website->where(array('sid' => $this->sid))->setInc('tried');
                    }
                }else{
                    //没有cookies,没有帐号密码
                    echo '没有cookies,没有帐号密码,无法登录.';
                    $this->saveLog('没有cookies,没有帐号密码,你让我怎么签到???');
                    $this->_website->where(array('sid' => $this->sid))->save(array('last_result' => 0));
                    $this->_website->where(array('sid' => $this->sid))->setInc('tried');
                }
            }else{
                //cookies不为空
                echo 'cookies不为空->';
                if($this->checkin_type == 1){
                    //先尝试用cookies签到
                    echo 'checkin_type为帐号密码,先尝试使用cookies签到->';
                    $checkin_result = $this->_checkin();
                    if($checkin_result){
                        //签到成功
                        echo '签到成功.';
                        $this->_website->where(array('sid' => $this->sid))->save(array(
                            'tried' => 0,
                            'last_result' => 1
                            ));
                    }else{
                        //签到失败,尝试用帐号密码签到
                        echo 'cookies方式签到失败,尝试用帐号密码登录->';
                        $this->cookies = $this->_login();
                        if($this->cookies){
                            //登录成功
                            echo '登录成功->';
                            $checkin_result = $this->_checkin();
                            if($checkin_result){
                                //签到成功
                                echo '签到成功,更新cookies下次用.';
                                $this->_website->where(array('sid' => $this->sid))->save(array(
                                    'tried' => 0,
                                    'last_result' => 1,
                                    'cookies'     => $this->cookies //更新cookies下次用
                                    ));
                            }else{
                                //签到失败
                                echo '签到失败,详情请看日志.';
                                $this->_website->where(array('sid' => $this->sid))->save(array(
                                    'last_result' => 0,
                                    'cookies' => '' //错误的cookie删除
                                    ));
                                $this->_website->where(array('sid' => $this->sid))->setInc('tried');
                            }
                        }else{
                            //登录失败
                            echo '登录失败,详情请看日志.';
                            $this->_website->where(array('sid' => $this->sid))->save(array(
                                'last_result' => 0,
                                'cookies' => ''
                                ));
                            $this->_website->where(array('sid' => $this->sid))->setInc('tried');
                        }
                    }

                }elseif($this->checkin_type == 2){
                    echo 'checkin_type为cookies,尝试用cookies签到->';
                    $checkin_result = $this->_checkin();
                    if($checkin_result){
                        //签到成功
                        echo '签到成功.';
                        $this->_website->where(array('sid' => $this->sid))->save(array(
                            'tried' => 0,
                            'last_result' => 1
                            ));
                    }else{
                        //签到失败
                        echo '签到失败,可能是cookies失效失效啦.';
                        // $this->saveLog('签到失败,可能是cookies失效啦');
                        $this->_website->where(array('sid' => $this->sid))->save(array('last_result' => 0));
                        $this->_website->where(array('sid' => $this->sid))->setInc('tried');
                    }

                }else{
                    $this->saveLog('未知的签到方式,黑人问号脸???;[ss_checkin_type]:'.$this->checkin_type);
                    $this->_website->where(array('sid' => $this->sid))->save(array('last_result' => 0));
                    $this->_website->where(array('sid' => $this->sid))->setInc('tried');
                }
            }
        }
    }

    //0:不支持;1:STAFF(ss-panel-mod)或ss-panel-3;2:ss-panel2;
    protected function getType(){
        if($this->getResponceCode(get_headers($this->website.'/staff')) == 200){
            return 1;
        }elseif($this->getResponceCode(get_headers($this->website.'/tos')) == 200){
            return 1;
        }elseif($this->getResponceCode(get_headers($this->website.'/user/tos.php')) == 200){
            return 2;
        }else{
            return 0;
        }
    }

    protected function getResponceCode($header){
        if(is_array($header)){
            $header = implode(' ',$header);
        }
            
        if(preg_match('/(?<=HTTP\/1\.\d )\d{3}/',$header,$preg_results)){
            return $preg_results[0];
        }else{
            return FALSE;
        }
    }

    protected function saveLog($log)
    {
        $result = $this->_log->where(array('sid' => $this->sid))->order('id DESC')->find();
        if($result['result'] == $log){
            $this->_log->where('id='.$result['id'])->delete();
        }
        //删除旧的，加入新的
        $this->_log->add(array(
            'sid' => $this->sid,
            'result' => $log
        ));
    }

    protected function _login(){
        $ch = curl_init();

        switch ($this->site_type){
            case 1:
            curl_setopt_array($ch,array(
                CURLOPT_URL => $this->website.'/auth/login',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
                CURLOPT_TIMEOUT  => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HEADER => true,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => array(
                    'email'       => $this->username,
                    'passwd'      => $this->password,
                    'code'        => '',
                    'remember_me' => 'week'
                )
            ));
            $curl_result = curl_exec($ch);

            if(preg_match('/{.*}/',$curl_result,$preg_results)){
                //JSON匹配成功
                $web_response = json_decode($preg_results[0],true);

                if($web_response['ret']){
                    //登录成功
                    if(preg_match_all('/(?<=Set-Cookie: ).*=.*;(?= e)/',$curl_result,$preg_results)){
                        $this->cookies = implode(' ',$preg_results[0]);
                        return 1;
                    }
                }else{
                    //登录失败
                    if(is_array($web_response)){
                        $this->saveLog('登录失败;[web_response]:'.$web_response['msg']);
                        return 0;
                    }else{
                        $this->saveLog('登录失败;[preg_results[0]]:'.$preg_results[0]);
                        return 0;
                    }
                }
            }else{
                //JSON匹配失败
                $this->saveLog('JSON匹配失败;[curl_result]:'.strip_tags($curl_result));
                return 0;
            }
            break;

            case 2:
            curl_setopt_array($ch,array(
                CURLOPT_URL => $this->website.'/user/_login.php',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
                CURLOPT_TIMEOUT  => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HEADER => true,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => array(
                    'email'       => $this->username,
                    'passwd'      => $this->password,
                    'remember_me' => 'week'
                )
            ));

            $curl_result = curl_exec($ch);

            if(preg_match('/{.*}/',$curl_result,$preg_results)){
                //JSON匹配成功
                $web_response = json_decode($preg_results[0],true);

                if($web_response['code']){
                    //登录成功
                    if(preg_match_all('/(?<=Set-Cookie: ).*=.*;(?= e)/',$curl_result,$preg_results)){
                        $this->cookies = implode(' ',$preg_results[0]);
                        return 1;
                    }
                }else{
                    //登录失败
                    if(is_array($web_response)){
                        $this->saveLog('登录失败;[web_response]:'.$web_response['msg']);
                        return 0;
                    }else{
                        $this->saveLog('登录失败;[preg_results[0]]:'.$preg_results[0]);
                        return 0;
                    }
                }
            }else{
                //JSON匹配失败
                $this->saveLog('JSON匹配失败;[curl_result]:'.strip_tags($curl_result));
                return 0;
            }
            break;

            default:
            $this->saveLog('这个网站好像不支持自动签到呐');
            return 0;
        }

        curl_close($ch);
    }
    
    protected function _checkin(){
        $ch = curl_init();

        switch ($this->site_type){
            case 1:
            curl_setopt_array($ch,array(
                CURLOPT_URL => $this->website.'/user/checkin',
                CURLOPT_REFERER  => $this->website.'/user',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
                CURLOPT_TIMEOUT  => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIE => $this->cookies
            ));
            $curl_result = curl_exec($ch);

            if(preg_match('/{.*}/',$curl_result,$preg_results)){
                //JSON匹配成功
                $web_response = json_decode($preg_results[0],true);

                if($web_response['ret']){
                    //签到成功
                    $this->saveLog('签到成功;[web_response]:'.$web_response['msg']);

                    //更新网站信息
                    curl_setopt($ch,CURLOPT_URL,$this->website.'/user');
                    curl_setopt($ch,CURLOPT_POST,false);
                    $curl_result = curl_exec($ch);

                    if(preg_match_all('/\d*\.\d*GB/',$curl_result,$preg_results)){
                        $data_remain = array_pop($preg_results[0]);
                        $this->_website->where(array('sid' => $this->sid))->save(array('data_remain'=>$data_remain));
                    }
                    if(preg_match('/(?<=<code>)\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',$curl_result,$preg_results)){
                        $last_time = $preg_results[0];
                        $this->_website->where(array('sid' => $this->sid))->save(array('last_time'=>$last_time));
                    }
                    if(empty($this->website_name)){
                        if(preg_match('/(?<=<title>).*(?=<\/title>)/',$curl_result,$preg_results)){
                            $this->website_name = $preg_results[0];
                            $this->_website->where(array('sid' => $this->sid))->save(array('website_name'=>$this->website_name));
                        }
                    }
                    if(empty($this->username)){
                        curl_setopt($ch,CURLOPT_URL,$this->website.'/user/profile');
                        $curl_result = curl_exec($ch);
                        if(preg_match('/(?<=<dd>).*@.*(?=<\/dd>)/',$curl_result,$preg_results)){
                            $this->username = $preg_results[0];
                            $this->_website->where(array('sid' => $this->sid))->save(array('username'=>$this->username));
                        }
                    }
                    return 1;

                }else{
                    //签到失败
                    if(is_array($web_response)){
                        $this->saveLog('签到失败;[web_response]:'.$web_response['msg']);
                        return 0;
                    }else{
                        $this->saveLog('签到失败;[preg_results[0]]:'.$preg_results[0]);
                        return 0;
                    }
                }
            }else{
                //JSON匹配失败
                $this->saveLog('JSON匹配失败;[curl_result]:'.strip_tags($curl_result));
                return 0;
            }
            break;

            case 2:
            curl_setopt_array($ch,array(
                CURLOPT_URL => $this->website.'/user/_checkin.php',
                CURLOPT_REFERER  => $this->website.'/user',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
                CURLOPT_TIMEOUT  => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIE => $this->cookies
            ));
            $curl_result = curl_exec($ch);

            if(preg_match('/{.*}/',$curl_result,$preg_results)){
                //JSON匹配成功
                $web_response = json_decode($preg_results[0],true);
                //签到成功
                $this->saveLog('签到成功;[web_response]:'.$web_response['msg']);

                //更新网站信息
                    curl_setopt($ch,CURLOPT_URL,$this->website.'/user');
                    curl_setopt($ch,CURLOPT_POST,false);
                    $curl_result = curl_exec($ch);

                    if(preg_match_all('/\d*\.\d*GB/',$curl_result,$preg_results)){
                        $data_remain = array_pop($preg_results[0]);
                        $this->_website->where(array('sid' => $this->sid))->save(array('data_remain'=>$data_remain));
                    }
                    if(preg_match_all('/(?<=<code>)\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',$curl_result,$preg_results)){
                        $last_time = array_pop($preg_results[0]);
                        $this->_website->where(array('sid' => $this->sid))->save(array('last_time'=>$last_time));
                    }
                    if(empty($this->website_name)){
                        if(preg_match('/(?<=<title>).*(?=<\/title>)/',$curl_result,$preg_results)){
                            $this->website_name = $preg_results[0];
                            $this->_website->where(array('sid' => $this->sid))->save(array('website_name'=>$this->website_name));
                        }
                    }
                    if(empty($this->username)){
                        curl_setopt($ch,CURLOPT_URL,$this->website.'/user/my.php');
                        $curl_result = curl_exec($ch);
                        if(preg_match('/(?<=：).*@.*\..*(?=<\/p>)/',$curl_result,$preg_results)){
                            $this->username = $preg_results[0];
                            $this->_website->where(array('sid' => $this->sid))->save(array('username'=>$this->username));
                        }
                    }

                return 1;

            }else{
                //JSON匹配失败
                $this->saveLog('JSON匹配失败;[curl_result]:'.strip_tags($curl_result));
                return 0;
            }
            break;

            default:
            return 0;
        }

        curl_close($ch);
    }
}
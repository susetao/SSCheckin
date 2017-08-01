<?php
namespace Home\Controller;

use Think\Controller;

class TaskController extends Controller
{
    public function __construct()
    {
        ini_set('max_execution_time', 0);
        parent::__construct();

        $this->_website = D('website');
        $this->_log = D('log');
        $this->_user = D('user');
    }

    public function index()
    {
        $num_once = 10;

        //读上一次的位置
        $result = $this->_log->where('sid=0')->order('id DESC')->find();
        $last_id = $result['result'];
        $last_time = $result['time'];
        $last_log_id = $result['id'];
        if($last_time){
            if(strtotime($last_time.' +1 minute') > strtotime('now')){
                echo '午时未到ヽ(●-`Д´-)ノ';
                return;
            }
        }

        $result = $this->_website->order('sid DESC')->find();
        $max_id = $result['sid'];

        if(empty($last_id) || $last_id == $max_id || !is_numeric($last_id)){
            $last_id = 0;
        }

        $checkin_query = $this->_website->where('sid>'.$last_id)->field('sid')->limit($num_once)->select();

        $this->_log->add(array(
            'time' => date('Y-m-d H:i:s'),
            'sid' => 0,
            'result' => $checkin_query[count($checkin_query) - 1]['sid']
        ));
        if($last_log_id) $this->_log->where('id='.$last_log_id)->delete();

        foreach ($checkin_query as $value) {
            $this->checkin($value['sid']);
        }
    }

    protected function countNum($v = true)
    {
        if($v){
            if(session('uid') != 1) return;
        }

        $arr = $this->_user->field('uid')->select();
        foreach ($arr as $value) {
            $uid = $value['uid'];
            $website_num = $this->_website->where("uid=$uid")->count();
            echo $uid,':',$website_num,'<br/>';
            $this->_user->where("uid=$uid")->save(array('website_num'=>$website_num));
        }
    }

    public function month()
    {
        if(date('j') != 1) return;

        $this->countNum(false);

        $this_year = date('Y');
        $last_year = $this_year - 1;
        $this_month = date('n');
        $last_month = $this_month - 1;

        $last_time = $this->_log->where('sid=1 and result="empty"')->getField('time');
        if($last_time){
            if(date_format(date_create($last_time),'n') == $this_month) return;
        }

        $clear_time = "{$this_year}-{$last_month}-1";

        if($last_month < 1) {
            $clear_time = "{$last_year}-12-1";
        }

        $this->_user->where("register_time<'$clear_time' AND login_time<'$clear_time' AND website_num=0")->delete();
        $Model = new \Think\Model();
        $Model->execute("TRUNCATE log");
        $this->_log->add(array(
            'time' => date('Y-m-d H:i:s'),
            'sid' => 1,
            'result'=>'empty'
            ));
        echo '每月任务完成';
    }

    public function backup(){
        $this->month();

        $result = $this->_log->where('sid=1 and result="backup"')->order('id DESC')->find();

        $last_time = $result['time'];
        $last_log_id = $result['id'];

        if($last_time){
            if(strtotime($last_time.' +60 minutes') > strtotime('now')){
                echo '午时未到ヽ(●-`Д´-)ノ';
                return;
            }
        }

        $result = $this->_user->select();
        $sql_user = '';
        foreach ($result as $value) {
            $uid = $value['uid'];
            $username = $value['username'];
            $password = $value['password'];
            $require = $value['require'];
            $register_time = $value['register_time'];
            $login_time = $value['login_time'];
            $ip = $value['ip'];
            $sql_user .= "INSERT INTO `user` (`uid`, `username`, `password`, `require`, `register_time`, `login_time`, `ip`) VALUES ('$uid', '$username', '$password', '$require', '$register_time', '$login_time', '$ip');\n";
        }

        $result = $this->_website->select();
        $sql_website = '';
        foreach ($result as $value) {
            $sid = $value['sid'];
            $uid = $value['uid'];
            $website = $value['website'];
            $website_name = $value['website_name'];
            $username = $value['username'];
            $password = $value['password'];
            $cookies = $value['cookies'];
            $checkin_type = $value['checkin_type'];
            $last_result = $value['last_result'];
            $last_time = $value['last_time'];
            $data_remain = $value['data_remain'];
            $tried = $value['tried'];
            $sql_website .= "INSERT INTO `website` (`sid`, `uid`, `website`, `username`, `password`, `cookies`, `checkin_type`, `last_result`, `tried`) VALUES ('$sid', '$uid', '$website', '$username', '$password', '$cookies', '$checkin_type', '0', '$tried');\n";
        }

        if(!file_exists('backup/')){
            mkdir('backup/');
        }

        if(!file_exists('backup/'.date('Y-m-d'))){
            mkdir('backup/'.date('Y-m-d'));
        }

        $date = date('Y-m-d');
        $filename = date('H-i-s').'.sql';
        $local_path = "backup/$date/$filename";
        $remote_path = "backups/SSCheckin/$date/";

        if(file_put_contents($local_path,$sql_user.$sql_website)){
            $ftp_server = 'files.000webhost.com';
            $ftp_user_name = 'yorkist-stator';
            $ftp_user_pass = '12345678990';
            // $ftp_server = '127.0.0.1';
            // $ftp_user_name = '_ftp';
            // $ftp_user_pass = '123';
            $conn_id = ftp_connect($ftp_server);
            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
            if($conn_id && $login_result){

                ftp_mkdir($conn_id,$remote_path);

                if(ftp_put($conn_id , $remote_path . $filename , $local_path , FTP_BINARY)){
                    echo '备份成功';
                }else{
                    echo '上传到FTP服务器失败';
                    return;
                }

            }else{
                echo 'FTP连接失败';
                return;
            }
        }else{
            echo '写入文件失败';
            return;
        }

        ftp_close($conn_id);

        $this->_log->add(array(
            'time' => date('Y-m-d H:i:s'),
            'sid' => 1,
            'result' => 'backup'
            ));
        if($last_log_id) $this->_log->where('id='.$last_log_id)->delete();
    }

    public function userRequire()
    {
        //读上一次的时间
        $result = $this->_log->where('sid=1 and result="userRequire"')->order('id DESC')->find();
        $last_time = $result['time'];
        $last_log_id = $result['id'];
        if($last_time){
            if(strtotime($last_time.' +2 minute') > strtotime('now')){
                echo '午时未到ヽ(●-`Д´-)ノ';
                return;
            }
        }

        $result = $this->_user->where('`require`=1')->field('uid')->find();
        $uid = $result['uid'];
        if($uid){
            $this->_log->add(array(
                'time' => date('Y-m-d H:i:s'),
                'sid' => 1,
                'result' => 'userRequire'
            ));
            if($last_log_id) $this->_log->where('id='.$last_log_id)->delete();

            $result = $this->_website->where('uid='.$uid)->field('sid')->select();
            foreach ($result as $value) {
                $this->checkin($value['sid']);
            }
            $this->_user->where('uid='.$uid)->save(array('require'=>0));
        }
    }

        protected function checkin($sid)
    {
        $this->sid = $sid;

        $value = $this->_website->where(array('sid' => $this->sid))->find();

        $this->website = $value['website'];
        $this->checkin_type = $value['checkin_type'];
        $this->username = $value['username'];
        $this->password = $value['password'];
        $this->cookies = $value['cookies'];
        $this->website_name = $value['website_name'];
        $this->site_type = $value['site_type'];
        $this->tried = $value['tried'];
        $this->pause = $value['pause'];
        $this->last_result = $value['last_result'];

        if ($this->pause) {
            echo "暂停";
            return;
        }

        if($this->tried >= 20){
            $this->_website->where(array('sid' => $this->sid))->save(array('pause' => 0));
            return 0;
        }

        if(empty($this->site_type)){
            $this->site_type = $this->getType();
            $this->_website->where(array('sid' => $this->sid))->save(array('site_type'=>$this->site_type));
        }

        if(empty($this->website)){
            return;
        }

        echo '<br/>'.$this->sid.':';

        $result = $this->_log->where('sid='.$this->sid)->order('id DESC')->find();
        if($this->last_result && strtotime($result['time'].' +60 minutes') > strtotime('now')){
            echo '跳过';
            return 0;
        }

        //获取网站访问状态
        $headers = get_headers($this->website);
        $responce_code = $this->getResponceCode($headers);
        if($responce_code != 200){
            echo '网站不能正常访问，停止执行签到任务.';
            $this->saveLog('网站不能正常访问，停止执行签到任务;[Responce_Code]:' . $responce_code . ';[HTTP_header]:' . implode(' ',$headers));
            $this->_website->where(array('sid' => $this->sid))->save(array('last_result' => 0));
            $this->_website->where(array('sid' => $this->sid))->setInc('tried');
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
        $log = substr($log,0,255);//最大字符长度限制
        $log = str_replace(PHP_EOL,' ',$log);//删除换行
        $result = $this->_log->where(array('sid' => $this->sid))->order('id DESC')->find();
        if($result['result'] == $log){
            $this->_log->where('id='.$result['id'])->delete();
        }
        //删除旧的，加入新的
        $this->_log->add(array(
            'time' => date('Y-m-d H:i:s'),
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

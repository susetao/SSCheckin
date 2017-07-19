<?php
namespace Home\Controller;

use Think\Controller;

class LoginCheckController extends Controller
{
    function __construct()
    {
        parent::__construct();
        if (!session('login')) {
            redirect('/Home/Login');
        }
        // $Model = new \Think\Model();
        // $Model->query("SET time_zone = '+8:00'");
        // D()->query("SET time_zone = '+8:00'");
    }

    public function index()
    {
        redirect('Index/');
    }
}
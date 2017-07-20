<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{$Think.config.WEB_TITLE}</title>
    <link rel="stylesheet" href="/Public/bootstrap.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="/Public/favicon.icon" />
    <style>
    th,td{
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="inline-block">添加站点 <small>{$Think.config.WEB_TITLE}</small></h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?php echo U('/Home/Index/');?>">主页</a></li>
            <li class="active">添加站点</li>
        </ol>
        <form class="form-horizontal" method="post" action="<?php echo U('/Home/Add/add')?>">
            <div class="form-group">
                <label for="checkin_type" class="col-sm-2 control-label">签到方式</label>
                <div class="col-sm-10">
                    <select id="checkin_type" name="checkin_type" class="form-control">
                        <option value="1">帐号密码</option>
                        <option value="2">Cookies</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="url" class="col-sm-2 control-label">(*)URL</label>
                <div class="col-sm-10">
                    <input type="url" class="form-control" id="website" name="website" required>
                </div>
            </div>
            <div class="form-group up-group">
                <label for="suser" class="col-sm-2 control-label" id="suser-label">(*)帐号</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="suser" name="suser" required>
                </div>
            </div>
            <div class="form-group up-group">
                <label for="spass" class="col-sm-2 control-label" id="spass-label">(*)密码</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="spass" name="spass" required>
                </div>
            </div>
            <div class="form-group">
                <label for="cookies" class="col-sm-2 control-label" id="cookies-label">Cookies</label>
                <div class="col-sm-10">
                    <textarea id="cookies" name="cookies" class="form-control" placeholder="格式为fruit=apple; colour=red"></textarea>
                </div>
            </div>
            <div class="form-group" id="end-div">
                <div class="col-sm-offset-2 col-sm-10">
                    <p class="help-block">注意：带*的项目为必填项目<p>
                    <input type="submit" class="btn btn-default" onclick="//return submitCheck();">
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script>
        select = $("#checkin_type");

        selectChange();
        
        select.on('change',selectChange);

        function selectChange() {
            var value = select.val();
            if(value == 1){
                $('#suser').prop('required',true);
                $('#spass').prop('required',true);
                $('#suser-label').html('(*)帐号');
                $('#spass-label').html('(*)密码');

                $('#cookies').prop('required',false);
                $('#cookies-label').html('Cookies');
            }else if(value == 2){
                $('#suser').prop('required',false);
                $('#spass').prop('required',false);
                $('#suser-label').html('帐号');
                $('#spass-label').html('密码');

                $('#cookies').prop('required',true);
                $('#cookies-label').html('(*)Cookies');
            }else{
                window.location.reload();
            }
        }

        function submitCheck() {
            //判断url
            if(!$("#url").val()){
                return false;
            }

            //根据签到方式再判断帐号密码或Cookies
            var select = $("#checkin_type").val();
            if(select == '帐号密码'){
                if($("#suser").val() && $("#spass").val()){
                    return ture;
                }else{
                    return false;
                }
            }else if(select == 'Cookies'){
                if($("#cookies").val()){
                    return ture;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    </script>
</body>
</html>

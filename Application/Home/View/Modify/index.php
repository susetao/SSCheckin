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
            <h1 class="inline-block">站点信息 <small>{$Think.config.WEB_TITLE}</small></h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?php echo U('/Home/Index/');?>">主页</a></li>
            <li class="active">站点信息</li>
        </ol>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">修改信息</h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="<?php echo U('/Home/Modify/mod?id='.I('get.id'))?>">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?php echo I('get.id');?></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="checkin_type" class="col-sm-2 control-label">签到方式</label>
                        <div class="col-sm-10">
                            <select id="checkin_type" name="checkin_type" class="form-control">
                                <option <?php if($result['checkin_type'] == 1)echo 'selected';?> value="1">帐号密码</option>
                                <option <?php if($result['checkin_type'] == 2)echo 'selected';?> value="2">Cookies</option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="form-group up-group">
                        <label for="website_name" class="col-sm-2 control-label">网站名称</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="website_name" name="website_name" value="<?php echo $result['website_name'];?>">
                            <p class="help-block">如果网站名称为空那么在签到成功时会自动获取，当然你也可以自己设置</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="url" class="col-sm-2 control-label">(*)URL</label>
                        <div class="col-sm-10">
                            <input type="url" class="form-control" id="website" name="website" required  value="<?php echo $result['website'];?>">
                        </div>
                    </div>
                    
                    <div class="form-group up-group">
                        <label for="suser" class="col-sm-2 control-label" id="suser-label">(*)帐号</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="suser" name="suser" required value="<?php echo $result['username'];?>">
                        </div>
                    </div>
                    <div class="form-group up-group">
                        <label for="spass" class="col-sm-2 control-label" id="spass-label">(*)密码</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="spass" name="spass" required value="<?php echo $result['password'];?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cookies" class="col-sm-2 control-label" id="cookies-label">Cookies</label>
                        <div class="col-sm-10">
                            <textarea id="cookies" name="cookies" class="form-control" placeholder="格式为fruit=apple; colour=red"><?php echo $result['cookies'];?></textarea>
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
        </div>


        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">查看日志</h3>
            </div>
            <div class="panel-body" id="log">

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">其他选项</h3>
            </div>
            <div class="panel-body text-center">
                <p>如果在签到的时候遇到了各种奇奇怪怪的问题可以尝试一下这个选项。</p>
                <p>以帐号密码方式签到的网站会清除网站名称、Cookies、网站类型。</p>
                <p>以Cookies方式签到的网站会清除网站名称、帐号、网站类型。</p>
                <p>唯一会导致的问题就是使下次签到时间变长。</p>
                <a href="<?php echo U('/Home/Modify/clearCache?id='.I('get.id'));?>" class="btn btn-info btn-block"><span class="glyphicon glyphicon-flag"></span> 清除缓存</a>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">删除站点</h3>
            </div>
            <div class="panel-body">
                <button id="delete" class="btn btn-danger btn-block"><span class="glyphicon glyphicon-trash"></span> 删除此站点</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">警告</h4>
            </div>
            <div class="modal-body">你正在执行一项非常危险的操作，删除不可逆，确定删除？</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">手抖了</button>
                <a href="<?php echo U("/Home/Modify/del?id=".I('get.id'));?>" class="btn btn-danger">确定删除</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal -->

    <script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script>
        var select = $("#checkin_type");

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

        $("#delete").on('click',function() {
            $("#delete-modal").modal('show');
        })
        
        $("document").ready(function() {
            $("#log").load("<?php echo U('/Home/Modify/log?id='.I('get.id'));?>",'',function(response,status,xhr) {
                if(status != 'success'){
                    $('#Checkin-body').html('向服务器请求数据时出错:'+status);
                }
            });
        });
    </script>
</body>
</html>

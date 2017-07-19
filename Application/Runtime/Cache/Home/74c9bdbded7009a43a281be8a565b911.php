<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>SSCheckin</title>
    <link rel="stylesheet" href="/Public/bootstrap.min.css">
    <style>
    th,td{
        text-align: center;
    }
    </style>
</head>
<body>
<div class="container">
    <div class="table-responsive">
        <table class="table table-hover">
            <caption>站点签到信息</caption>
            <tr>
                <th>#</th>
                <th>站点</th>
                <th>用户名</th>
                <th>上次签到时间</th>
                <th>上次签到结果</th>
                <th>剩余流量</th>
                <th>查看日志</th>
            </tr>
            <?php
 foreach ($table_data as $value) { ?>
                <tr>
                    <td><?php echo $value['sid'];?></td>
                    <td>
                        <a target="_blank" href="<?php echo $value['website'];?>"><?php echo $value['website_name'];?></a>
                    </td>
                    <td><?php echo $value['username'];?></td>
                    <td><?php echo $value['last_time'];?></td>
                    <?php
 if($value['last_result']){ echo '<td class="success">成功</td>'; }else{ echo '<td class="danger">失败</td>'; } ?>
                    <td><?php echo $value['data_remain']?></td>
                    <td></td>
                </tr>
                <?php
 } ?>
        </table>
    </div>
    <div class="row text-center">
        <button id="checkin" class="btn btn-default">立刻执行签到</button>
        <a href="#" class="btn btn-default">查看所有日志</a>
    </div>
</div>

<div class="modal fade" id="Checkin-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="Checkin-title">
                    签到结果
                </h4>
            </div>
            <div id="Checkin-body" class="modal-body">
                签到中，请耐心等待签到结果...
            </div>
            <div class="modal-footer">
                <button id="refresh" class="btn btn-primary">我知道了</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script type="text/javascript">
$('#refresh').hide();

$('#checkin').on('click',function(){
    $('#Checkin-modal').modal('show');
    $('#modal-body').load('<?php echo U('/Home/Index/checkin');?>');
    $('#refresh').show();
});

$('#refresh').on('click',function(){
    window.location.reload(true);
})
</script>
</body>
</html>
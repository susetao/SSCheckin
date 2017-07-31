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
            <h1>签到信息 <small>{$Think.config.WEB_TITLE}</small></h1>
        </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>#</th>
                <th>站点</th>
                <th>用户名</th>
                <th>上次签到时间<a data-toggle="tooltip" title="这是从网站上直接抓取的时间，因为服务器时区设置可能不一样，这里显示的时间可能和你当地时间不一样">(?)</a></th>
                <th>上次签到结果</th>
                <th>剩余流量</th>
                <th>编辑</th>
            </tr>
            <?php
            foreach ($table_data as $value) {
                ?>
                <tr>
                    <td><?php echo $value['sid'];?></td>
                    <td>
                        <a target="_blank" href="<?php echo $value['website'];?>"><?php echo $value['website_name'];?></a>
                    </td>
                    <td><?php echo $value['username'];?></td>
                    <td><?php echo $value['last_time'];?></td>
                    <?php
                    if($value['last_result']){
                        echo '<td class="success">成功</td>';
                    }else{
                        echo '<td class="danger">失败</td>';
                    }
                    ?>
                    <td><?php echo $value['data_remain']?></td>
                    <td><a href="<?php echo U('Modify/index?id='.$value['sid']);?>"><span class="glyphicon glyphicon-zoom-in"></a></span></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
    <div class="row text-center">
        <a href="<?php echo U('/Home/Add/');?>" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> 添加签到站点</a>
        <button id="checkin" class="btn btn-default"><span class="glyphicon glyphicon-repeat"></span> 立刻执行签到</button>
    </div>
</div>

<div class="modal fade" id="Checkin-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Checkin-title">
                    提示
                </h4>
            </div>
            <div id="Checkin-body" class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script type="text/javascript">
$(function () { $("[data-toggle='tooltip']").tooltip(); });

$('#checkin').on('click',function(){
    $('#Checkin-modal').modal('show');
    $('#Checkin-body').load('<?php echo U('/Home/Index/userRequire?id='.session('uid'));?>','',function(response,status,xhr){
        if(status != 'success'){
            $('#Checkin-body').html('向服务器请求数据时出错:'+status);
        }
    });
});

</script>
</body>
</html>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{$Think.config.WEB_TITLE}</title>
    <link rel="stylesheet" href="/Public/bootstrap.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="/Public/favicon.icon" />
</head>
<body>
<div class="container">
    <div class="jumbotron">
        <h1>欢迎访问{$Think.config.WEB_TITLE}！</h1>
        <p>这是一个SS网站自动签到的网站，你需要登录才能查看更多内容！</p>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <form action="<?php echo U('Home/Login/login'); ?>" method="post">
                <div class="form-group">
                    <label for="password">请输入管理员密码</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <input type="submit" class="btn btn-primary btn-block">
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"
        integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn"
        crossorigin="anonymous"></script>
</body>
</html>
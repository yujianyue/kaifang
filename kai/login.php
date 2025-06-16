<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/login.php
// 文件大小: 6105 字节
/**
 * 本文件功能：登录模块
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 如果已经登录，则跳转到开方页面
if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    echo "<script>window.location.href = 'kai.php?do=iikai';</script>";
    exit;
}

// 处理AJAX请求
if (isset($_GET['act'])) {
    // 引入所需文件
    require_once './inc/conn.php';
    require_once './inc/pubs.php';
    
    $act = $_GET['act'];
    
    switch ($act) {
        case 'login': // 登录处理
            // 获取POST数据
            $username = post_param('username');
            $password = post_param('password');
            $captcha = post_param('captcha');
            
            // 检查验证码
            if (!isset($_SESSION['captcha_code']) || strtolower($captcha) != strtolower($_SESSION['captcha_code'])) {
                json_result(1, '验证码错误');
            }
            
            // 清除验证码，防止重复使用
            unset($_SESSION['captcha_code']);
            
            // 验证用户名和密码
            $mima_file = './inc/mima.php';
            
            // 如果密码文件不存在，则使用默认账号
            if (!file_exists($mima_file)) {
                // 使用默认账号验证
                if ($username == $default_admin['username'] && md5($password . 'kaifang') == $default_admin['password']) {
                    // 登录成功
                    $_SESSION['admin_id'] = 1;
                    $_SESSION['admin_name'] = $username;
                    
                    // 创建密码文件
                    $content = json_encode([
                        'username' => $username,
                        'password' => md5($password . 'kaifang')
                    ], JSON_UNESCAPED_UNICODE);
                    file_put_contents($mima_file, $content);
                    
                    json_result(0, '登录成功');
                } else {
                    json_result(1, '用户名或密码错误');
                }
            } else {
                // 读取密码文件
                $content = file_get_contents($mima_file);
                $admin = json_decode($content, true);
                
                // 验证用户名和密码
                if ($username == $admin['username'] && md5($password . 'kaifang') == $admin['password']) {
                    // 登录成功
                    $_SESSION['admin_id'] = 1;
                    $_SESSION['admin_name'] = $username;
                    
                    json_result(0, '登录成功');
                } else {
                    json_result(1, '用户名或密码错误');
                }
            }
            break;
            
        default:
            json_result(1, '未知操作');
            break;
    }
    
    exit;
}
?>

<div class="login-container">
    <div class="login-box">
        <h2 class="login-title">中医处方管理系统</h2>
        <form id="login-form" class="login-form">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="请输入用户名" required>
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" required>
            </div>
            <div class="form-group captcha-group">
                <label for="captcha">验证码</label>
                <div class="captcha-box">
                    <input type="text" id="captcha" name="captcha" class="form-control" placeholder="请输入验证码" required>
                    <img id="captcha-img" src="inc/code.php" alt="验证码" title="点击刷新" onclick="refreshCaptcha()">
                </div>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-primary btn-block" onclick="doLogin()">登录系统</button>
            </div>
        </form>
    </div>
</div>

<style>
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: calc(100vh - 120px);
}

.login-box {
    background-color: #fff;
    padding: 30px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

.login-title {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.login-form .form-group {
    margin-bottom: 20px;
}

.captcha-group .captcha-box {
    display: flex;
}

.captcha-box img {
    height: 36px;
    margin-left: 10px;
    cursor: pointer;
}

.btn-block {
    width: 100%;
}
</style>

<script>
// 刷新验证码
function refreshCaptcha() {
    document.getElementById('captcha-img').src = 'inc/code.php?' + Math.random();
}

// 登录处理
function doLogin() {
    // 获取表单数据
    let formData = getFormValues('login-form');
    
    // 验证表单
    if (!formData.username) {
        showToast('请输入用户名', 'error');
        return;
    }
    
    if (!formData.password) {
        showToast('请输入密码', 'error');
        return;
    }
    
    if (!formData.captcha) {
        showToast('请输入验证码', 'error');
        return;
    }
    
    // 发送登录请求
    ajaxRequest('kai.php?do=login&act=login', 'post', formData, function(res) {
        if (res.code === 0) {
            showToast(res.msg, 'success');
            // 登录成功，跳转到开方页面
            setTimeout(function() {
                window.location.href = 'kai.php?do=iikai';
            }, 1000);
        } else {
            showToast(res.msg, 'error');
            refreshCaptcha();
        }
    });
}

// 回车键登录
document.getElementById('login-form').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        doLogin();
    }
});
</script>

<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/ipass.php
// 文件大小: 5571 字节
/**
 * 本文件功能：修改密码
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 处理AJAX请求
if (isset($_GET['act'])) {
    // 引入所需文件
    require_once './inc/conn.php';
    require_once './inc/pubs.php';
    
    $act = $_GET['act'];
    
    switch ($act) {
        case 'change': // 修改密码
            // 获取POST数据
            $old_password = post_param('old_password');
            $new_password = post_param('new_password');
            $confirm_password = post_param('confirm_password');
            
            // 验证参数
            if (empty($old_password)) {
                json_result(1, '请输入原密码');
            }
            
            if (empty($new_password)) {
                json_result(1, '请输入新密码');
            }
            
            if ($new_password != $confirm_password) {
                json_result(1, '两次输入的新密码不一致');
            }
            
            // 读取密码文件
            $mima_file = './inc/mima.php';
            
            if (!file_exists($mima_file)) {
                // 使用默认账号验证
                if (md5($old_password . 'kaifang') != $default_admin['password']) {
                    json_result(1, '原密码错误');
                }
                
                // 创建密码文件
                $content = json_encode([
                    'username' => $default_admin['username'],
                    'password' => md5($new_password . 'kaifang')
                ], JSON_UNESCAPED_UNICODE);
                file_put_contents($mima_file, $content);
                
                json_result(0, '密码修改成功');
            } else {
                // 读取密码文件
                $content = file_get_contents($mima_file);
                $admin = json_decode($content, true);
                
                // 验证原密码
                if (md5($old_password . 'kaifang') != $admin['password']) {
                    json_result(1, '原密码错误');
                }
                
                // 更新密码
                $admin['password'] = md5($new_password . 'kaifang');
                
                // 保存更新
                $content = json_encode($admin, JSON_UNESCAPED_UNICODE);
                file_put_contents($mima_file, $content);
                
                json_result(0, '密码修改成功');
            }
            break;
            
        default:
            json_result(1, '未知操作');
            break;
    }
    
    exit;
}
?>

<div class="panel">
    <div class="panel-header">
        <h3>修改密码</h3>
    </div>
    <div class="panel-body">
        <form id="change-password-form" class="form">
            <div class="form-group">
                <label for="old_password">原密码</label>
                <input type="password" id="old_password" name="old_password" class="form-control" placeholder="请输入原密码" required>
            </div>
            <div class="form-group">
                <label for="new_password">新密码</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="请输入新密码" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">确认新密码</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="请再次输入新密码" required>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-primary" onclick="changePassword()">提交修改</button>
                <button type="button" class="btn btn-default" onclick="resetForm()">重置</button>
            </div>
        </form>
    </div>
</div>

<style>
.panel {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.panel-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.panel-header h3 {
    margin: 0;
    font-size: 18px;
}

.panel-body {
    padding: 20px;
}

.form {
    max-width: 500px;
}

.btn-default {
    color: #333;
    background-color: #f8f9fa;
    border-color: #ddd;
}

.btn-default:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}
</style>

<script>
// 修改密码
function changePassword() {
    // 获取表单数据
    let formData = getFormValues('change-password-form');
    
    // 验证表单
    if (!formData.old_password) {
        showToast('请输入原密码', 'error');
        return;
    }
    
    if (!formData.new_password) {
        showToast('请输入新密码', 'error');
        return;
    }
    
    if (!formData.confirm_password) {
        showToast('请确认新密码', 'error');
        return;
    }
    
    if (formData.new_password !== formData.confirm_password) {
        showToast('两次输入的新密码不一致', 'error');
        return;
    }
    
    // 发送修改密码请求
    ajaxRequest('kai.php?do=ipass&act=change', 'post', formData, function(res) {
        if (res.code === 0) {
            showToast(res.msg, 'success');
            // 修改成功，重置表单
            document.getElementById('change-password-form').reset();
        } else {
            showToast(res.msg, 'error');
        }
    });
}

// 重置表单
function resetForm() {
    document.getElementById('change-password-form').reset();
}
</script>

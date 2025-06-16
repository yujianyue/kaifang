<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai.php
// 文件大小: 1815 字节
/**
 * 本文件功能：中医处方管理系统主入口
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 开启session
session_start();

// 获取动作参数
$do = isset($_GET['do']) ? $_GET['do'] : 'login';

// 不包含HTML头部的动作列表
$no_html_actions = ['ajax', 'print'];

// 检查是否为AJAX请求
$is_ajax_request = isset($_GET['act']) && !empty($_GET['act']);

// 如果不是AJAX请求且不在无HTML头部列表中，输出页面头部
if (!$is_ajax_request && !in_array($do, $no_html_actions)) {
    require_once './inc/head.php';
}

// 根据动作参数加载对应的模块
switch ($do) {
    case 'login': // 登录页
        require_once './kai/login.php';
        break;
        
    case 'lgout': // 退出登录
        require_once './kai/lgout.php';
        break;
        
    case 'iikai': // 开方模块
        require_once './kai/iikai.php';
        break;
        
    case 'ilist': // 历史药方
        require_once './kai/ilist.php';
        break;
        
    case 'ifang': // 药名库
        require_once './kai/ifang.php';
        break;
        
    case 'idaru': // 导入功能
        require_once './kai/idaru.php';
        break;
        
    case 'ipass': // 修改密码
        require_once './kai/ipass.php';
        break;
        
    case 'ihelp': // 使用帮助
        require_once './kai/ihelp.php';
        break;
        
    case 'print': // 打印页面
        require_once './kai/print.php';
        break;
        
    default: // 默认为登录页
        require_once './kai/login.php';
        break;
}

// 如果不是AJAX请求且不在无HTML底部列表中，输出页面底部
if (!$is_ajax_request && !in_array($do, $no_html_actions)) {
    require_once './inc/foot.php';
}
?>

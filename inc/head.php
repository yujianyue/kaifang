<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: inc/head.php
// 文件大小: 1649 字节
/**
 * 本文件功能：公共头部
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 引入必要的配置文件
require_once './inc/conn.php';
require_once './inc/pubs.php';

// 检查是否需要登录
$no_login_pages = ['login', 'lgout'];
$current_do = isset($_GET['do']) ? $_GET['do'] : 'login';

if (!in_array($current_do, $no_login_pages) && !isset($_SESSION['admin_id'])) {
    header('Location: kai.php?do=login');
    exit;
}

// 获取网站设置
$site_settings = get_site_settings();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_settings['site_name']; ?></title>
    <link rel="stylesheet" href="./inc/pubs.css?v=<?php echo $version; ?>">
    <script src="./inc/pubs.js?v=<?php echo $version; ?>"></script>
</head>
<body>
    <div class="header">
        <div class="header-title">
            <h1><?php echo $site_settings['site_name']; ?></h1>
            <?php if (isset($_SESSION['admin_id'])): ?>
            <a href="kai.php?do=lgout" class="logout-btn">退出登录</a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['admin_id'])): ?>
        <div class="nav">
            <?php foreach ($menu as $key => $value): ?>
            <a href="kai.php?do=<?php echo $key; ?>" class="nav-item <?php echo $current_do == $key ? 'active' : ''; ?>">
                <?php echo $value; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="container">

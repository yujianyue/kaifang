<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: inc/conn.php
// 文件大小: 1421 字节
/**
 * 本文件功能：数据库连接及公共参数配置
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 数据库配置
$db_host = 'localhost'; // 数据库主机名
$db_user = 'ifang_chalide';      // 数据库用户名
$db_pass = 'G5YWaTX388xZjmN4';          // 数据库密码
$db_name = 'ifang_chalide';   // 数据库名

// 版本号（用于更新浏览器缓存）
$version = 'V1.0.0';

// 默认账号信息（初始安装使用）
$default_admin = [
    'username' => 'admin',
    'password' => md5('admin123' . 'kaifang') // 使用加盐方式存储密码
];

// 菜单配置
$menu = [
    'iikai' => '处方开具',
    'ilist' => '历史药方',
    'ifang' => '药名库管理',
    'idaru' => '药品导入',
    'ipass' => '修改密码',
    'ihelp' => '使用帮助'
];

// 上传配置
$upload_max_size = 2 * 1024 * 1024; // 上传文件最大限制（2MB）

// 网站设置默认值（用于创建site.json.php）
$default_site = [
    'site_name' => '中医处方管理系统',
    'site_logo' => '',
    'site_doctor' => '张医师',
    'site_address' => '某某中医诊所01号',
    'site_phone' => '12345678901',
];

// 创建数据库连接
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("数据库连接失败: " . mysqli_connect_error());
}

// 设置编码
mysqli_set_charset($conn, "utf8");
?>

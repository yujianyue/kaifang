<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/lgout.php
// 文件大小: 264 字节
/**
 * 本文件功能：退出登录
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 清除所有session
session_start();
session_destroy();

// 跳转到登录页面
header('Location: kai.php?do=login');
exit;
?>

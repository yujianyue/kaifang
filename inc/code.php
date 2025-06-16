<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: inc/code.php
// 文件大小: 1917 字节
/**
 * 本文件功能：生成验证码
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 开启session
session_start();

// 清除缓存
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 设定验证码图片的宽度和高度
$width = 80;
$height = 30;

// 创建图像
$image = imagecreatetruecolor($width, $height);

// 设置背景颜色
$bg_color = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bg_color);

// 添加干扰点
for ($i = 0; $i < 100; $i++) {
    $noise_color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), $noise_color);
}

// 添加干扰线
for ($i = 0; $i < 5; $i++) {
    $noise_color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $noise_color);
}

// 生成随机验证码
$code = '';
$characters = '0123456789';
for ($i = 0; $i < 4; $i++) {
    $code .= $characters[mt_rand(0, strlen($characters) - 1)];
}

// 将验证码保存到session
$_SESSION['captcha_code'] = $code;

// 在图像上写入验证码
$text_color = imagecolorallocate($image, 0, 0, 0);
for ($i = 0; $i < 4; $i++) {
    $text_x = $width / 5 * ($i + 0.5);
    $text_y = $height / 1.5;
    $angle = mt_rand(-30, 30);
    $font_size = mt_rand(12, 18);
    
    // 使用内置字体
    imagestring($image, $font_size / 3, $text_x, $text_y - $font_size / 2, $code[$i], $text_color);
}

// 输出图像
header('Content-Type: image/png');
imagepng($image);

// 释放资源
imagedestroy($image);
?>

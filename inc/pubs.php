<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: inc/pubs.php
// 文件大小: 4778 字节
/**
 * 本文件功能：公共PHP函数库
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

/**
 * 输出JSON格式的提示信息
 * @param int $code 状态码：0成功，1失败
 * @param string $msg 提示信息
 * @param array $data 返回数据
 * @return string
 */
function json_result($code, $msg = '', $data = []) {
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 安全过滤用户输入
 * @param string $str 需要过滤的字符串
 * @return string
 */
function safe_filter($str) {
    if (!$str) return '';
    $str = trim($str);
    $str = htmlspecialchars($str, ENT_QUOTES);
    $str = str_replace("'", "&#39;", $str);
    return $str;
}

/**
 * GET参数过滤
 * @param string $param 参数名
 * @param mixed $default 默认值
 * @return mixed
 */
function get_param($param, $default = '') {
    return isset($_GET[$param]) ? safe_filter($_GET[$param]) : $default;
}

/**
 * POST参数过滤
 * @param string $param 参数名
 * @param mixed $default 默认值
 * @return mixed
 */
function post_param($param, $default = '') {
    return isset($_POST[$param]) ? safe_filter($_POST[$param]) : $default;
}

/**
 * 密码加密函数（加盐）
 * @param string $password 原始密码
 * @return string 加密后的密码
 */
function encrypt_password($password) {
    return md5($password . 'kaifang'); // 使用固定盐值
}

/**
 * CSV数据导入函数
 * @param string $filename CSV文件路径
 * @param string $table 表名
 * @param array $columns 列名数组
 * @param object $conn 数据库连接对象
 * @return array [成功导入数, 错误记录]
 */
function import_csv_to_db($filename, $table, $columns, $conn) {
    if (!file_exists($filename)) {
        return [0, '文件不存在'];
    }
    
    $success_count = 0;
    $errors = [];
    
    $handle = fopen($filename, 'r');
    if ($handle) {
        // 跳过标题行
        fgetcsv($handle, 1000, "\t");
        
        // 处理数据行
        while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
            // 确保数据列数与列名数组匹配
            $data_count = count($data);
            $column_count = count($columns);
            
            // 如果数据列少于列名，用空值补齐
            if ($data_count < $column_count) {
                $data = array_pad($data, $column_count, '');
            } 
            // 如果数据列多于列名，截取对应数量
            else if ($data_count > $column_count) {
                $data = array_slice($data, 0, $column_count);
            }
            
            // 构建SQL插入语句
            $values = array_map(function($value) use ($conn) {
                return "'" . mysqli_real_escape_string($conn, $value) . "'";
            }, $data);
            
            $sql = "INSERT INTO `{$table}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $values) . ")";
            
            if (mysqli_query($conn, $sql)) {
                $success_count++;
            } else {
                $errors[] = "行 " . ($success_count + 1) . ": " . mysqli_error($conn);
            }
        }
        fclose($handle);
        
        // 导入完成后删除临时文件
        unlink($filename);
    }
    
    return [$success_count, $errors];
}

/**
 * 格式化年龄（根据生日计算）
 * @param string $birthday 8位数字的生日(格式：YYYYMMDD)
 * @return string 年龄
 */
function format_age($birthday) {
    if (!$birthday || strlen($birthday) != 8) {
        return '';
    }
    
    $year = substr($birthday, 0, 4);
    $month = substr($birthday, 4, 2);
    $day = substr($birthday, 6, 2);
    
    $birthday_time = mktime(0, 0, 0, $month, $day, $year);
    $diff = time() - $birthday_time;
    $age = floor($diff / (365 * 24 * 60 * 60));
    
    return $age . '岁';
}

/**
 * 生成唯一订单号
 * @return string 订单号
 */
function generate_order_number() {
    return date('YmdHis') . rand(100, 999);
}

/**
 * 获取网站设置
 * @return array 设置项数组
 */
function get_site_settings() {
    global $default_site;
    
    $file_path = __DIR__ . '/site.json.php';
    
    // 文件不存在，创建默认设置
    if (!file_exists($file_path)) {
        $content = json_encode($default_site, JSON_UNESCAPED_UNICODE);
        file_put_contents($file_path, $content);
        return $default_site;
    }
    
    // 文件存在，读取配置
    $content = file_get_contents($file_path);    
    $settings = json_decode($content, true);
    return $settings ?: $default_site;
}


?>
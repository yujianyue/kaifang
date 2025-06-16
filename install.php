<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: install.php
// 文件大小: 19616 字节
/**
 * 本文件功能：数据库安装
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

    // 引入数据库配置
    require_once './inc/conn.php';

// 处理AJAX请求
if (isset($_GET['act'])) {
    
    $act = $_GET['act'];
    
    switch ($act) {
        case 'check': // 检查环境
            $result = [
                'php_version' => PHP_VERSION,
                'mysql_version' => mysqli_get_server_info($conn),
                'mysql_connect' => $conn ? true : false,
                'writable_dirs' => [
                    'cache' => is_writable('./cache'),
                    'inc' => is_writable('./inc')
                ]
            ];
            
            echo json_encode($result);
            break;
            
        case 'install': // 安装数据库
            $import_demo = isset($_POST['import_demo']) ? $_POST['import_demo'] : 0;
            
            // 开始安装
            $result = install_database($conn, $import_demo);
            
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['status' => 0, 'msg' => '未知操作']);
            break;
    }
    
    exit;
}

/**
 * 安装数据库
 * @param object $conn 数据库连接
 * @param int $import_demo 是否导入演示数据
 * @return array 安装结果
 */
function install_database($conn, $import_demo) {
    // 创建药品表
    $sql_medicine = "CREATE TABLE IF NOT EXISTS `medicine` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `medicine_name` varchar(100) NOT NULL COMMENT '药品名称',
        `alias` varchar(100) DEFAULT NULL COMMENT '别名',
        `pinyin` varchar(200) DEFAULT NULL COMMENT '全拼',
        `short_pinyin` varchar(50) DEFAULT NULL COMMENT '简拼',
        `unit` varchar(20) DEFAULT '克' COMMENT '单位',
        `default_amount` decimal(10,2) DEFAULT '10.00' COMMENT '默认数量',
        `price` decimal(10,2) DEFAULT '0.00' COMMENT '参考价格',
        `count_times` int(11) DEFAULT '0' COMMENT '开单记次',
        `advice` varchar(255) DEFAULT NULL COMMENT '该药医嘱',
        PRIMARY KEY (`id`),
        UNIQUE KEY `medicine_name` (`medicine_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='药品库';";
    
    // 创建处方表
    $sql_prescription = "CREATE TABLE IF NOT EXISTS `prescription` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_number` varchar(50) NOT NULL COMMENT '订单号',
        `patient_name` varchar(50) NOT NULL COMMENT '姓名',
        `patient_birthday` varchar(8) NOT NULL COMMENT '生日',
        `patient_mobile` varchar(20) NOT NULL COMMENT '手机号',
        `diagnosis` text COMMENT '诊断信息',
        `advice` text COMMENT '医嘱信息',
        `medicine_detail` text NOT NULL COMMENT '药方详细',
        `total_price` decimal(10,2) DEFAULT '0.00' COMMENT '参考总价',
        `is_common` tinyint(1) DEFAULT '0' COMMENT '常方标记',
        `common_name` varchar(100) DEFAULT NULL COMMENT '常方简称',
        `create_time` datetime DEFAULT NULL COMMENT '添加时间',
        `status` tinyint(1) DEFAULT '1' COMMENT '状态(1有效,0作废)',
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_number` (`order_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='处方表';";
    
    // 执行SQL
    $errors = [];
    
    if (!mysqli_query($conn, $sql_medicine)) {
        $errors[] = "创建药品表失败: " . mysqli_error($conn);
    }
    
    if (!mysqli_query($conn, $sql_prescription)) {
        $errors[] = "创建处方表失败: " . mysqli_error($conn);
    }
    
    // 导入演示数据
    if ($import_demo == 1 && empty($errors)) {
        // 演示药品数据
        $demo_medicines = [
            ["黄芪", "北芪", "huangqi", "hq", "克", 15, 0.30, 0, "益气固表，利水消肿"],
            ["当归", "", "danggui", "dg", "克", 10, 0.50, 0, "补血活血，调经止痛"],
            ["川芎", "", "chuanxiong", "cx", "克", 10, 0.40, 0, "活血行气，祛风止痛"],
            ["白芍", "", "baishao", "bs", "克", 10, 0.35, 0, "养血敛阴，柔肝止痛"],
            ["熟地黄", "熟地", "shudihuang", "sdh", "克", 15, 0.45, 0, "滋阴补血，益精填髓"],
            ["生地黄", "生地", "shengdihuang", "sdh", "克", 15, 0.40, 0, "清热凉血，养阴生津"],
            ["白术", "", "baizhu", "bz", "克", 10, 0.35, 0, "健脾益气，燥湿利水"],
            ["茯苓", "", "fuling", "fl", "克", 10, 0.30, 0, "利水渗湿，健脾安神"],
            ["甘草", "", "gancao", "gc", "克", 5, 0.25, 0, "补脾益气，清热解毒，调和诸药"],
            ["陈皮", "", "chenpi", "cp", "克", 10, 0.30, 0, "理气健脾，燥湿化痰"],
            ["枸杞子", "枸杞", "gouqizi", "gqz", "克", 10, 0.50, 0, "滋补肝肾，益精明目"],
            ["山药", "", "shanyao", "sy", "克", 15, 0.35, 0, "补脾养胃，生津益肺"],
            ["板蓝根", "", "banlangen", "blg", "克", 15, 0.30, 0, "清热解毒，凉血利咽"],
            ["连翘", "", "lianqiao", "lq", "克", 10, 0.40, 0, "清热解毒，消肿散结"],
            ["金银花", "", "jinyinhua", "jyh", "克", 10, 0.45, 0, "清热解毒，疏散风热"],
            ["桂枝", "", "guizhi", "gz", "克", 10, 0.35, 0, "发汗解表，温通经脉"],
            ["大枣", "红枣", "dazao", "dz", "枚", 5, 0.20, 0, "补中益气，养血安神"],
            ["生姜", "", "shengjiang", "sj", "克", 5, 0.20, 0, "发汗解表，温中止呕"],
            ["党参", "", "dangshen", "ds", "克", 15, 0.40, 0, "补中益气，健脾益肺"],
            ["黄芩", "", "huangqin", "hq", "克", 10, 0.35, 0, "清热燥湿，泻火解毒"],
            ["柴胡", "", "chaihu", "ch", "克", 10, 0.40, 0, "疏肝解郁，升阳退热"],
            ["白芷", "", "baizhi", "bz", "克", 10, 0.30, 0, "发散风寒，通窍止痛"],
            ["麦冬", "", "maidong", "md", "克", 10, 0.45, 0, "养阴生津，润肺清心"],
            ["天麻", "", "tianma", "tm", "克", 10, 0.60, 0, "平肝息风，祛风通络"],
            ["制附片", "附片", "zhifupian", "zfp", "克", 10, 0.50, 0, "回阳救逆，补火助阳，散寒止痛"],
            ["杜仲", "", "duzhong", "dz", "克", 10, 0.40, 0, "补肝肾，强筋骨，安胎"],
            ["泽泻", "", "zexie", "zx", "克", 10, 0.30, 0, "利水渗湿，泄热"],
            ["丹参", "", "danshen", "ds", "克", 15, 0.40, 0, "活血化瘀，凉血消痈"],
            ["砂仁", "", "sharen", "sr", "克", 6, 0.60, 0, "化湿开胃，温脾止泻"],
            ["薏苡仁", "薏米", "yiyiren", "yyr", "克", 15, 0.25, 0, "健脾渗湿，清热解毒"],
            ["车前子", "", "cheqianzi", "cqz", "克", 10, 0.30, 0, "清热利尿，明目"],
            ["桑叶", "", "sangye", "sy", "克", 10, 0.30, 0, "疏散风热，清肺润燥"],
            ["菊花", "", "juhua", "jh", "克", 10, 0.35, 0, "疏风清热，平肝明目"],
            ["决明子", "", "juemingzi", "jmz", "克", 10, 0.25, 0, "清肝明目，润肠通便"],
            ["牛膝", "", "niuxi", "nx", "克", 10, 0.40, 0, "活血通经，引血下行"],
            ["蒲公英", "", "pugongying", "pgy", "克", 15, 0.25, 0, "清热解毒，消肿散结"]
        ];
        
        // 插入演示药品数据
        foreach ($demo_medicines as $medicine) {
            $sql = "INSERT INTO `medicine` (`medicine_name`, `alias`, `pinyin`, `short_pinyin`, `unit`, `default_amount`, `price`, `count_times`, `advice`) VALUES (
                '{$medicine[0]}', '{$medicine[1]}', '{$medicine[2]}', '{$medicine[3]}', '{$medicine[4]}', {$medicine[5]}, {$medicine[6]}, {$medicine[7]}, '{$medicine[8]}'
            ) ON DUPLICATE KEY UPDATE id=id";
            
            mysqli_query($conn, $sql);
        }
    }  
   
    if (empty($errors)) {
        return ['status' => 1, 'msg' => '数据库安装成功' . ($import_demo == 1 ? '，并已导入演示数据' : '')];
    } else {
        return ['status' => 0, 'msg' => '安装过程中出现错误', 'errors' => $errors];
    }
}
// 创建管理员密码文件
    $mima_file = './inc/mima.php';
    if (!file_exists($mima_file)) {
        $admin_data = [
            'username' => $default_admin['username'],
            'password' => $default_admin['password']
        ];
        
        $content = json_encode($admin_data, JSON_UNESCAPED_UNICODE);
        file_put_contents($mima_file, $content);
    }
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>中医处方管理系统安装</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: "Microsoft YaHei", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background-color: #f5f5f5;
            max-width: 100%;
            overflow-x: hidden;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1976d2;
            border-left: 4px solid #1976d2;
            padding-left: 10px;
        }
        
        .check-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .check-item .status {
            font-weight: bold;
        }
        
        .status-ok {
            color: #4caf50;
        }
        
        .status-error {
            color: #f44336;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 4px;
            transition: all 0.2s;
            background-color: #1976d2;
            color: #fff;
            margin-right: 10px;
        }
        
        .btn:hover {
            background-color: #1565c0;
        }
        
        .btn-danger {
            background-color: #f44336;
        }
        
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        
        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group input {
            margin-right: 10px;
        }
        
        .result-message {
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }
        
        .result-success {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .result-error {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>中医处方管理系统安装</h1>
            <p>本向导将帮助您安装中医处方管理系统</p>
        </div>
        
        <div class="section">
            <div class="section-title">环境检查</div>
            <div id="check-result">
                <div class="check-item">
                    <span class="item-name">检查中...</span>
                    <span class="status">请稍候</span>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">安装设置</div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="import-demo" checked>
                <label for="import-demo">导入演示数据（包含36种常用中药）</label>
            </div>
            
            <div class="form-group">
                <button id="install-btn" class="btn" disabled>开始安装</button>
            </div>
        </div>
        
        <div id="result-message" style="display: none;" class="result-message"></div>
        
        <div class="footer">
            <p>版权所有 &copy; 2025 中医处方管理系统</p>
            <p>技术支持：15058593138@qq.com</p>
        </div>
    </div>
    
    <script>
        // AJAX函数
        function ajax(url, method, data, callback) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            callback(response);
                        } catch (e) {
                            callback({status: 0, msg: '返回数据格式错误'});
                        }
                    } else {
                        callback({status: 0, msg: '请求失败，状态码：' + xhr.status});
                    }
                }
            };
            
            xhr.open(method, url, true);
            
            if (method.toLowerCase() === 'post') {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(data);
            } else {
                xhr.send();
            }
        }
        
        // 检查环境
        function checkEnvironment() {
            document.getElementById('check-result').innerHTML = '<div class="check-item"><span class="item-name">检查中...</span><span class="status">请稍候</span></div>';
            
            ajax('?act=check', 'get', null, function(res) {
                var html = '';
                var hasError = false;
                
                // PHP版本检查
                html += '<div class="check-item">';
                html += '<span class="item-name">PHP版本：' + res.php_version + '</span>';
                
                if (parseFloat(res.php_version) >= 7.0) {
                    html += '<span class="status status-ok">正常</span>';
                } else {
                    html += '<span class="status status-error">需要PHP 7.0+</span>';
                    hasError = true;
                }
                
                html += '</div>';
                
                // MySQL检查
                html += '<div class="check-item">';
                html += '<span class="item-name">MySQL连接</span>';
                
                if (res.mysql_connect) {
                    html += '<span class="status status-ok">正常 (' + res.mysql_version + ')</span>';
                } else {
                    html += '<span class="status status-error">连接失败</span>';
                    hasError = true;
                }
                
                html += '</div>';
                
                // 目录权限检查
                html += '<div class="check-item">';
                html += '<span class="item-name">cache目录权限</span>';
                
                if (res.writable_dirs.cache) {
                    html += '<span class="status status-ok">可写</span>';
                } else {
                    html += '<span class="status status-error">不可写</span>';
                    hasError = true;
                }
                
                html += '</div>';
                
                html += '<div class="check-item">';
                html += '<span class="item-name">inc目录权限</span>';
                
                if (res.writable_dirs.inc) {
                    html += '<span class="status status-ok">可写</span>';
                } else {
                    html += '<span class="status status-error">不可写</span>';
                    hasError = true;
                }
                
                html += '</div>';
                
                document.getElementById('check-result').innerHTML = html;
                document.getElementById('install-btn').disabled = hasError;
            });
        }
        
        // 安装系统
        function installSystem() {
            var importDemo = document.getElementById('import-demo').checked ? 1 : 0;
            var installBtn = document.getElementById('install-btn');
            var resultMessage = document.getElementById('result-message');
            
            installBtn.disabled = true;
            installBtn.textContent = '安装中...';
            resultMessage.style.display = 'none';
            
            ajax('?act=install', 'post', 'import_demo=' + importDemo, function(res) {
                installBtn.textContent = '开始安装';
                installBtn.disabled = false;
                
                resultMessage.style.display = 'block';
                
                if (res.status === 1) {
                    resultMessage.className = 'result-message result-success';
                    resultMessage.innerHTML = '<strong>安装成功！</strong><p>' + res.msg + '</p>' +
                        '<p>默认账号：admin</p>' +
                        '<p>默认密码：admin123</p>' +
                        '<p><a href="kai.php" class="btn">立即访问系统</a></p>';
                } else {
                    resultMessage.className = 'result-message result-error';
                    var errorMsg = '<strong>安装失败！</strong><p>' + res.msg + '</p>';
                    
                    if (res.errors && res.errors.length > 0) {
                        errorMsg += '<ul>';
                        for (var i = 0; i < res.errors.length; i++) {
                            errorMsg += '<li>' + res.errors[i] + '</li>';
                        }
                        errorMsg += '</ul>';
                    }
                    
                    resultMessage.innerHTML = errorMsg;
                }
            });
        }
        
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', function() {
            // 检查环境
            checkEnvironment();
            
            // 安装按钮点击事件
            document.getElementById('install-btn').addEventListener('click', installSystem);
        });
    </script>
</body>
</html>

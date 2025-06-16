<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/print.php
// 文件大小: 8540 字节
/**
 * 本文件功能：处方打印页面
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 引入数据库和工具函数
require_once './inc/conn.php';
require_once './inc/pubs.php';
require_once './inc/sqls.php';

// 初始化数据库操作对象
$db = new DB($conn);

// 获取网站设置
$site_settings = get_site_settings();

// 获取订单号
$order_number = get_param('order');
if (empty($order_number)) {
    echo '<div class="error-message">参数错误，缺少订单号</div>';
    exit;
}

// 查询处方信息
$sql = "SELECT * FROM prescription WHERE order_number = '{$order_number}'";
$prescription = $db->getRow($sql);

if (!$prescription) {
    echo '<div class="error-message">处方不存在</div>';
    exit;
}

// 解析药方详情
$medicines = json_decode($prescription['medicine_detail'], true);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>处方打印页面 - <?php echo $prescription['order_number']; ?></title>
    <style>
        body {
            font-family: "SimSun", "宋体", serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        
        .print-container {
            width: 148mm; /* A5 纸宽度 */
            min-height: 210mm; /* A5 纸高度 */
            margin: 0 auto;
            padding: 15mm 10mm;
            box-sizing: border-box;
            position: relative;
        }
        
        .clinic-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .clinic-info {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .prescription-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .patient-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .patient-info-item {
            width: 50%;
            margin-bottom: 5px;
        }
        
        .diagnosis-info {
            margin-bottom: 15px;
        }
        
        .medicine-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .medicine-list th, .medicine-list td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        
        .medicine-list th {
            background-color: #f5f5f5;
        }
        
        .advice-info {
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .total-price {
            text-align: right;
            margin-bottom: 15px;
        }
        
        .doctor-info {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .qr-code {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .footer-info {
            text-align: center;
            font-size: 12px;
            position: absolute;
            bottom: 10mm;
            left: 10mm;
            right: 10mm;
        }
        
        .no-print {
            text-align: center;
            margin: 20px 0;
            display: block;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #1976d2;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin: 0 5px;
        }
        
        .btn-return {
            background-color: #4caf50;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            @page {
                size: A5;
                margin: 0;
            }
            
            body {
                margin: 0;
            }
            
            .print-container {
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="clinic-header">
            <div class="clinic-name"><?php echo $site_settings['site_name']; ?></div>
            <div class="clinic-info">地址：<?php echo $site_settings['site_address']; ?> | 电话：<?php echo $site_settings['site_phone']; ?></div>
        </div>
        
        <div class="prescription-title">中医处方</div>
        
        <div class="patient-info">
            <div class="patient-info-item">
                <strong>姓名：</strong><?php echo $prescription['patient_name']; ?>
            </div>
            <div class="patient-info-item">
                <strong>年龄：</strong><?php echo format_age($prescription['patient_birthday']); ?>
            </div>
            <div class="patient-info-item">
                <strong>电话：</strong><?php echo $prescription['patient_mobile']; ?>
            </div>
            <div class="patient-info-item">
                <strong>日期：</strong><?php echo date('Y年m月d日', strtotime($prescription['create_time'])); ?>
            </div>
        </div>
        
        <?php if (!empty($prescription['diagnosis'])): ?>
        <div class="diagnosis-info">
            <strong>诊断：</strong><?php echo $prescription['diagnosis']; ?>
        </div>
        <?php endif; ?>
        
        <table class="medicine-list">
            <thead>
                <tr>
                    <th>药品名称</th>
                    <th>数量</th>
                    <th>单位</th>
                    <th>单价</th>
                    <th>金额</th>
                </tr>
            </thead>
            <tbody>
                <?php
              $fang = htmlspecialchars_decode($prescription["medicine_detail"], ENT_QUOTES); 
              $list = json_decode($fang, True);
              //echo json_encode($list);
              foreach ($list as $medicine): ?>
                <tr>
                    <td><?php echo $medicine['medicine_name']; ?></td>
                    <td><?php echo $medicine['amount']; ?></td>
                    <td><?php echo $medicine['unit']; ?></td>
                    <td><?php echo number_format($medicine['price'], 2); ?></td>
                    <td><?php echo number_format($medicine['amount'] * $medicine['price'], 2); ?></td>
                </tr>
                <?php if (!empty($medicine['advice'])): ?>
                <tr>
                    <td colspan="5" style="text-align: left; font-size: 12px; color: #666;">
                        <strong>用法：</strong><?php echo $medicine['advice']; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach;  ?>
            </tbody>
        </table>
        
        <?php if (!empty($prescription['advice'])): ?>
        <div class="advice-info">
            <strong>医嘱：</strong><?php echo $prescription['advice']; ?>
        </div>
        <?php endif; ?>
        
        <div class="total-price">
            <strong>参考总价：</strong><?php echo number_format($prescription['total_price'], 2); ?> 元
        </div>
        
        <div class="doctor-info">
            <div>
                <strong>医师：</strong><?php echo $site_settings['site_doctor']; ?>
            </div>
            <div>
                <strong>处方号：</strong><?php echo $prescription['order_number']; ?>
            </div>
        </div>
        
        <div class="footer-info">
            本处方仅限<?php echo date('Y年m月d日', strtotime('+7 days', strtotime($prescription['create_time']))); ?>前有效
        </div>
    </div>
    
    <div class="no-print">
        <button class="btn" onclick="window.print()">打印处方</button>
        <a href="kai.php?do=iikai" class="btn btn-return">返回开方</a>
    </div>
</body>
</html>

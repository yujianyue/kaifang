<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/idaru.php
// 文件大小: 13391 字节
/**
 * 本文件功能：药品数据导入
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 引入数据库和工具函数
require_once './inc/conn.php';
require_once './inc/pubs.php';
require_once './inc/sqls.php';

// 初始化数据库操作对象
$db = new DB($conn);

// 处理AJAX请求
if (isset($_GET['act'])) {
    $act = $_GET['act'];
    
    switch ($act) {
        case 'import': // 导入数据
            $data = post_param('data');
            
            if (empty($data)) {
                json_result(1, '请输入数据');
            }
            
            // 生成临时文件名
            $temp_file = './cache/' . date('YmdHis') . rand(100, 999) . '.tsv';
            
            // 写入临时文件
            if (file_put_contents($temp_file, $data)) {
                // 定义列名
                $columns = ['medicine_name', 'alias', 'pinyin', 'short_pinyin', 'unit', 'default_amount', 'price', 'count_times', 'advice'];
                
                // 导入数据
                $result = import_medicine_data($temp_file, $db);
                
                if ($result[0] > 0) {
                    $msg = "导入成功！共导入 {$result[0]} 条数据";
                    if (!empty($result[1])) {
                        $msg .= "，但有 " . count($result[1]) . " 条数据导入失败";
                    }
                    json_result(0, $msg, $result[1]);
                } else {
                    json_result(1, '导入失败，请检查数据格式', $result[1]);
                }
            } else {
                json_result(1, '临时文件创建失败');
            }
            break;
            
        default:
            json_result(1, '未知操作');
            break;
    }
    
    exit;
}

/**
 * 导入药品数据
 * @param string $filename 文件路径
 * @param object $db 数据库对象
 * @return array [成功导入数, 错误信息数组]
 */
function import_medicine_data($filename, $db) {
    if (!file_exists($filename)) {
        return [0, ['文件不存在']];
    }
    
    $success_count = 0;
    $errors = [];
    
    $handle = fopen($filename, 'r');
    if ($handle) {
        $line_num = 0;
        
        // 处理每一行数据
        while (($line = fgets($handle)) !== false) {
            $line_num++;
            
            // 忽略空行
            if (trim($line) === '') {
                continue;
            }
            
            // 分割数据（按制表符）
            $data = explode("\t", $line);
            
            // 至少需要药名
            if (empty($data[0])) {
                $errors[] = "第 {$line_num} 行: 药品名称不能为空";
                continue;
            }
            
            // 准备数据
            $medicine_data = [
                'medicine_name' => trim($data[0]),
                'alias' => isset($data[1]) ? trim($data[1]) : '',
                'pinyin' => '', // 需要生成拼音
                'short_pinyin' => '', // 需要生成简拼
                'unit' => isset($data[2]) && !empty($data[2]) ? trim($data[2]) : '克',
                'default_amount' => isset($data[3]) && is_numeric($data[3]) ? trim($data[3]) : 10,
                'price' => isset($data[4]) && is_numeric($data[4]) ? trim($data[4]) : 0,
                'count_times' => isset($data[5]) && is_numeric($data[5]) ? trim($data[5]) : 0,
                'advice' => isset($data[6]) ? trim($data[6]) : ''
            ];
            
            // 生成全拼和简拼 (简化版本)
            $medicine_data['pinyin'] = getPinyin($medicine_data['medicine_name']);
            $medicine_data['short_pinyin'] = getShortPinyin($medicine_data['medicine_name']);
            
            // 插入数据
            $result = $db->insert('medicine', $medicine_data);
            
            if ($result) {
                $success_count++;
            } else {
                $errors[] = "第 {$line_num} 行: 插入失败，可能是药品名称已存在";
            }
        }
        
        fclose($handle);
        
        // 导入完成后删除临时文件
        unlink($filename);
    }
    
    return [$success_count, $errors];
}

/**
 * 简易中文转拼音函数（仅示例，实际项目中可能需要使用完整的拼音库）
 * @param string $str 中文字符串
 * @return string 拼音
 */
function getPinyin($str) {
    // 简化版本，仅支持部分常见汉字
    $pinyin_dict = [
        '安' => 'an', '八' => 'ba', '板' => 'ban', '般' => 'ban', '茶' => 'cha',
        '陈' => 'chen', '川' => 'chuan', '大' => 'da', '丹' => 'dan', '党' => 'dang',
        '地' => 'di', '东' => 'dong', '方' => 'fang', '风' => 'feng', '甘' => 'gan',
        '高' => 'gao', '根' => 'gen', '狗' => 'gou', '古' => 'gu', '黄' => 'huang',
        '姜' => 'jiang', '金' => 'jin', '九' => 'jiu', '连' => 'lian', '龙' => 'long',
        '陆' => 'lu', '马' => 'ma', '木' => 'mu', '牛' => 'niu', '片' => 'pian',
        '气' => 'qi', '三' => 'san', '山' => 'shan', '生' => 'sheng', '石' => 'shi',
        '天' => 'tian', '王' => 'wang', '五' => 'wu', '西' => 'xi', '香' => 'xiang',
        '血' => 'xue', '羊' => 'yang', '药' => 'yao', '银' => 'yin', '玉' => 'yu',
        '泽' => 'ze', '枣' => 'zao', '竹' => 'zhu', '子' => 'zi', '草' => 'cao'
    ];
    
    $result = '';
    $len = mb_strlen($str, 'UTF-8');
    
    for ($i = 0; $i < $len; $i++) {
        $char = mb_substr($str, $i, 1, 'UTF-8');
        if (isset($pinyin_dict[$char])) {
            $result .= $pinyin_dict[$char];
        } else {
            $result .= $char;
        }
    }
    
    return $result;
}

/**
 * 简易中文转简拼函数
 * @param string $str 中文字符串
 * @return string 简拼
 */
function getShortPinyin($str) {
    // 简化版本，仅支持部分常见汉字
    $pinyin_dict = [
        '安' => 'a', '八' => 'b', '板' => 'b', '般' => 'b', '茶' => 'c',
        '陈' => 'c', '川' => 'c', '大' => 'd', '丹' => 'd', '党' => 'd',
        '地' => 'd', '东' => 'd', '方' => 'f', '风' => 'f', '甘' => 'g',
        '高' => 'g', '根' => 'g', '狗' => 'g', '古' => 'g', '黄' => 'h',
        '姜' => 'j', '金' => 'j', '九' => 'j', '连' => 'l', '龙' => 'l',
        '陆' => 'l', '马' => 'm', '木' => 'm', '牛' => 'n', '片' => 'p',
        '气' => 'q', '三' => 's', '山' => 's', '生' => 's', '石' => 's',
        '天' => 't', '王' => 'w', '五' => 'w', '西' => 'x', '香' => 'x',
        '血' => 'x', '羊' => 'y', '药' => 'y', '银' => 'y', '玉' => 'y',
        '泽' => 'z', '枣' => 'z', '竹' => 'z', '子' => 'z', '草' => 'c'
    ];
    
    $result = '';
    $len = mb_strlen($str, 'UTF-8');
    
    for ($i = 0; $i < $len; $i++) {
        $char = mb_substr($str, $i, 1, 'UTF-8');
        if (isset($pinyin_dict[$char])) {
            $result .= $pinyin_dict[$char];
        } else {
            $result .= $char;
        }
    }
    
    return $result;
}
?>

<div class="panel">
    <div class="panel-header">
        <h3>药品数据导入</h3>
    </div>
    <div class="panel-body">
        <div class="import-info">
            <h4>导入说明</h4>
            <p>请按照以下格式准备数据，各字段之间用制表符（Tab键）分隔：</p>
            <div class="import-format">
                <pre>药品名称*	别名(可选)	单位(可选,默认克)	默认数量(可选,默认10)	参考价格(可选)	开单记次(可选)	医嘱(可选)</pre>
            </div>
            <p>注意：药品名称为必填项，其他字段为选填项，如无可留空。技巧，可以复制示范数据到excel查看,编辑后粘贴提交。</p>
        </div>
        
        <div class="import-form">
            <div class="form-group">
                <label for="import-data">数据内容</label>
                <textarea id="import-data" class="form-control" rows="10" placeholder="请按格式粘贴数据内容，一行一条记录...">
药品名称*	别名(可选)	单位(默认克)	默认数量(默认10)	参考价格	开单记次	医嘱
人参	红参、山参	克	10	15.8	36	气虚欲脱者宜用
黄芪	北芪、绵芪	克	10	8.5	42	表虚自汗者慎用
当归	秦归、云归	克	10	12.3	38	血虚便秘者适用
甘草	国老、蜜草	克	10	6.2	45	不宜与海藻同用
茯苓	云苓、白茯苓	克	10	7.8	39	阴虚火旺者忌
白术	于术、冬术	克	10	9.1	33	脾虚食少者宜
白芍	杭芍、金芍药	克	10	10.4	29	反藜芦
川芎	芎䓖、香果	克	10	11.7	31	月经过多慎用
熟地黄	熟地	克	10	13.6	27	脾虚湿滞不宜
黄连	川连、鸡爪连	克	10	18.9	24	脾胃虚寒忌
黄芩	枯芩、条芩	克	10	14.2	26	肺热咳嗽宜
金银花	双花、忍冬花	克	10	22.5	41	疮痈肿毒适用
枸杞子	苟起子、甜菜子	克	10	25.8	48	肝肾阴虚宜
丹参	赤参、紫丹参	克	10	16.3	35	孕妇慎用
山药	怀山药、淮山	克	10	19.7	43	脾虚泄泻适用
陈皮	橘皮、广陈皮	克	10	9.8	37	气虚燥咳慎
半夏	地文、守田	克	10	21.4	22	反乌头
柴胡	地熏、山菜	克	10	17.6	28	肝阳上亢忌
麦冬	麦门冬、沿阶草	克	10	20.1	34	胃寒腹泻慎
五味子	玄及、会及	克	10	23.8	30	表邪未解忌
菊花	甘菊、金蕊	克	10	15.2	39	风热感冒宜
薏苡仁	薏米、苡仁	克	10	12.9	40	脾虚无湿慎
红花	草红花、红蓝花	克	10	27.3	23	孕妇禁用
桃仁	桃核仁	克	10	24.6	25	便溏者慎用
杜仲	思仙、木绵	克	10	30.5	32	肾虚腰痛宜
天麻	赤箭、定风草	克	10	35.2	21	肝阳上亢宜
何首乌	首乌、地精	克	10	28.7	20	大便溏泄慎
阿胶	驴皮胶	克	10	45.9	47	阴虚血虚宜
灵芝	赤芝、木灵芝	克	10	52.3	18	心神不宁宜
冬虫夏草	虫草	克	10	120.0	15	肺肾两虚宜
板蓝根	靛青根、蓝靛根	克	10	11.5	44	风寒感冒忌
鱼腥草	折耳根	克	10	9.3	46	疮痈肿毒宜
决明子	草决明、马蹄决明	克	10	8.7	36	目赤涩痛宜
桑叶	霜桑叶、铁扇子	克	10	7.4	33	风热感冒宜
薄荷	蕃荷菜、南薄荷	克	10	6.9	38	阴虚血燥忌
桂枝	柳桂	克	10	10.8	29	温热病忌用</textarea>
            </div>
            
            <div class="form-group">
                <button type="button" class="btn btn-primary" onclick="importData()">导入数据</button>
                <button type="button" class="btn btn-default" onclick="document.getElementById('import-data').value=''">清空内容</button>
            </div>
        </div>
        
        <div id="import-result" class="import-result" style="display: none;">
            <h4>导入结果</h4>
            <div id="result-content"></div>
        </div>
    </div>
</div>

<style>
.import-info {
    background-color: #f5f5f5;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.import-format {
    background-color: #fff;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    margin: 10px 0;
}

.import-format pre {
    margin: 0;
    white-space: pre-wrap;
    word-break: break-all;
}

.import-form {
    margin-bottom: 20px;
}

.import-result {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.result-success {
    color: #4caf50;
    margin-bottom: 10px;
}

.result-error {
    color: #f44336;
}

.result-error-list {
    margin-top: 10px;
    padding-left: 20px;
}
</style>

<script>
// 导入数据
function importData() {
    const importData = document.getElementById('import-data').value.trim();
    
    if (!importData) {
        showToast('请输入要导入的数据', 'error');
        return;
    }
    
    // 显示加载提示
    showToast('正在导入数据，请稍候...', 'info');
    
    // 发送请求
    ajaxRequest('kai.php?do=idaru&act=import', 'post', {data: importData}, function(res) {
        // 显示结果
        const resultElement = document.getElementById('import-result');
        const resultContent = document.getElementById('result-content');
        
        resultElement.style.display = 'block';
        
        if (res.code === 0) {
            let html = '<div class="result-success">' + res.msg + '</div>';
            
            // 显示错误信息（如果有）
            if (res.data && res.data.length > 0) {
                html += '<div class="result-error">以下数据导入失败：</div>';
                html += '<ul class="result-error-list">';
                for (let i = 0; i < res.data.length; i++) {
                    html += '<li>' + res.data[i] + '</li>';
                }
                html += '</ul>';
            }
            
            resultContent.innerHTML = html;
            showToast('导入完成', 'success');
        } else {
            let html = '<div class="result-error">' + res.msg + '</div>';
            
            // 显示错误信息（如果有）
            if (res.data && res.data.length > 0) {
                html += '<ul class="result-error-list">';
                for (let i = 0; i < res.data.length; i++) {
                    html += '<li>' + res.data[i] + '</li>';
                }
                html += '</ul>';
            }
            
            resultContent.innerHTML = html;
            showToast('导入失败', 'error');
        }
    });
}
</script>

<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/ihelp.php
// 文件大小: 5571 字节
/**
 * 本文件功能：系统帮助说明
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */
?>

<div class="panel">
    <div class="panel-header">
        <h3>使用帮助</h3>
    </div>
    <div class="panel-body help-content">
        <div class="help-section">
            <h4>系统简介</h4>
            <p>本系统是一款专为中医诊所设计的处方管理系统，支持药品管理、处方开具、打印以及历史查询等功能。</p>
        </div>
        
        <div class="help-section">
            <h4>主要功能</h4>
            <ul class="help-list">
                <li>
                    <strong>处方开具</strong>
                    <p>可快速录入患者信息，选择药品并设置用量，自动计算价格，支持处方打印功能，并可将常用处方设为模板。</p>
                </li>
                <li>
                    <strong>历史处方</strong>
                    <p>查看历史开具的处方记录，支持按患者姓名、手机号等条件搜索，可查看明细、打印和标记状态。</p>
                </li>
                <li>
                    <strong>药品库管理</strong>
                    <p>管理药品信息，包括药名、别名、价格等信息，支持添加、修改和删除操作。</p>
                </li>
                <li>
                    <strong>药品导入</strong>
                    <p>批量导入药品数据，支持制表符分隔的数据格式，便于快速建立药品库。</p>
                </li>
            </ul>
        </div>
        
        <div class="help-section">
            <h4>开方流程</h4>
            <ol class="help-list">
                <li>进入"处方开具"页面，填写患者基本信息</li>
                <li>在药品输入框中输入药名，系统会自动提示匹配的药品</li>
                <li>选择药品后，可调整用量和价格</li>
                <li>继续添加多种药品，系统会自动计算总价</li>
                <li>填写必要的诊断和医嘱信息</li>
                <li>点击"保存处方并打印"按钮完成开方</li>
                <li>系统会打开打印预览页面，可以直接打印处方</li>
            </ol>
        </div>
        
        <div class="help-section">
            <h4>常用处方使用</h4>
            <ol class="help-list">
                <li>在开方页面，勾选"标记为常用处方"并填写简称可将当前处方保存为常用处方</li>
                <li>点击"调用常用处方"按钮可查看所有常用处方</li>
                <li>点击常用处方可将其中的药品信息一键导入到当前处方</li>
            </ol>
        </div>
        
        <div class="help-section">
            <h4>药品导入格式说明</h4>
            <p>在"药品导入"页面，您可以按照以下格式准备数据进行批量导入：</p>
            <pre class="help-code">药品名称*	别名(可选)	单位(可选,默认克)	默认数量(可选,默认10)	参考价格(可选)	开单记次(可选)	医嘱(可选)</pre>
            <p>各字段之间用制表符（Tab键）分隔，一行为一条记录。</p>
        </div>
        
        <div class="help-section">
            <h4>系统要求</h4>
            <ul class="help-list">
                <li>PHP版本：PHP 7.0+</li>
                <li>MySQL版本：MySQL 5.6+</li>
                <li>浏览器：建议使用Chrome、Firefox、Edge等现代浏览器</li>
                <li>分辨率：适配手机和电脑屏幕</li>
            </ul>
        </div>
        
        <div class="help-section">
            <h4>常见问题</h4>
            <div class="help-qa">
                <div class="help-question">Q: 如何修改系统设置（如医生姓名、诊所地址等）？</div>
                <div class="help-answer">A: 系统设置信息保存在inc/site.json.php文件中，修改该文件即可更改相关信息。</div>
            </div>
            <div class="help-qa">
                <div class="help-question">Q: 忘记密码怎么办？</div>
                <div class="help-answer">A: 如果忘记密码，请删除inc/mima.php文件，系统将重置为默认密码。</div>
            </div>
            <div class="help-qa">
                <div class="help-question">Q: 如何备份数据？</div>
                <div class="help-answer">A: 建议定期备份MySQL数据库以保障数据安全。</div>
            </div>
        </div>
        
        <div class="help-section">
            <h4>联系支持</h4>
            <p>如有任何问题或建议，请联系：<a href="mailto:15058593138@qq.com">15058593138@qq.com</a></p>
        </div>
    </div>
</div>

<style>
.help-content {
    max-width: 800px;
    margin: 0 auto;
}

.help-section {
    margin-bottom: 25px;
}

.help-section h4 {
    border-left: 4px solid #1976d2;
    padding-left: 10px;
    margin-bottom: 15px;
}

.help-list {
    margin-left: 20px;
    padding-left: 0;
}

.help-list li {
    margin-bottom: 10px;
}

.help-code {
    background-color: #f5f5f5;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    overflow-x: auto;
    white-space: pre-wrap;
    margin: 10px 0;
}

.help-qa {
    margin-bottom: 15px;
}

.help-question {
    font-weight: bold;
    color: #1976d2;
    margin-bottom: 5px;
}

.help-answer {
    margin-left: 20px;
}

@media (max-width: 768px) {
    .help-content {
        padding: 0 10px;
    }
}
</style>

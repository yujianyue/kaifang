<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/iikai.php
// 文件大小: 20187 字节
/**
 * 本文件功能：处方开方模块
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
        case 'search_medicine': // 搜索药品
            $keyword = post_param('keyword');
            if (empty($keyword)) {
                json_result(1, '请输入药品名称');
            }
            
            // 查询药品信息
            $sql = "SELECT * FROM medicine WHERE 
                    medicine_name LIKE '%{$keyword}%' OR 
                    alias LIKE '%{$keyword}%' OR 
                    pinyin LIKE '%{$keyword}%' OR 
                    short_pinyin LIKE '%{$keyword}%' 
                    ORDER BY count_times DESC LIMIT 10";
            
            $medicines = $db->getAll($sql);
            
            json_result(0, '查询成功', $medicines);
            break;
            
        case 'get_common_prescriptions': // 获取常用处方
            $sql = "SELECT * FROM prescription WHERE is_common = 1 ORDER BY id DESC";
            $common_prescriptions = $db->getAll($sql);
            
            json_result(0, '查询成功', $common_prescriptions);
            break;
            
        case 'get_prescription_detail': // 获取处方详情
            $id = post_param('id');
            if (empty($id)) {
                json_result(1, '参数错误');
            }
            
            // 查询处方信息
            $sql = "SELECT * FROM prescription WHERE id = '{$id}'";
            $prescription = $db->getRow($sql);
            
            if (!$prescription) {
                json_result(1, '处方不存在');
            }
            
            json_result(0, '查询成功', $prescription);
            break;
            
        case 'save_prescription': // 保存处方
            // 获取基本信息
            $patient_name = post_param('patient_name');
            $patient_birthday = post_param('patient_birthday');
            $patient_mobile = post_param('patient_mobile');
            $diagnosis = post_param('diagnosis');
            $advice = post_param('advice');
            $medicine_detail = post_param('medicine_detail');
            $total_price = post_param('total_price');
            $is_common = post_param('is_common', '0');
            $common_name = post_param('common_name', '');
            
            // 验证必填字段
            if (empty($patient_name)) {
                json_result(1, '请输入患者姓名');
            }
            
            if (empty($patient_birthday) || strlen($patient_birthday) != 8 || !is_numeric($patient_birthday)) {
                json_result(1, '请输入正确的出生日期（8位数字）');
            }
            
            if (empty($patient_mobile) || !is_numeric($patient_mobile)) {
                json_result(1, '请输入正确的手机号');
            }
            
            if (empty($medicine_detail)) {
                json_result(1, '请添加至少一种药品');
            }
            
            // 生成订单号
            $order_number = generate_order_number();
            
            // 准备数据
            $data = [
                'order_number' => $order_number,
                'patient_name' => $patient_name,
                'patient_birthday' => $patient_birthday,
                'patient_mobile' => $patient_mobile,
                'diagnosis' => $diagnosis,
                'advice' => $advice,
                'medicine_detail' => $medicine_detail,
                'total_price' => $total_price,
                'is_common' => $is_common,
                'common_name' => $common_name,
                'create_time' => date('Y-m-d H:i:s')
            ];
            
            // 插入数据
            $result = $db->insert('prescription', $data);
            
            if ($result) {
                // 更新药品使用次数
                $medicines = json_decode($medicine_detail, true);
                if (is_array($medicines)) {
                    foreach ($medicines as $med) {
                        if (isset($med['medicine_id'])) {
                            $sql = "UPDATE medicine SET count_times = count_times + 1 WHERE id = '{$med['medicine_id']}'";
                            $db->execute($sql);
                        }
                    }
                }
                
                json_result(0, '处方保存成功', ['id' => $result, 'order_number' => $order_number]);
            } else {
                json_result(1, '处方保存失败');
            }
            break;
            
        default:
            json_result(1, '未知操作');
            break;
    }
    
    exit;
}
?>

<div class="panel">
    <div class="panel-header">
        <h3>中医处方开具</h3>
    </div>
    <div class="panel-body">
        <form id="prescription-form" class="form">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="patient_name">患者姓名 <span class="required">*</span></label>
                    <input type="text" id="patient_name" name="patient_name" class="form-control" placeholder="请输入患者姓名" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="patient_birthday">出生日期 (YYYYMMDD) <span class="required">*</span></label>
                    <input type="text" id="patient_birthday" name="patient_birthday" class="form-control" placeholder="如：19900101" maxlength="8" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="patient_mobile">手机号码 <span class="required">*</span></label>
                    <input type="text" id="patient_mobile" name="patient_mobile" class="form-control" placeholder="请输入手机号码" maxlength="11" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="diagnosis">诊断信息</label>
                    <textarea id="diagnosis" name="diagnosis" class="form-control" placeholder="请输入诊断信息" rows="2"></textarea>
                </div>
                <div class="form-group col-md-6">
                    <label for="advice">医嘱</label>
                    <textarea id="advice" name="advice" class="form-control" placeholder="请输入医嘱" rows="2"></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label>药品列表</label>
                <div class="medicine-input">
                    <input type="text" id="medicine_name" class="form-control medicine-search" placeholder="请输入药品名称">
                    <div id="medicine-suggestions" class="autocomplete-items"></div>
                </div>
            </div>
            
            <div class="medicine-list-container">
                <table class="table" id="medicine-table">
                    <thead>
                        <tr>
                            <th>药品名称</th>
                            <th>单位</th>
                            <th>数量</th>
                            <th>单价(元)</th>
                            <th>金额(元)</th>
                            <th>医嘱</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="medicine-list">
                        <!-- 这里动态添加药品 -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>总计：</strong></td>
                            <td><span id="total-price">0.00</span> 元</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-12">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="is_common" name="is_common" value="1"> 标记为常用处方
                        </label>
                    </div>
                </div>
                <div class="form-group col-md-6 common-name-group" style="display: none;">
                    <label for="common_name">常用处方简称</label>
                    <input type="text" id="common_name" name="common_name" class="form-control" placeholder="请输入常用处方简称">
                </div>
            </div>
            
            <div class="form-group">
                <button type="button" class="btn btn-primary" onclick="savePrescription()">保存处方并打印</button>
                <button type="button" class="btn btn-default" onclick="resetForm()">重置表单</button>
                <button type="button" class="btn btn-success" onclick="showCommonPrescriptions()">调用常用处方</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}

.col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
    padding-right: 15px;
    padding-left: 15px;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding-right: 15px;
    padding-left: 15px;
}

.col-md-12 {
    flex: 0 0 100%;
    max-width: 100%;
    padding-right: 15px;
    padding-left: 15px;
}

.required {
    color: red;
}

.medicine-input {
    position: relative;
    margin-bottom: 15px;
}

.medicine-list-container {
    margin-bottom: 20px;
    overflow-x: auto;
}

.text-right {
    text-align: right;
}

.checkbox {
    margin-top: 10px;
}

@media (max-width: 768px) {
    .col-md-4, .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

<script>
// 存储药品列表
let medicineList = [];
let totalPrice = 0;

// 防抖搜索
const searchMedicine = debounce(function(keyword) {
    if (keyword.length < 1) {
        document.getElementById('medicine-suggestions').innerHTML = '';
        return;
    }
    
    // 发送请求查询药品
    ajaxRequest('kai.php?do=iikai&act=search_medicine', 'post', {keyword: keyword}, function(res) {
        if (res.code === 0 && res.data.length > 0) {
            // 显示搜索结果
            let html = '';
            for (let i = 0; i < res.data.length; i++) {
                let medicine = res.data[i];
                html += `<div onclick="selectMedicine(${JSON.stringify(medicine).replace(/"/g, '&quot;')})">
                            ${medicine.medicine_name} ${medicine.alias ? '(' + medicine.alias + ')' : ''}
                        </div>`;
            }
            document.getElementById('medicine-suggestions').innerHTML = html;
            document.getElementById('medicine-suggestions').style.display = 'block';
        } else {
            document.getElementById('medicine-suggestions').innerHTML = '<div>无匹配药品</div>';
            document.getElementById('medicine-suggestions').style.display = 'block';
        }
    });
}, 500);

// 药品名称输入框事件
document.getElementById('medicine_name').addEventListener('input', function() {
    searchMedicine(this.value);
});

// 点击页面其他地方关闭提示框
document.addEventListener('click', function(e) {
    if (e.target.id !== 'medicine_name' && e.target.id !== 'medicine-suggestions') {
        document.getElementById('medicine-suggestions').style.display = 'none';
    }
});

// 选择药品
function selectMedicine(medicine) {
    // 关闭提示框
    document.getElementById('medicine-suggestions').style.display = 'none';
    document.getElementById('medicine_name').value = '';
    
    // 添加药品到列表
    medicineList.push({
        medicine_id: medicine.id,
        medicine_name: medicine.medicine_name,
        unit: medicine.unit || '克',
        amount: medicine.default_amount || 10,
        price: medicine.price || 0,
        advice: medicine.advice || ''
    });
    
    // 更新显示
    renderMedicineList();
    calculateTotal();
}

// 渲染药品列表
function renderMedicineList() {
    let html = '';
    for (let i = 0; i < medicineList.length; i++) {
        let med = medicineList[i];
        html += `<tr>
                    <td>${med.medicine_name}</td>
                    <td>${med.unit}</td>
                    <td>
                        <input type="number" class="form-control input-sm" value="${med.amount}" 
                               onchange="updateAmount(${i}, this.value)" min="0" step="0.5">
                    </td>
                    <td>
                        <input type="number" class="form-control input-sm" value="${med.price}" 
                               onchange="updatePrice(${i}, this.value)" min="0" step="0.1">
                    </td>
                    <td>${(med.amount * med.price).toFixed(2)}</td>
                    <td>
                        <input type="text" class="form-control input-sm" value="${med.advice}" 
                               onchange="updateAdvice(${i}, this.value)">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeMedicine(${i})">删除</button>
                    </td>
                </tr>`;
    }
    document.getElementById('medicine-list').innerHTML = html;
}

// 更新药品数量
function updateAmount(index, value) {
    medicineList[index].amount = parseFloat(value) || 0;
    renderMedicineList();
    calculateTotal();
}

// 更新药品价格
function updatePrice(index, value) {
    medicineList[index].price = parseFloat(value) || 0;
    renderMedicineList();
    calculateTotal();
}

// 更新药品医嘱
function updateAdvice(index, value) {
    medicineList[index].advice = value;
}

// 移除药品
function removeMedicine(index) {
    medicineList.splice(index, 1);
    renderMedicineList();
    calculateTotal();
}

// 计算总价
function calculateTotal() {
    totalPrice = 0;
    for (let i = 0; i < medicineList.length; i++) {
        totalPrice += medicineList[i].amount * medicineList[i].price;
    }
    document.getElementById('total-price').innerText = totalPrice.toFixed(2);
}

// 处理常用处方复选框
document.getElementById('is_common').addEventListener('change', function() {
    if (this.checked) {
        document.querySelector('.common-name-group').style.display = 'block';
    } else {
        document.querySelector('.common-name-group').style.display = 'none';
        document.getElementById('common_name').value = '';
    }
});

// 显示常用处方列表
function showCommonPrescriptions() {
    ajaxRequest('kai.php?do=iikai&act=get_common_prescriptions', 'post', {}, function(res) {
        if (res.code === 0 && res.data.length > 0) {
            let html = '<div class="common-prescription-list">';
            
            for (let i = 0; i < res.data.length; i++) {
                let prescription = res.data[i];
                html += `<div class="common-prescription-item" onclick="loadPrescription(${prescription.id})">
                            <span class="prescription-name">${prescription.common_name || '处方#' + prescription.id}</span>
                            <span class="prescription-date">${prescription.create_time}</span>
                         </div>`;
            }
            
            html += '</div>';
            
            showModal('选择常用处方', html, [
                {text: '关闭', type: 'btn-default', callback: closeModal}
            ]);
        } else {
            showToast('暂无常用处方', 'info');
        }
    });
}
function reverse_safe_filter(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.innerHTML = str;
    str = div.textContent; // 还原 HTML 转义
    str = str.replace(/&#39;/g, "'"); // 还原单引号
    return str;
}
// 加载处方
function loadPrescription(id) {
    ajaxRequest('kai.php?do=iikai&act=get_prescription_detail', 'post', {id: id}, function(res) {
        if (res.code === 0) {
            const prescription = res.data;
            
            // 关闭模态框
            closeModal();
            
            // 确认是否要覆盖当前处方
            if (medicineList.length > 0) {
                if (!confirm('当前处方已有药品，是否要替换？')) {
                    return;
                }
            }
            
            // 清空现有药品列表
            medicineList = [];
            
            // 解析药方详情
            try {
                const medicines = JSON.parse(reverse_safe_filter(prescription.medicine_detail));
                medicineList = medicines;
                
                // 更新显示
                renderMedicineList();
                calculateTotal();
                
                showToast('已加载常用处方', 'success');
            } catch (e) {
                showToast('处方数据格式错误', 'error');
            }
        } else {
            showToast(res.msg, 'error');
        }
    });
}

// 保存处方
function savePrescription() {
    // 验证必填项
    const patientName = document.getElementById('patient_name').value.trim();
    const patientBirthday = document.getElementById('patient_birthday').value.trim();
    const patientMobile = document.getElementById('patient_mobile').value.trim();
    
    if (!patientName) {
        showToast('请输入患者姓名', 'error');
        return;
    }
    
    if (!patientBirthday || patientBirthday.length !== 8 || !/^\d{8}$/.test(patientBirthday)) {
        showToast('请输入正确的出生日期（8位数字）', 'error');
        return;
    }
    
    if (!patientMobile || !/^\d{11}$/.test(patientMobile)) {
        showToast('请输入正确的手机号', 'error');
        return;
    }
    
    if (medicineList.length === 0) {
        showToast('请添加至少一种药品', 'error');
        return;
    }
    
    // 获取表单数据
    const formData = {
        patient_name: patientName,
        patient_birthday: patientBirthday,
        patient_mobile: patientMobile,
        diagnosis: document.getElementById('diagnosis').value.trim(),
        advice: document.getElementById('advice').value.trim(),
        medicine_detail: JSON.stringify(medicineList),
        total_price: totalPrice.toFixed(2),
        is_common: document.getElementById('is_common').checked ? 1 : 0,
        common_name: document.getElementById('common_name').value.trim()
    };
    
    // 如果勾选了常用处方但未填写简称
    if (formData.is_common === 1 && !formData.common_name) {
        showToast('请输入常用处方简称', 'error');
        return;
    }
    
    // 发送保存请求
    ajaxRequest('kai.php?do=iikai&act=save_prescription', 'post', formData, function(res) {
        if (res.code === 0) {
            showToast(res.msg, 'success');
            
            // 打开打印页面
            const printWindow = window.open(`kai.php?do=print&order=${res.data.order_number}`, '_blank');
            if (printWindow) {
                printWindow.focus();
            } else {
                alert('请允许弹出窗口以打开打印页面');
            }
            
            // 重置表单
            resetForm();
        } else {
            showToast(res.msg, 'error');
        }
    });
}

// 重置表单
function resetForm() {
    document.getElementById('prescription-form').reset();
    medicineList = [];
    renderMedicineList();
    calculateTotal();
    document.querySelector('.common-name-group').style.display = 'none';
}
</script>

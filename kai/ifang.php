<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/ifang.php
// 文件大小: 18590 字节
/**
 * 本文件功能：药品库管理
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
        case 'list': // 获取药品列表
            $page = post_param('page', 1);
            $page_size = post_param('page_size', 10);
            $search_keyword = post_param('search_keyword', '');
            
            // 构建查询条件
            $where = '';
            if ($search_keyword) {
                $where = "medicine_name LIKE '%{$search_keyword}%' OR alias LIKE '%{$search_keyword}%'";
            }
            
            // 获取分页数据
            $result = $db->getPage('medicine', $page, $page_size, $where, 'id DESC');
            
            json_result(0, '查询成功', $result);
            break;
            
        case 'add': // 添加药品
            $medicine_name = post_param('medicine_name');
            $alias = post_param('alias');
            $unit = post_param('unit', '克');
            $default_amount = post_param('default_amount', 10);
            $price = post_param('price', 0);
            $advice = post_param('advice');
            
            // 验证必填字段
            if (empty($medicine_name)) {
                json_result(1, '请输入药品名称');
            }
            
            // 验证药品名称是否已存在
            $sql = "SELECT id FROM medicine WHERE medicine_name = '{$medicine_name}'";
            $exist = $db->getRow($sql);
            if ($exist) {
                json_result(1, '药品名称已存在');
            }
            
            // 生成拼音和简拼（简化版本）
            $pinyin = getPinyin($medicine_name);
            $short_pinyin = getShortPinyin($medicine_name);
            
            // 准备数据
            $data = [
                'medicine_name' => $medicine_name,
                'alias' => $alias,
                'pinyin' => $pinyin,
                'short_pinyin' => $short_pinyin,
                'unit' => $unit,
                'default_amount' => $default_amount,
                'price' => $price,
                'count_times' => 0, // 初始记次为0
                'advice' => $advice
            ];
            
            // 插入数据
            $result = $db->insert('medicine', $data);
            
            if ($result) {
                json_result(0, '药品添加成功', ['id' => $result]);
            } else {
                json_result(1, '药品添加失败');
            }
            break;
            
        case 'edit': // 编辑药品
            $id = post_param('id');
            $medicine_name = post_param('medicine_name');
            $alias = post_param('alias');
            $unit = post_param('unit', '克');
            $default_amount = post_param('default_amount', 10);
            $price = post_param('price', 0);
            $advice = post_param('advice');
            
            // 验证必填字段
            if (empty($id) || !is_numeric($id)) {
                json_result(1, '参数错误');
            }
            
            if (empty($medicine_name)) {
                json_result(1, '请输入药品名称');
            }
            
            // 验证药品名称是否已存在（排除自己）
            $sql = "SELECT id FROM medicine WHERE medicine_name = '{$medicine_name}' AND id != {$id}";
            $exist = $db->getRow($sql);
            if ($exist) {
                json_result(1, '药品名称已存在');
            }
            
            // 生成拼音和简拼
            $pinyin = getPinyin($medicine_name);
            $short_pinyin = getShortPinyin($medicine_name);
            
            // 准备数据
            $data = [
                'medicine_name' => $medicine_name,
                'alias' => $alias,
                'pinyin' => $pinyin,
                'short_pinyin' => $short_pinyin,
                'unit' => $unit,
                'default_amount' => $default_amount,
                'price' => $price,
                'advice' => $advice
            ];
            
            // 更新数据
            $result = $db->update('medicine', $data, "id = {$id}");
            
            if ($result) {
                json_result(0, '药品更新成功');
            } else {
                json_result(1, '药品更新失败');
            }
            break;
            
        case 'delete': // 删除药品
            $id = post_param('id');
            
            // 验证参数
            if (empty($id) || !is_numeric($id)) {
                json_result(1, '参数错误');
            }
            
            // 删除数据
            $result = $db->delete('medicine', "id = {$id}");
            
            if ($result) {
                json_result(0, '药品删除成功');
            } else {
                json_result(1, '药品删除失败');
            }
            break;
            
        case 'batch_delete': // 批量删除
            $ids = post_param('ids');
            if (empty($ids)) {
                json_result(1, '请选择要删除的药品');
            }
            
            // 解析ID数组
            $id_array = explode(',', $ids);
            if (empty($id_array)) {
                json_result(1, '参数错误');
            }
            
            // 批量删除
            $id_str = implode(',', $id_array);
            $result = $db->delete('medicine', "id IN ({$id_str})");
            
            if ($result) {
                json_result(0, '批量删除成功');
            } else {
                json_result(1, '操作失败');
            }
            break;
            
        case 'get': // 获取药品详情
            $id = post_param('id');
            
            // 验证参数
            if (empty($id) || !is_numeric($id)) {
                json_result(1, '参数错误');
            }
            
            // 查询药品信息
            $sql = "SELECT * FROM medicine WHERE id = {$id}";
            $medicine = $db->getRow($sql);
            
            if ($medicine) {
                json_result(0, '查询成功', $medicine);
            } else {
                json_result(1, '药品不存在');
            }
            break;
            
        default:
            json_result(1, '未知操作');
            break;
    }
    
    exit;
}

/**
 * 简易中文转拼音函数（仅示例）
 * @param string $str 中文字符串
 * @return string 拼音
 */
function getPinyin($str) {
    // 由于没有使用第三方库，这里提供一个非常简化的拼音转换
    // 实际项目应该使用更完整的拼音库
    return strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', $str));
}

/**
 * 简易中文转简拼函数（仅示例）
 * @param string $str 中文字符串
 * @return string 简拼
 */
function getShortPinyin($str) {
    // 简化版本
    return strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', $str));
}
?>

<div class="panel">
    <div class="panel-header">
        <h3>药品库管理</h3>
    </div>
    <div class="panel-body">
        <div class="list-header">
            <div class="list-title">药品列表</div>
            <div class="search-box">
                <input type="text" id="search-keyword" class="form-control" placeholder="请输入药品名称/别名">
                <button type="button" class="btn btn-primary" onclick="searchMedicine()">查询</button>
                <button type="button" class="btn btn-success" onclick="showAddForm()">新增药品</button>
                <button type="button" class="btn btn-danger" onclick="batchDelete()">批量删除</button>
            </div>
        </div>
        
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="check-all" onchange="toggleCheckAll(this.checked)"></th>
                        <th>药品名称</th>
                        <th>别名</th>
                        <th>单位</th>
                        <th>默认数量</th>
                        <th>参考价格</th>
                        <th>使用次数</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="medicine-list">
                    <!-- 动态加载数据 -->
                </tbody>
            </table>
        </div>
        
        <div id="pagination" class="pagination">
            <!-- 分页控件 -->
        </div>
    </div>
</div>

<script>
// 当前页码
let currentPage = 1;
// 每页记录数
let pageSize = 10;
// 总页数
let totalPage = 1;

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    loadMedicineList();
});

// 加载药品列表
function loadMedicineList() {
    const searchKeyword = document.getElementById('search-keyword').value;
    
    const params = {
        page: currentPage,
        page_size: pageSize,
        search_keyword: searchKeyword
    };
    
    ajaxRequest('kai.php?do=ifang&act=list', 'post', params, function(res) {
        if (res.code === 0) {
            renderMedicineList(res.data);
            // 更新分页
            totalPage = res.data.total_page;
            initPagination(totalPage, currentPage, function(page) {
                currentPage = page;
                loadMedicineList();
            });
        } else {
            showToast(res.msg, 'error');
        }
    });
}

// 渲染药品列表
function renderMedicineList(data) {
    const list = document.getElementById('medicine-list');
    list.innerHTML = '';
    
    if (data.data.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="8" class="text-center">暂无药品记录</td>';
        list.appendChild(tr);
        return;
    }
    
    for (let i = 0; i < data.data.length; i++) {
        const item = data.data[i];
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td><input type="checkbox" class="check-item" value="${item.id}"></td>
            <td>${item.medicine_name}</td>
            <td>${item.alias || '-'}</td>
            <td>${item.unit}</td>
            <td>${item.default_amount}</td>
            <td>${item.price}</td>
            <td>${item.count_times}</td>
            <td>
                <button type="button" class="btn btn-sm btn-primary" onclick="showEditForm(${item.id})">编辑</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteMedicine(${item.id})">删除</button>
            </td>
        `;
        
        list.appendChild(tr);
    }
}

// 搜索药品
function searchMedicine() {
    currentPage = 1;
    loadMedicineList();
}

// 显示添加表单
function showAddForm() {
    let html = `
        <form id="medicine-form" class="form">
            <div class="form-group">
                <label for="medicine_name">药品名称 <span class="required">*</span></label>
                <input type="text" id="medicine_name" name="medicine_name" class="form-control" placeholder="请输入药品名称" required>
            </div>
            <div class="form-group">
                <label for="alias">别名</label>
                <input type="text" id="alias" name="alias" class="form-control" placeholder="请输入别名（可选）">
            </div>
            <div class="form-group">
                <label for="unit">单位</label>
                <input type="text" id="unit" name="unit" class="form-control" value="克" placeholder="请输入单位">
            </div>
            <div class="form-group">
                <label for="default_amount">默认数量</label>
                <input type="number" id="default_amount" name="default_amount" class="form-control" value="10" placeholder="请输入默认数量">
            </div>
            <div class="form-group">
                <label for="price">参考价格</label>
                <input type="number" id="price" name="price" class="form-control" value="0" step="0.01" placeholder="请输入参考价格">
            </div>
            <div class="form-group">
                <label for="advice">医嘱</label>
                <textarea id="advice" name="advice" class="form-control" placeholder="请输入医嘱（可选）"></textarea>
            </div>
        </form>
    `;
    
    showModal('添加药品', html, [
        {text: '取消', type: 'btn-default', callback: closeModal},
        {text: '保存', type: 'btn-primary', callback: addMedicine}
    ]);
}

// 添加药品
function addMedicine() {
    // 获取表单数据
    const medicineForm = document.getElementById('medicine-form');
    const formData = {
        medicine_name: medicineForm.medicine_name.value.trim(),
        alias: medicineForm.alias.value.trim(),
        unit: medicineForm.unit.value.trim(),
        default_amount: medicineForm.default_amount.value.trim(),
        price: medicineForm.price.value.trim(),
        advice: medicineForm.advice.value.trim()
    };
    
    // 验证必填项
    if (!formData.medicine_name) {
        showToast('请输入药品名称', 'error');
        return;
    }
    
    // 发送请求
    ajaxRequest('kai.php?do=ifang&act=add', 'post', formData, function(res) {
        showToast(res.msg, res.code === 0 ? 'success' : 'error');
        if (res.code === 0) {
            closeModal();
            loadMedicineList();
        }
    });
}

// 显示编辑表单
function showEditForm(id) {
    // 先获取药品详情
    ajaxRequest('kai.php?do=ifang&act=get', 'post', {id: id}, function(res) {
        if (res.code === 0) {
            const medicine = res.data;
            
            let html = `
                <form id="medicine-form" class="form">
                    <input type="hidden" id="id" name="id" value="${medicine.id}">
                    <div class="form-group">
                        <label for="medicine_name">药品名称 <span class="required">*</span></label>
                        <input type="text" id="medicine_name" name="medicine_name" class="form-control" value="${medicine.medicine_name}" required>
                    </div>
                    <div class="form-group">
                        <label for="alias">别名</label>
                        <input type="text" id="alias" name="alias" class="form-control" value="${medicine.alias || ''}">
                    </div>
                    <div class="form-group">
                        <label for="unit">单位</label>
                        <input type="text" id="unit" name="unit" class="form-control" value="${medicine.unit}">
                    </div>
                    <div class="form-group">
                        <label for="default_amount">默认数量</label>
                        <input type="number" id="default_amount" name="default_amount" class="form-control" value="${medicine.default_amount}">
                    </div>
                    <div class="form-group">
                        <label for="price">参考价格</label>
                        <input type="number" id="price" name="price" class="form-control" value="${medicine.price}" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="advice">医嘱</label>
                        <textarea id="advice" name="advice" class="form-control">${medicine.advice || ''}</textarea>
                    </div>
                </form>
            `;
            
            showModal('编辑药品', html, [
                {text: '取消', type: 'btn-default', callback: closeModal},
                {text: '保存', type: 'btn-primary', callback: editMedicine}
            ]);
        } else {
            showToast(res.msg, 'error');
        }
    });
}

// 编辑药品
function editMedicine() {
    // 获取表单数据
    const medicineForm = document.getElementById('medicine-form');
    const formData = {
        id: medicineForm.id.value.trim(),
        medicine_name: medicineForm.medicine_name.value.trim(),
        alias: medicineForm.alias.value.trim(),
        unit: medicineForm.unit.value.trim(),
        default_amount: medicineForm.default_amount.value.trim(),
        price: medicineForm.price.value.trim(),
        advice: medicineForm.advice.value.trim()
    };
    
    // 验证必填项
    if (!formData.medicine_name) {
        showToast('请输入药品名称', 'error');
        return;
    }
    
    // 发送请求
    ajaxRequest('kai.php?do=ifang&act=edit', 'post', formData, function(res) {
        showToast(res.msg, res.code === 0 ? 'success' : 'error');
        if (res.code === 0) {
            closeModal();
            loadMedicineList();
        }
    });
}

// 删除药品
function deleteMedicine(id) {
    if (confirm('确定要删除此药品吗？')) {
        ajaxRequest('kai.php?do=ifang&act=delete', 'post', {id: id}, function(res) {
            showToast(res.msg, res.code === 0 ? 'success' : 'error');
            if (res.code === 0) {
                loadMedicineList();
            }
        });
    }
}

// 切换全选
function toggleCheckAll(checked) {
    const checkItems = document.getElementsByClassName('check-item');
    for (let i = 0; i < checkItems.length; i++) {
        checkItems[i].checked = checked;
    }
}

// 批量删除
function batchDelete() {
    const checkItems = document.getElementsByClassName('check-item');
    const ids = [];
    
    for (let i = 0; i < checkItems.length; i++) {
        if (checkItems[i].checked) {
            ids.push(checkItems[i].value);
        }
    }
    
    if (ids.length === 0) {
        showToast('请选择要删除的药品', 'warning');
        return;
    }
    
    if (confirm(`确定要删除选中的 ${ids.length} 个药品吗？`)) {
        ajaxRequest('kai.php?do=ifang&act=batch_delete', 'post', {ids: ids.join(',')}, function(res) {
            showToast(res.msg, res.code === 0 ? 'success' : 'error');
            if (res.code === 0) {
                document.getElementById('check-all').checked = false;
                loadMedicineList();
            }
        });
    }
}
</script>

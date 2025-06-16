<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: kai/ilist.php
// 文件大小: 8928 字节
/**
 * 本文件功能：历史处方管理
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
        case 'list': // 获取处方列表
            $page = post_param('page', 1);
            $page_size = post_param('page_size', 10);
            $search_field = post_param('search_field', '');
            $search_keyword = post_param('search_keyword', '');
            
            // 构建查询条件
            $where = '';
            if ($search_field && $search_keyword) {
                $where = "{$search_field} LIKE '%{$search_keyword}%'";
            }
            
            // 获取分页数据
            $result = $db->getPage('prescription', $page, $page_size, $where, 'id DESC');
            
            json_result(0, '查询成功', $result);
            break;
            
        case 'detail': // 获取处方详情
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
            
        case 'void': // 作废处方
            $id = post_param('id');
            if (empty($id)) {
                json_result(1, '参数错误');
            }
            
            // 更新处方状态
            $data = [
                'status' => 0 // 0表示作废
            ];
            
            $result = $db->update('prescription', $data, "id = '{$id}'");
            
            if ($result) {
                json_result(0, '处方已作废');
            } else {
                json_result(1, '操作失败');
            }
            break;
            
        case 'batch_delete': // 批量删除
            $ids = post_param('ids');
            if (empty($ids)) {
                json_result(1, '请选择要删除的处方');
            }
            
            // 解析ID数组
            $id_array = explode(',', $ids);
            if (empty($id_array)) {
                json_result(1, '参数错误');
            }
            
            // 批量删除
            $id_str = implode("','", $id_array);
            $result = $db->delete('prescription', "id IN ('{$id_str}')");
            
            if ($result) {
                json_result(0, '批量删除成功');
            } else {
                json_result(1, '操作失败');
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
        <h3>历史处方管理</h3>
    </div>
    <div class="panel-body">
        <div class="list-header">
            <div class="list-title">处方列表</div>
            <div class="search-box">
                <select id="search-field" class="form-control">
                    <option value="patient_name">患者姓名</option>
                    <option value="patient_mobile">手机号</option>
                    <option value="order_number">订单号</option>
                </select>
                <input type="text" id="search-keyword" class="form-control" placeholder="请输入关键词">
                <button type="button" class="btn btn-primary" onclick="searchPrescription()">查询</button>
                <button type="button" class="btn btn-danger" onclick="batchDelete()">批量删除</button>
            </div>
        </div>
        
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="check-all" onchange="toggleCheckAll(this.checked)"></th>
                        <th>订单号</th>
                        <th>患者姓名</th>
                        <th>手机号</th>
                        <th>总价(元)</th>
                        <th>常用方</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="prescription-list">
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
    loadPrescriptionList();
});

// 加载处方列表
function loadPrescriptionList() {
    const searchField = document.getElementById('search-field').value;
    const searchKeyword = document.getElementById('search-keyword').value;
    
    const params = {
        page: currentPage,
        page_size: pageSize,
        search_field: searchField,
        search_keyword: searchKeyword
    };
    
    ajaxRequest('kai.php?do=ilist&act=list', 'post', params, function(res) {
        if (res.code === 0) {
            renderPrescriptionList(res.data);
            // 更新分页
            totalPage = res.data.total_page;
            initPagination(totalPage, currentPage, function(page) {
                currentPage = page;
                loadPrescriptionList();
            });
        } else {
            showToast(res.msg, 'error');
        }
    });
}

// 渲染处方列表
function renderPrescriptionList(data) {
    const list = document.getElementById('prescription-list');
    list.innerHTML = '';
    
    if (data.data.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="8" class="text-center">暂无处方记录</td>';
        list.appendChild(tr);
        return;
    }
    
    for (let i = 0; i < data.data.length; i++) {
        const item = data.data[i];
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td><input type="checkbox" class="check-item" value="${item.id}"></td>
            <td>${item.order_number}</td>
            <td>${item.patient_name}</td>
            <td>${item.patient_mobile}</td>
            <td>${item.total_price}</td>
            <td>${item.is_common == 1 ? (item.common_name || '是') : '否'}</td>
            <td>${item.create_time}</td>
            <td>
                <button type="button" class="btn btn-sm btn-primary" onclick="viewPrescription('${item.order_number}')">查看</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="voidPrescription(${item.id})">作废</button>
            </td>
        `;
        
        list.appendChild(tr);
    }
}

// 搜索处方
function searchPrescription() {
    currentPage = 1;
    loadPrescriptionList();
}

// 查看处方
function viewPrescription(orderNumber) {
    window.open(`kai.php?do=print&order=${orderNumber}`, '_blank');
}

// 作废处方
function voidPrescription(id) {
    if (confirm('确定要作废该处方吗？')) {
        ajaxRequest('kai.php?do=ilist&act=void', 'post', {id: id}, function(res) {
            showToast(res.msg, res.code === 0 ? 'success' : 'error');
            if (res.code === 0) {
                loadPrescriptionList();
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
        showToast('请选择要删除的处方', 'warning');
        return;
    }
    
    if (confirm(`确定要删除选中的 ${ids.length} 个处方吗？`)) {
        ajaxRequest('kai.php?do=ilist&act=batch_delete', 'post', {ids: ids.join(',')}, function(res) {
            showToast(res.msg, res.code === 0 ? 'success' : 'error');
            if (res.code === 0) {
                document.getElementById('check-all').checked = false;
                loadPrescriptionList();
            }
        });
    }
}
</script>

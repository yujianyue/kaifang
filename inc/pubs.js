/**
 * 本文件功能：公共JavaScript函数库
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

// 通用AJAX函数
function ajaxRequest(url, method, data, callback) {
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                let response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    response = { code: 1, msg: '返回数据格式错误' };
                }
                callback(response);
            } else {
                callback({ code: 1, msg: '请求失败，状态码：' + xhr.status });
            }
        }
    };
    
    xhr.open(method, url, true);
    
    if (method.toLowerCase() === 'post') {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // 如果data是对象，转为查询字符串
        if (typeof data === 'object') {
            data = objectToQueryString(data);
        }
        
        xhr.send(data);
    } else {
        xhr.send();
    }
}

// 对象转查询字符串
function objectToQueryString(obj) {
    let parts = [];
    for (let key in obj) {
        if (obj.hasOwnProperty(key)) {
            let value = obj[key];
            parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
        }
    }
    return parts.join('&');
}

// 获取表单所有值，返回对象
function getFormValues(formId) {
    let form = document.getElementById(formId);
    if (!form) return {};
    
    let values = {};
    let elements = form.elements;
    
    for (let i = 0; i < elements.length; i++) {
        let element = elements[i];
        if (element.name) {
            switch (element.type) {
                case 'checkbox':
                    values[element.name] = element.checked ? '1' : '0';
                    break;
                case 'radio':
                    if (element.checked) {
                        values[element.name] = element.value;
                    }
                    break;
                default:
                    values[element.name] = element.value;
            }
        }
    }
    
    return values;
}

// 吐司提示
function showToast(message, type = 'info', duration = 3000) {
    let toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // 淡入显示
    setTimeout(function() {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    // 定时淡出
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        
        // 移除元素
        setTimeout(function() {
            document.body.removeChild(toast);
        }, 300);
    }, duration);
}

// 分页函数
function initPagination(totalPages, currentPage, callback) {
    const pageContainer = document.getElementById('pagination');
    if (!pageContainer) return;
    
    pageContainer.innerHTML = '';
    
    // 只有一页或没有数据时不显示分页
    if (totalPages <= 1) return;
    
    // 起始页
    const firstBtn = document.createElement('a');
    firstBtn.href = 'javascript:void(0)';
    firstBtn.textContent = '首页';
    firstBtn.classList.add('page-btn');
    if (currentPage <= 1) {
        firstBtn.classList.add('disabled');
    } else {
        firstBtn.onclick = function() {
            callback(1);
        };
    }
    pageContainer.appendChild(firstBtn);
    
    // 上一页
    const prevBtn = document.createElement('a');
    prevBtn.href = 'javascript:void(0)';
    prevBtn.textContent = '上一页';
    prevBtn.classList.add('page-btn');
    if (currentPage <= 1) {
        prevBtn.classList.add('disabled');
    } else {
        prevBtn.onclick = function() {
            callback(currentPage - 1);
        };
    }
    pageContainer.appendChild(prevBtn);
    
    // 页码下拉选择框
    const pageSelect = document.createElement('select');
    pageSelect.classList.add('page-select');
    pageSelect.onchange = function() {
        callback(parseInt(this.value));
    };
    
    for (let i = 1; i <= totalPages; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i + '/' + totalPages;
        if (i === currentPage) {
            option.selected = true;
        }
        pageSelect.appendChild(option);
    }
    pageContainer.appendChild(pageSelect);
    
    // 下一页
    const nextBtn = document.createElement('a');
    nextBtn.href = 'javascript:void(0)';
    nextBtn.textContent = '下一页';
    nextBtn.classList.add('page-btn');
    if (currentPage >= totalPages) {
        nextBtn.classList.add('disabled');
    } else {
        nextBtn.onclick = function() {
            callback(currentPage + 1);
        };
    }
    pageContainer.appendChild(nextBtn);
    
    // 最后页
    const lastBtn = document.createElement('a');
    lastBtn.href = 'javascript:void(0)';
    lastBtn.textContent = '尾页';
    lastBtn.classList.add('page-btn');
    if (currentPage >= totalPages) {
        lastBtn.classList.add('disabled');
    } else {
        lastBtn.onclick = function() {
            callback(totalPages);
        };
    }
    pageContainer.appendChild(lastBtn);
}

// 遮罩层函数
function showModal(title, content, buttons) {
    // 防止重复创建
    closeModal();
    
    // 创建遮罩层容器
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    
    // 创建模态框内容区
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    
    // 创建标题区
    const titleElement = document.createElement('div');
    titleElement.className = 'modal-title';
    titleElement.innerHTML = title;
    
    // 创建关闭按钮
    const closeButton = document.createElement('span');
    closeButton.className = 'modal-close';
    closeButton.innerHTML = '&times;';
    closeButton.onclick = closeModal;
    
    titleElement.appendChild(closeButton);
    modalContent.appendChild(titleElement);
    
    // 创建内容区
    const bodyElement = document.createElement('div');
    bodyElement.className = 'modal-body';
    bodyElement.innerHTML = content;
    modalContent.appendChild(bodyElement);
    
    // 创建按钮区
    const footerElement = document.createElement('div');
    footerElement.className = 'modal-footer';
    
    if (buttons && buttons.length > 0) {
        buttons.forEach(function(btn) {
            const button = document.createElement('button');
            button.className = 'btn ' + (btn.type || 'btn-primary');
            button.textContent = btn.text;
            button.onclick = function() {
                if (typeof btn.callback === 'function') {
                    btn.callback();
                }
            };
            footerElement.appendChild(button);
        });
    }
    
    modalContent.appendChild(footerElement);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // 显示动画
    setTimeout(function() {
        modal.classList.add('active');
    }, 10);
    
    // 阻止点击内容区关闭
    modalContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // 点击遮罩层关闭
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
}

// 关闭遮罩层
function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.classList.remove('active');
        
        // 移除动画完成后删除元素
        setTimeout(function() {
            document.body.removeChild(modal);
        }, 300);
    }
}

// 防抖函数 - 用于输入框延迟搜索
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

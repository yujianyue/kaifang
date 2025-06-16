php7+mysql5.6中医处方管理系统说明文档
=============================

一、系统简介
-----------
本系统是一款专为中医诊所设计的处方管理系统，基于PHP+MySQL开发，不依赖第三方框架，采用原生HTML5+CSS3+AJAX技术，适配手机和电脑访问。
系统支持药品管理、处方开具、打印以及历史查询等功能，操作简单，界面清晰，输入提示，适合单医生中医诊所使用。
首发版本，未经实际业务流程检验，仅供学习参考。

二、环境要求
-----------
1. PHP版本：PHP 7.0+
2. MySQL版本：MySQL 5.6+
3. 浏览器：支持HTML5的现代浏览器（Chrome、Firefox、Edge等）
4. 服务器环境：Apache或Nginx

三、默认账户和密码
---------------
* 默认用户名：admin
* 默认密码：admin123

四、文件结构及功能
---------------
【根目录】
- kai.php：系统主入口文件
- install.php：数据库安装文件
- README.txt：系统说明文档

【inc目录】：公共文件目录
- conn.php：数据库连接及配置文件
- pubs.php：公共PHP函数库
- pubs.js：公共JavaScript函数库
- pubs.css：公共样式表
- sqls.php：数据库操作类
- code.php：验证码生成文件
- head.php：公共头部
- foot.php：公共底部
- site.json.php：网站设置文件
- mima.php：管理员密码文件（自动生成）

【kai目录】：功能模块目录
- login.php：登录模块
- lgout.php：退出登录模块
- iikai.php：处方开具模块
- ilist.php：历史处方模块
- ifang.php：药品库管理模块
- idaru.php：药品导入模块
- ipass.php：修改密码模块
- ihelp.php：使用帮助模块
- print.php：处方打印模块

【cache目录】：缓存文件目录
- 用于存储临时导入文件

五、数据库结构
-----------
【处方表（prescription）】
- id：主键ID
- order_number：订单号(YmdHis)
- patient_name：患者姓名
- patient_birthday：出生日期(8位数字YYYYMMDD)
- patient_mobile：手机号码
- diagnosis：诊断信息
- advice：医嘱信息
- medicine_detail：药方详细(JSON格式)
- total_price：参考总价
- is_common：常方标记(0或1)
- common_name：常方简称
- create_time：添加时间
- status：状态(1有效, 0作废)

【药品表（medicine）】
- id：主键ID
- medicine_name：药品名称
- alias：别名
- pinyin：全拼
- short_pinyin：简拼
- unit：单位(默认"克")
- default_amount：默认数量(默认10)
- price：参考价格
- count_times：开单记次
- advice：医嘱

六、安装说明
-----------
1. 将所有文件上传到服务器
2. 创建MySQL数据库
3. 修改inc/conn.php中的数据库连接信息
4. 访问install.php进行数据库表创建
5. 安装完成后使用默认账号登录系统

七、使用流程
-----------
1. 登录系统
2. 可通过"药品导入"或"药品库管理"模块建立药品库
3. 在"处方开具"模块填写患者信息和药品信息
4. 保存处方并打印
5. 在"历史处方"模块查看历史记录

八、注意事项
-----------
1. 请定期备份数据库
2. 修改默认密码以保障系统安全
3. 如忘记密码，请删除inc/mima.php文件，系统将重置为默认密码
4. 药品导入时，请严格按照格式要求准备数据

九、联系方式
-----------
问题反馈：15058593138@qq.com（手机号同微信）

版权所有 © 2025 保留所有权利

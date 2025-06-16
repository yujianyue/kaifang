<?php

// PHP7+MySQL5.6 中医个体户处方系统 V2025.05.30
// 首发版本，仅供学习参考(或评估可用再使用)
// 演示地址: http://kaifang.chalide.cn
// 开源更新: /chalide/zhongyikaifang
// 文件路径: inc/sqls.php
// 文件大小: 6424 字节
/**
 * 本文件功能：数据库操作类
 * 版权声明：保留发行权和署名权
 * 作者信息：15058593138@qq.com
 */

class DB {
    private $conn;
    
    /**
     * 构造函数，初始化数据库连接
     * @param object $conn 数据库连接对象
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * 执行查询
     * @param string $sql SQL查询语句
     * @return object|bool 查询结果集对象或失败时返回false
     */
    public function query($sql) {
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * 执行增删改操作
     * @param string $sql SQL语句
     * @return bool 操作是否成功
     */
    public function execute($sql) {
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * 查询单行数据
     * @param string $sql SQL查询语句
     * @return array|null 查询结果数组或不存在时返回null
     */
    public function getRow($sql) {
        $result = $this->query($sql);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    /**
     * 查询多行数据
     * @param string $sql SQL查询语句
     * @return array 查询结果数组
     */
    public function getAll($sql) {
        $result = $this->query($sql);
        $rows = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }
    
    /**
     * 查询单个字段值
     * @param string $sql SQL查询语句
     * @return mixed|null 字段值或不存在时返回null
     */
    public function getOne($sql) {
        $result = $this->query($sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            return $row[0];
        }
        return null;
    }
    
    /**
     * 新增数据
     * @param string $table 表名
     * @param array $data 字段值对应数组
     * @return int|bool 新增成功返回插入ID，失败返回false
     */
    public function insert($table, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`" . $key . "`";
            $values[] = "'" . mysqli_real_escape_string($this->conn, $value) . "'";
        }
        
        $sql = "INSERT INTO `{$table}` (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
        
        if ($this->execute($sql)) {
            return mysqli_insert_id($this->conn);
        } else {
            return false;
        }
    }
    
    /**
     * 更新数据
     * @param string $table 表名
     * @param array $data 字段值对应数组
     * @param string $where 条件语句
     * @return bool 是否更新成功
     */
    public function update($table, $data, $where) {
        $set = [];
        
        foreach ($data as $key => $value) {
            $set[] = "`" . $key . "` = '" . mysqli_real_escape_string($this->conn, $value) . "'";
        }
        
        $sql = "UPDATE `{$table}` SET " . implode(", ", $set) . " WHERE " . $where;
        
        return $this->execute($sql);
    }
    
    /**
     * 删除数据
     * @param string $table 表名
     * @param string $where 条件语句
     * @return bool 是否删除成功
     */
    public function delete($table, $where) {
        $sql = "DELETE FROM `{$table}` WHERE " . $where;
        return $this->execute($sql);
    }
    
    /**
     * 获取记录总数
     * @param string $table 表名
     * @param string $where 条件语句，可选
     * @return int 记录总数
     */
    public function count($table, $where = '') {
        $sql = "SELECT COUNT(*) FROM `{$table}`";
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        return (int)$this->getOne($sql);
    }
    
    /**
     * 获取分页数据
     * @param string $table 表名
     * @param int $page 当前页码
     * @param int $page_size 每页记录数
     * @param string $where 条件语句，可选
     * @param string $order 排序语句，可选
     * @param string $fields 查询字段，可选，默认为*
     * @return array [总记录数, 总页数, 当前页数据]
     */
    public function getPage($table, $page, $page_size, $where = '', $order = '', $fields = '*') {
        // 获取总记录数
        $total = $this->count($table, $where);
        
        // 计算总页数
        $total_page = ceil($total / $page_size);
        
        // 确保页码有效
        $page = max(1, min($page, $total_page));
        
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
        
        // 构建查询SQL
        $sql = "SELECT {$fields} FROM `{$table}`";
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        if ($order) {
            $sql .= " ORDER BY " . $order;
        }
        $sql .= " LIMIT {$offset}, {$page_size}";
        
        // 获取页面数据
        $list = $this->getAll($sql);
        
        // 返回分页数据
        return [
            'total' => $total,
            'total_page' => $total_page,
            'data' => $list
        ];
    }
    
    /**
     * 获取最后一次错误信息
     * @return string 错误信息
     */
    public function getError() {
        return mysqli_error($this->conn);
    }
    
    /**
     * 执行事务操作
     * @param callable $callback 回调函数
     * @return bool 事务是否执行成功
     */
    public function transaction($callback) {
        // 开始事务
        mysqli_autocommit($this->conn, false);
        
        try {
            $result = call_user_func($callback, $this);
            
            if ($result) {
                // 提交事务
                mysqli_commit($this->conn);
            } else {
                // 回滚事务
                mysqli_rollback($this->conn);
            }
            
            mysqli_autocommit($this->conn, true);
            
            return $result;
        } catch (Exception $e) {
            // 发生异常，回滚事务
            mysqli_rollback($this->conn);
            mysqli_autocommit($this->conn, true);
            
            return false;
        }
    }
}
?>

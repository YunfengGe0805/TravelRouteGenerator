<?php
// 强制设置JSON头
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-cache, must-revalidate");

// 引入数据库连接
require 'db_connect.php';

try {
    // 获取用户名参数
    $username = $_GET['name'] ?? '';
    
    // 验证用户名格式（字母数字下划线，长度3-20）
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        throw new Exception("Invalid username format");
    }

    // 查询用户历史（使用PDO预处理防止SQL注入）
    $query = "SELECT path_id, poi_id FROM user_history WHERE name = ? LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetch();

    if ($result) {
        echo json_encode([
            'success' => true,
            'pathLayer' => $result['path_id'],
            'poiLayer' => $result['poi_id']
        ]);
    } else {
        throw new Exception("User history not found");
    }

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
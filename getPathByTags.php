<?php
// 强制设置JSON头（必须位于文件顶部）
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Cache-Control: no-cache, must-revalidate");

// 数据库连接配置
require 'db_connect.php';


try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $like = array_filter($data['like'] ?? [], function($v) {
        return preg_match('/^[a-zA-Z0-9_\s]+$/', $v); // 允许字母数字空格
    });
    $dislike = array_filter($data['dislike'] ?? [], function($v) {
        return preg_match('/^[a-zA-Z0-9_\s]+$/', $v);
    });

    // 严格生成PG数组（关键修复！）
    function buildPgArray($items) {
        $quoted = array_map(function($item) {
            return '"' . str_replace('"', '""', $item) . '"'; // 处理带空格和引号的标签
        }, $items);
        return '{' . implode(',', $quoted) . '}';
    }

    $conditions = [];
    $params = [];
    $paramTypes = '';

    // 严格条件：tag1-tag4必须全部匹配like（保持原逻辑）
    if (!empty($like)) {
        $pgArray = buildPgArray($like);
        // 四个tag都必须匹配
        $conditions = array_fill(0, 4, "tag1 = ANY(?::text[])");
        $params = array_fill(0, 4, $pgArray);
        $paramTypes = str_repeat('s', 4);
    } else {
        // 如果like为空，要求四个tag都有值
        $conditions = ["tag1 IS NOT NULL", "tag2 IS NOT NULL", "tag3 IS NOT NULL", "tag4 IS NOT NULL"];
    }

    // 处理dislike
    if (!empty($dislike)) {
        $conditions[] = "tag5 = ANY(?::text[])";
        $params[] = buildPgArray($dislike);
        $paramTypes .= 's';
    }

    // 构建查询
    $query = "SELECT path_id, poi_id FROM tag_search 
              WHERE ".implode(" AND ", $conditions)." 
              ORDER BY path_id DESC 
              LIMIT 1";

    // 调试日志（重要！）
    error_log("Final Query: ".$query);
    error_log("Parameters: ".json_encode($params));

    // 执行查询
    $stmt = $pdo->prepare($query);
    foreach ($params as $index => $value) {
        $stmt->bindValue($index+1, $value, PDO::PARAM_STR);
    }
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result) {
        echo json_encode([
            'success' => true,
            'pathLayer' => $result['path_id'],
            'poiLayer' => $result['poi_id']
        ]);
    } else {
        throw new Exception("No matching path found with current strict filters");
    }

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
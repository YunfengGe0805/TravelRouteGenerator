<?php
$host = '127.0.0.1';
$port = '11741'; 
$db   = 'test1';
$user = 'postgres';
$pass = 'iAmTheBoss'; 


$dsn = "pgsql:host=$host;port=$port;dbname=$db";
$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>


<?php
try {
    $host = '127.0.0.1';
    $port = '11741';
    $dbname = 'test1';
    $user = 'postgres';
    $pass = 'iAmTheBoss';
    
    // 创建连接
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // 设置编码
    $pdo->exec("SET NAMES 'UTF8'");
    
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'error' => '数据库连接失败，请联系管理员'
    ]));
}
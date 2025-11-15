<?php
// Configurações do banco de dados
$host = '127.0.0.1';
$db   = 'tarefas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opções do PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Cria a instância do PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Em caso de erro, exibe a mensagem e encerra a execução
    http_response_code(500);
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
    exit;
}
?>
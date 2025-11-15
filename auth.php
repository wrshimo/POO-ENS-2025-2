<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($login) || empty($senha)) {
    header('Location: login.php?error=Usuário e senha são obrigatórios');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE login = ?");
    $stmt->execute([$login]);
    $funcionario = $stmt->fetch();

    // Por simplicidade, estamos armazenando senhas em texto plano.
    // Em um ambiente de produção, use password_hash() e password_verify().
    if ($funcionario && $senha === $funcionario['senha']) {
        // Autenticação bem-sucedida
        $_SESSION['id_funcionario'] = $funcionario['id'];
        $_SESSION['nome_funcionario'] = $funcionario['nome'];
        header('Location: index.php');
        exit;
    } else {
        // Falha na autenticação
        header('Location: login.php?error=Usuário ou senha inválidos');
        exit;
    }
} catch (PDOException $e) {
    // Redireciona para a página de login com uma mensagem de erro genérica
    error_log("Erro de autenticação: " . $e->getMessage()); // Log do erro real
    header('Location: login.php?error=Ocorreu um erro no servidor. Tente novamente mais tarde.');
    exit;
}
?>

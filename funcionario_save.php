<?php
require_once 'check_auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: funcionarios.php');
    exit;
}

$id = $_GET['id'] ?? null;
$nome = $_POST['nome'] ?? '';
$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($nome) || empty($login)) {
    header("Location: funcionario_form.php?id=$id&error=Nome e login são obrigatórios");
    exit;
}

try {
    if ($id) {
        // Atualizar funcionário existente
        if (!empty($senha)) {
            $sql = "UPDATE funcionarios SET nome = ?, login = ?, senha = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $login, $senha, $id]);
        } else {
            $sql = "UPDATE funcionarios SET nome = ?, login = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $login, $id]);
        }
    } else {
        // Inserir novo funcionário
        if (empty($senha)) {
             header("Location: funcionario_form.php?error=Senha é obrigatória para novos funcionários");
             exit;
        }
        $sql = "INSERT INTO funcionarios (nome, login, senha) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $login, $senha]);
    }

    header("Location: funcionarios.php");
    exit;

} catch (PDOException $e) {
    // Tratar erro de login duplicado ou outros erros de banco
    if ($e->errorInfo[1] == 1062) { // Código de erro para entrada duplicada
        $error = "Este login já está em uso. Tente outro.";
    } else {
        $error = "Ocorreu um erro ao salvar o funcionário. Tente novamente.";
        error_log("Erro ao salvar funcionário: " . $e->getMessage());
    }
    header("Location: funcionario_form.php?id=$id&error=" . urlencode($error));
    exit;
}
?>
<?php
require_once 'check_auth.php';
require_once 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: funcionarios.php');
    exit;
}

try {
    // Verifica se o funcionário tem tarefas associadas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE id_funcionario = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Não permite excluir se houver tarefas
        header("Location: funcionarios.php?error=" . urlencode('Não é possível excluir funcionários com tarefas associadas.'));
        exit;
    }

    // Exclui o funcionário
    $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: funcionarios.php");
    exit;

} catch (PDOException $e) {
    error_log("Erro ao excluir funcionário: " . $e->getMessage());
    header("Location: funcionarios.php?error=" . urlencode('Ocorreu um erro ao excluir o funcionário.'));
    exit;
}
?>
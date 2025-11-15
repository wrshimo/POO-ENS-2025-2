<?php
require_once 'check_auth.php';
require_once 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: tarefas.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: tarefas.php");
    exit;

} catch (PDOException $e) {
    error_log("Erro ao excluir tarefa: " . $e->getMessage());
    header("Location: tarefas.php?error=" . urlencode('Ocorreu um erro ao excluir a tarefa.'));
    exit;
}
?>
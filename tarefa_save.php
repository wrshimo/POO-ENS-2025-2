<?php
require_once 'check_auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tarefas.php');
    exit;
}

$id = $_GET['id'] ?? null;
$titulo = $_POST['titulo'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$prazo = $_POST['prazo'] ?? '';
$id_funcionario = $_POST['id_funcionario'] ?? '';
$concluido = isset($_POST['concluido']) ? 1 : 0;

if (empty($titulo) || empty($prazo) || empty($id_funcionario)) {
    header("Location: tarefa_form.php?id=$id&error=Todos os campos, exceto descrição, são obrigatórios");
    exit;
}

try {
    if ($id) {
        // Atualizar tarefa existente
        $sql = "UPDATE tarefas SET titulo = ?, descricao = ?, prazo = ?, concluido = ?, id_funcionario = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $descricao, $prazo, $concluido, $id_funcionario, $id]);
    } else {
        // Inserir nova tarefa
        $sql = "INSERT INTO tarefas (titulo, descricao, prazo, concluido, id_funcionario) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $descricao, $prazo, $concluido, $id_funcionario]);
    }

    header("Location: tarefas.php");
    exit;

} catch (PDOException $e) {
    error_log("Erro ao salvar tarefa: " . $e->getMessage());
    header("Location: tarefa_form.php?id=$id&error=" . urlencode('Ocorreu um erro ao salvar a tarefa.'));
    exit;
}
?>
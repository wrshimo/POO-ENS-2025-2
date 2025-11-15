<?php
require_once 'check_auth.php';
require_once 'db.php';

$tarefa = [
    'id' => '',
    'titulo' => '',
    'descricao' => '',
    'prazo' => '',
    'concluido' => 0,
    'id_funcionario' => '',
];
$pageTitle = 'Adicionar Tarefa';
$action = 'tarefa_save.php';

// Busca a lista de funcionários para o dropdown
$stmt_funcionarios = $pdo->query("SELECT id, nome FROM funcionarios ORDER BY nome");
$funcionarios = $stmt_funcionarios->fetchAll();


// Se um ID for passado, estamos editando uma tarefa existente
if (isset($_GET['id'])) {
    $pageTitle = 'Editar Tarefa';
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM tarefas WHERE id = ?");
    $stmt->execute([$id]);
    $tarefa = $stmt->fetch();

    if (!$tarefa) {
        header("Location: tarefas.php?error=Tarefa não encontrada");
        exit;
    }
    $action = 'tarefa_save.php?id=' . $id;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-container { background-color: white; padding: 2rem; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 500px; }
        .form-container h2 { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .5rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: .5rem; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 100px; }
        .btn-save { width: 100%; padding: .7rem; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 1rem; }
        .btn-save:hover { background-color: #0056b3; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #333; }
        .checkbox-group { display: flex; align-items: center; }
        .checkbox-group input { width: auto; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><?= $pageTitle ?></h2>
        <form action="<?= $action ?>" method="POST">
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($tarefa['titulo']) ?>" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao"><?= htmlspecialchars($tarefa['descricao']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="prazo">Prazo</label>
                <input type="date" id="prazo" name="prazo" value="<?= htmlspecialchars($tarefa['prazo']) ?>" required>
            </div>
            <div class="form-group">
                <label for="id_funcionario">Funcionário Responsável</label>
                <select id="id_funcionario" name="id_funcionario" required>
                    <option value="">Selecione um funcionário</option>
                    <?php foreach ($funcionarios as $funcionario): ?>
                        <option value="<?= $funcionario['id'] ?>" <?= ($tarefa['id_funcionario'] == $funcionario['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($funcionario['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group checkbox-group">
                 <input type="checkbox" id="concluido" name="concluido" value="1" <?= $tarefa['concluido'] ? 'checked' : '' ?>>
                <label for="concluido">Marcar como Concluída</label>
            </div>
            <button type="submit" class="btn-save">Salvar</button>
        </form>
        <a href="tarefas.php" class="back-link">Voltar para a Lista</a>
    </div>
</body>
</html>

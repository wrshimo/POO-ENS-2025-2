<?php
require_once 'check_auth.php';
require_once 'db.php';

$funcionario = [
    'id' => '',
    'nome' => '',
    'login' => '',
];
$pageTitle = 'Adicionar Funcionário';
$action = 'funcionario_save.php';

// Se um ID for passado, estamos editando um funcionário existente
if (isset($_GET['id'])) {
    $pageTitle = 'Editar Funcionário';
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE id = ?");
    $stmt->execute([$id]);
    $funcionario = $stmt->fetch();

    if (!$funcionario) {
        // Se não encontrar, redireciona ou mostra erro
        header("Location: funcionarios.php?error=Funcionário não encontrado");
        exit;
    }
    $action = 'funcionario_save.php?id=' . $id;
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
        .form-container { background-color: white; padding: 2rem; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 400px; }
        .form-container h2 { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .5rem; }
        .form-group input { width: 100%; padding: .5rem; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .btn-save { width: 100%; padding: .7rem; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 1rem; }
        .btn-save:hover { background-color: #0056b3; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #333; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><?= $pageTitle ?></h2>
        <form action="<?= $action ?>" method="POST">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($funcionario['nome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" value="<?= htmlspecialchars($funcionario['login']) ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Deixe em branco para não alterar">
            </div>
            <button type="submit" class="btn-save">Salvar</button>
        </form>
        <a href="funcionarios.php" class="back-link">Voltar para a Lista</a>
    </div>
</body>
</html>

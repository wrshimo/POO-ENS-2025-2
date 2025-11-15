<?php
// Pega o nome do script atual para saber qual página está ativa
$pagina_atual = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="index.php">Painel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?= ($pagina_atual == 'index.php') ? 'active' : '' ?>">
                <a class="nav-link" href="index.php">Dashboard</a>
            </li>
            <li class="nav-item <?= ($pagina_atual == 'tarefas.php' || $pagina_atual == 'tarefa_form.php') ? 'active' : '' ?>">
                <a class="nav-link" href="tarefas.php">Tarefas</a>
            </li>
            <li class="nav-item <?= ($pagina_atual == 'funcionarios.php' || $pagina_atual == 'funcionario_form.php') ? 'active' : '' ?>">
                <a class="nav-link" href="funcionarios.php">Funcionários</a>
            </li>
        </ul>
        <ul class="navbar-nav">
            <li class="nav-item">
                <span class="navbar-text mr-3">
                    Olá, <?= htmlspecialchars($_SESSION['nome_funcionario'] ?? 'Usuário') ?>!
                </span>
            </li>
            <li class="nav-item">
                <a class="btn btn-danger" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>
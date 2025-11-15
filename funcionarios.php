<?php
require_once 'check_auth.php';
require_once 'db.php';

// --- Função Auxiliar para Ordenação ---
function criar_link_ordem($coluna, $titulo, $ordenar_por_atual, $direcao_atual) {
    $proxima_direcao = ($direcao_atual == 'ASC') ? 'DESC' : 'ASC';
    $direcao = ($ordenar_por_atual == $coluna) ? $proxima_direcao : 'ASC';
    $icone = '';
    if ($ordenar_por_atual == $coluna) {
        $icone = ($direcao_atual == 'ASC') ? ' &#x25B2;' : ' &#x25BC;';
    }
    return sprintf(
        '<a href="?ordenar_por=%s&direcao=%s">%s%s</a>',
        $coluna, $direcao, htmlspecialchars($titulo), $icone
    );
}

// --- Lógica de Ordenação ---
$colunas_permitidas = ['id', 'nome', 'login'];
$ordenar_por = isset($_GET['ordenar_por']) && in_array($_GET['ordenar_por'], $colunas_permitidas) 
    ? $_GET['ordenar_por'] 
    : 'nome';

$direcoes_permitidas = ['ASC', 'DESC'];
$direcao = isset($_GET['direcao']) && in_array(strtoupper($_GET['direcao']), $direcoes_permitidas) 
    ? strtoupper($_GET['direcao']) 
    : 'ASC';

// Mapeamento de colunas para segurança
$mapa_colunas = [
    'id' => 'id',
    'nome' => 'nome',
    'login' => 'login'
];
$coluna_sql = $mapa_colunas[$ordenar_por];

// --- Busca dos Dados Ordenados (sem paginação) ---
$sql = sprintf("
    SELECT id, nome, login 
    FROM funcionarios 
    ORDER BY %s %s, id %s
", $coluna_sql, $direcao, $direcao);

$stmt = $pdo->prepare($sql);
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Funcionários</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .actions a { margin-right: 5px; }
        .th-sortable a { color: black; text-decoration: none; }
        .th-sortable a:hover { color: #0056b3; }
    </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gerenciar Funcionários</h2>
        <a href="funcionario_form.php" class="btn btn-success">Adicionar Novo Funcionário</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th class="th-sortable"><?= criar_link_ordem('id', 'ID', $ordenar_por, $direcao) ?></th>
                    <th class="th-sortable"><?= criar_link_ordem('nome', 'Nome', $ordenar_por, $direcao) ?></th>
                    <th class="th-sortable"><?= criar_link_ordem('login', 'Login', $ordenar_por, $direcao) ?></th>
                    <th style="width: 150px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($funcionarios)):
                    echo '<tr><td colspan="4" class="text-center">Nenhum funcionário encontrado.</td></tr>';
                else:
                    foreach ($funcionarios as $funcionario):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($funcionario['id']) ?></td>
                        <td><?= htmlspecialchars($funcionario['nome']) ?></td>
                        <td><?= htmlspecialchars($funcionario['login']) ?></td>
                        <td class="actions">
                            <a href="funcionario_form.php?id=<?= $funcionario['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                            <a href="funcionario_delete.php?id=<?= $funcionario['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                endif; 
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

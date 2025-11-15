<?php
require_once 'check_auth.php';
require_once 'db.php';

// 1. GATHER AND VALIDATE ALL INPUT PARAMETERS
$current_params = [];

// -- Pagination settings
$itens_por_pagina_opcoes = [5, 10, 25, 50];
$itens_por_pagina = (isset($_GET['itens']) && in_array((int)$_GET['itens'], $itens_por_pagina_opcoes)) ? (int)$_GET['itens'] : 10;
$current_params['itens'] = $itens_por_pagina;

$pagina_atual = (isset($_GET['pagina']) && filter_var($_GET['pagina'], FILTER_VALIDATE_INT) && (int)$_GET['pagina'] > 0) ? (int)$_GET['pagina'] : 1;

// -- Sorting settings
$colunas_permitidas = ['id', 'titulo', 'prazo', 'concluido', 'nome_funcionario'];
$ordenar_por = (isset($_GET['ordenar_por']) && in_array($_GET['ordenar_por'], $colunas_permitidas)) ? $_GET['ordenar_por'] : 'prazo';
$current_params['ordenar_por'] = $ordenar_por;

$direcoes_permitidas = ['ASC', 'DESC'];
$direcao = (isset($_GET['direcao']) && in_array(strtoupper($_GET['direcao']), $direcoes_permitidas)) ? strtoupper($_GET['direcao']) : 'DESC';
$current_params['direcao'] = $direcao;

// -- Filtering settings
$id_funcionario_filtro = null;
$nome_funcionario_filtro = null;
if (isset($_GET['id_funcionario']) && filter_var($_GET['id_funcionario'], FILTER_VALIDATE_INT)) {
    $stmt_func = $pdo->prepare("SELECT nome FROM funcionarios WHERE id = ?");
    $stmt_func->execute([(int)$_GET['id_funcionario']]);
    if ($nome = $stmt_func->fetchColumn()) {
        $id_funcionario_filtro = (int)$_GET['id_funcionario'];
        $nome_funcionario_filtro = $nome;
        $current_params['id_funcionario'] = $id_funcionario_filtro;
    }
}

// 2. DATABASE QUERIES

// -- Count total items for pagination (respecting filters)
$sql_count = "SELECT COUNT(t.id) FROM tarefas t";
$where_clauses = [];
$execute_params = [];

if ($id_funcionario_filtro) {
    $where_clauses[] = "t.id_funcionario = :id_funcionario";
    $execute_params[':id_funcionario'] = $id_funcionario_filtro;
}

if (!empty($where_clauses)) {
    $sql_count .= " WHERE " . implode(' AND ', $where_clauses);
}

$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($execute_params);
$total_tarefas = $stmt_count->fetchColumn();

// -- Calculate pagination details
$total_paginas = $total_tarefas > 0 ? ceil($total_tarefas / $itens_por_pagina) : 1;
if ($pagina_atual > $total_paginas) {
    $pagina_atual = $total_paginas;
}
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// -- Fetch the actual data for the current page
$mapa_colunas = ['id' => 't.id', 'titulo' => 't.titulo', 'prazo' => 't.prazo', 'concluido' => 't.concluido', 'nome_funcionario' => 'nome_funcionario'];
$coluna_sql = $mapa_colunas[$ordenar_por];

$sql_data = "SELECT t.*, f.nome as nome_funcionario FROM tarefas t LEFT JOIN funcionarios f ON t.id_funcionario = f.id";
if (!empty($where_clauses)) {
    $sql_data .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql_data .= sprintf(" ORDER BY %s %s, t.id %s LIMIT :limit OFFSET :offset", $coluna_sql, $direcao, $direcao);

$stmt_data = $pdo->prepare($sql_data);
$stmt_data->bindValue(':limit', $itens_por_pagina, PDO::PARAM_INT);
$stmt_data->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($execute_params as $key => $val) {
    $stmt_data->bindValue($key, $val);
}
$stmt_data->execute();
$tarefas = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// 3. HELPER FUNCTION FOR SORTING LINKS
function criar_link_ordem($coluna, $titulo, $current_params) {
    $params_para_link = $current_params;
    $proxima_direcao = ($current_params['ordenar_por'] == $coluna && $current_params['direcao'] == 'ASC') ? 'DESC' : 'ASC';
    $params_para_link['ordenar_por'] = $coluna;
    $params_para_link['direcao'] = $proxima_direcao;
    unset($params_para_link['pagina']);

    $icone = ($current_params['ordenar_por'] == $coluna) ? (($current_params['direcao'] == 'ASC') ? ' &#x25B2;' : ' &#x25BC;') : '';
    return sprintf('<a href="?%s">%s%s</a>', http_build_query($params_para_link), htmlspecialchars($titulo), $icone);
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Tarefas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .actions a { margin-right: 5px; } 
        .th-sortable a { color: black; text-decoration: none; } 
        .th-sortable a:hover { color: #0056b3; }
        .tr-concluida { background-color: #d4edda !important; } /* Light green */
        .tr-vencida { background-color: #f8d7da !important; }  /* Light red */
    </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $nome_funcionario_filtro ? 'Tarefas de: <strong>' . htmlspecialchars($nome_funcionario_filtro) . '</strong>' : 'Gerenciar Tarefas' ?></h2>
        <div>
            <?php if ($id_funcionario_filtro): ?><a href="tarefas.php" class="btn btn-secondary">Limpar Filtro</a><?php endif; ?>
            <a href="tarefa_form.php" class="btn btn-success">Adicionar Nova Tarefa</a>
        </div>
    </div>

    <form method="GET" action="tarefas.php" id="pagination-form" class="form-inline mb-3">
        <?php 
        $params_para_form = $current_params;
        unset($params_para_form['itens'], $params_para_form['pagina']);
        foreach ($params_para_form as $key => $value): 
            printf('<input type="hidden" name="%s" value="%s">', htmlspecialchars($key), htmlspecialchars($value));
        endforeach; 
        ?>
        <label for="itens-select" class="mr-2">Itens por página:</label>
        <select name="itens" id="itens-select" class="form-control mr-3" onchange="document.getElementById('pagina-select').value = 1; this.form.submit();">
            <?php foreach ($itens_por_pagina_opcoes as $opcao): ?>
                <option value="<?= $opcao ?>" <?= ($opcao == $itens_por_pagina) ? 'selected' : '' ?>><?= $opcao ?></option>
            <?php endforeach; ?>
        </select>

        <label for="pagina-select" class="mr-2">Página:</label>
        <select name="pagina" id="pagina-select" class="form-control" onchange="this.form.submit();">
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <option value="<?= $i ?>" <?= ($i == $pagina_atual) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th class="th-sortable"><?= criar_link_ordem('id', 'ID', $current_params) ?></th>
                    <th class="th-sortable"><?= criar_link_ordem('titulo', 'Título', $current_params) ?></th>
                    <th class="th-sortable"><?= criar_link_ordem('prazo', 'Prazo', $current_params) ?></th>
                    <th class="th-sortable"><?= criar_link_ordem('concluido', 'Status', $current_params) ?></th>
                    <th class="th-sortable"><?= criar_link_ordem('nome_funcionario', 'Funcionário', $current_params) ?></th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tarefas)): ?>
                    <tr><td colspan="6" class="text-center">Nenhuma tarefa encontrada.</td></tr>
                <?php else: 
                    $hoje = strtotime(date('Y-m-d'));
                    foreach ($tarefas as $tarefa): 
                        $tr_class = '';
                        if ($tarefa['concluido']) {
                            $tr_class = 'tr-concluida';
                        } elseif (strtotime($tarefa['prazo']) < $hoje) {
                            $tr_class = 'tr-vencida';
                        }
                ?>
                    <tr class="<?= $tr_class ?>">
                        <td><?= htmlspecialchars($tarefa['id']) ?></td>
                        <td><?= htmlspecialchars($tarefa['titulo']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($tarefa['prazo']))) ?></td>
                        <td><span class="<?= $tarefa['concluido'] ? 'text-success' : 'text-warning' ?>"><?= $tarefa['concluido'] ? 'Concluída' : 'Pendente' ?></span></td>
                        <td><?= htmlspecialchars($tarefa['nome_funcionario'] ?? 'N/A') ?></td>
                        <td class="actions"><a href="tarefa_form.php?id=<?= $tarefa['id'] ?>" class="btn btn-sm btn-primary">Editar</a><a href="tarefa_delete.php?id=<?= $tarefa['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

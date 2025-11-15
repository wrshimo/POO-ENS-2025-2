<?php
require_once 'check_auth.php';
require_once 'db.php';

// --- Buscando Estatísticas ---

// 1. Contadores gerais
$total_tarefas = $pdo->query("SELECT COUNT(*) FROM tarefas")->fetchColumn();
$tarefas_concluidas = $pdo->query("SELECT COUNT(*) FROM tarefas WHERE concluido = 1")->fetchColumn();
$tarefas_pendentes = $total_tarefas - $tarefas_concluidas;

// 2. Tarefas com prazo próximo (próximos 7 dias)
$stmt_prazo = $pdo->prepare(
    "SELECT t.titulo, t.prazo, f.nome as funcionario_nome 
     FROM tarefas t 
     LEFT JOIN funcionarios f ON t.id_funcionario = f.id 
     WHERE t.concluido = 0 AND t.prazo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
     ORDER BY t.prazo ASC"
);
$stmt_prazo->execute();
$tarefas_proximas = $stmt_prazo->fetchAll(PDO::FETCH_ASSOC);

// 3. Resumo de tarefas por funcionário para o gráfico
$stmt_por_funcionario = $pdo->prepare(
    "SELECT 
        f.id, 
        f.nome, 
        SUM(CASE WHEN t.concluido = 1 THEN 1 ELSE 0 END) as concluidas,
        SUM(CASE WHEN t.concluido = 0 AND t.prazo >= CURDATE() THEN 1 ELSE 0 END) as pendentes,
        SUM(CASE WHEN t.concluido = 0 AND t.prazo < CURDATE() THEN 1 ELSE 0 END) as vencidas
     FROM funcionarios f
     LEFT JOIN tarefas t ON f.id = t.id_funcionario
     GROUP BY f.id, f.nome
     ORDER BY vencidas DESC, pendentes DESC, concluidas DESC"
);
$stmt_por_funcionario->execute();
$resumo_funcionarios = $stmt_por_funcionario->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para o Chart.js
$ids_funcionarios = [];
$nomes_funcionarios = [];
$tarefas_concluidas_data = [];
$tarefas_pendentes_data = [];
$tarefas_vencidas_data = [];

foreach ($resumo_funcionarios as $resumo) {
    $ids_funcionarios[] = $resumo['id'];
    $nomes_funcionarios[] = $resumo['nome'];
    $tarefas_concluidas_data[] = (int)$resumo['concluidas'];
    $tarefas_pendentes_data[] = (int)$resumo['pendentes'];
    $tarefas_vencidas_data[] = (int)$resumo['vencidas'];
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Gerenciador de Tarefas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-deck .card { min-width: 220px; }
        .card-header { font-weight: bold; }
        #graficoFuncionarios { cursor: pointer; }
    </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container mt-4">
    <h2>Dashboard</h2>
    <p>Visão geral do andamento das tarefas.</p>

    <hr>

    <h4>Resumo Geral</h4>
    <div class="card-deck mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body"><h5 class="card-title"><?= $total_tarefas ?></h5><p class="card-text">Total de Tarefas</p></div>
        </div>
        <div class="card text-white bg-success">
            <div class="card-body"><h5 class="card-title"><?= $tarefas_concluidas ?></h5><p class="card-text">Concluídas</p></div>
        </div>
        <div class="card text-white bg-warning">
            <div class="card-body"><h5 class="card-title"><?= $tarefas_pendentes ?></h5><p class="card-text">Pendentes</p></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <h4>Tarefas Vencendo em Breve (7 dias)</h4>
            <?php if (empty($tarefas_proximas)): ?>
                <div class="alert alert-info">Nenhuma tarefa vencendo nos próximos 7 dias.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($tarefas_proximas as $tarefa): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <?= htmlspecialchars($tarefa['titulo']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($tarefa['funcionario_nome']) ?></small>
                            </div>
                            <span class="badge badge-danger">Vence em: <?= date("d/m/Y", strtotime($tarefa['prazo'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="col-md-6 mb-4">
            <h4>Distribuição de Tarefas por Funcionário</h4>
            <canvas id="graficoFuncionarios"></canvas>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const funcionarioIds = <?= json_encode($ids_funcionarios) ?>;
    const ctx = document.getElementById('graficoFuncionarios').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($nomes_funcionarios) ?>,
            datasets: [
            {
                label: 'Concluídas',
                data: <?= json_encode($tarefas_concluidas_data) ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.7)', // Verde
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }, {
                label: 'Pendentes',
                data: <?= json_encode($tarefas_pendentes_data) ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.7)', // Amarelo
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }, {
                label: 'Vencidas',
                data: <?= json_encode($tarefas_vencidas_data) ?>,
                backgroundColor: 'rgba(220, 53, 69, 0.7)', // Vermelho
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const funcionarioId = funcionarioIds[index];
                    if (funcionarioId) {
                        window.location.href = 'tarefas.php?id_funcionario=' + funcionarioId;
                    }
                }
            },
            plugins: {
                legend: { position: 'top', },
                tooltip: { mode: 'index', intersect: false, }
            },
            scales: {
                x: { stacked: true, },
                y: { stacked: true, beginAtZero: true, ticks: { callback: function(value) { if (Number.isInteger(value)) { return value; } } } }
            }
        }
    });
});
</script>

</body>
</html>

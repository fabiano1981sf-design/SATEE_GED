<?php
/**
 * ARQUIVO: index.php
 * DESCRIÇÃO: Dashboard Principal - Visão geral e estatísticas do sistema (com Gráficos Pizza).
 */
require_once 'auth.php'; 
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; 

// =========================================================================
// LÓGICA DE BUSCA DE DADOS GERAIS DO SISTEMA (MANTIDA)
// =========================================================================
$data_hoje_db = date('Y-m-d'); 
$estatisticas = [
    'total' => 0,
    'concluido' => 0,
    'pendente' => 0,
    'em_andamento' => 0,
    'atrasado' => 0, 
];

try {
    // Cálculo das contagens brutas (total, concluido, pendente, em_andamento)
    $statuses = ['concluido', 'pendente', 'em_andamento'];
    foreach ($statuses as $s) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE status_tarefa = :status");
        $stmt->bindParam(':status', $s);
        $stmt->execute();
        $estatisticas[$s] = $stmt->fetchColumn();
        $estatisticas['total'] += $estatisticas[$s];
    }
    
    // Cálculo das tarefas ATRASADAS (Com a correção de data)
    $sql_atraso = "SELECT COUNT(*) FROM tarefas 
                   WHERE status_tarefa IN ('pendente', 'em_andamento') AND prazo < :data_hoje";
    $stmt_atraso = $pdo->prepare($sql_atraso);
    $stmt_atraso->bindParam(':data_hoje', $data_hoje_db);
    $stmt_atraso->execute();
    $estatisticas['atrasado'] = $stmt_atraso->fetchColumn();
    
    // Cálculos para ajuste de porcentagem (evitar dupla contagem)
    $sql_atraso_pendente = "SELECT COUNT(*) FROM tarefas WHERE status_tarefa = 'pendente' AND prazo < :data_hoje";
    $stmt_ap = $pdo->prepare($sql_atraso_pendente);
    $stmt_ap->bindParam(':data_hoje', $data_hoje_db);
    $stmt_ap->execute();
    $atrasado_pendente_geral = $stmt_ap->fetchColumn();

    $sql_atraso_andamento = "SELECT COUNT(*) FROM tarefas WHERE status_tarefa = 'em_andamento' AND prazo < :data_hoje";
    $stmt_aa = $pdo->prepare($sql_atraso_andamento);
    $stmt_aa->bindParam(':data_hoje', $data_hoje_db);
    $stmt_aa->execute();
    $atrasado_em_andamento_geral = $stmt_aa->fetchColumn();

    // Contagens ajustadas (apenas tarefas DENTRO do prazo)
    $contagem_pendente_no_prazo_geral = $estatisticas['pendente'] - $atrasado_pendente_geral;
    $contagem_andamento_no_prazo_geral = $estatisticas['em_andamento'] - $atrasado_em_andamento_geral;

    // Cálculo das porcentagens
    $pc_concluido = 0;
    $pc_andamento_no_prazo = 0;
    $pc_pendente_no_prazo = 0;
    $pc_atrasado = 0;
    $pc_ativos_no_prazo = 0; // Novo Total Ativos

    if ($estatisticas['total'] > 0) {
        $total = $estatisticas['total'];

        $pc_concluido = round(($estatisticas['concluido'] / $total) * 100, 1);
        $pc_andamento_no_prazo = round(($contagem_andamento_no_prazo_geral / $total) * 100, 1);
        $pc_pendente_no_prazo = round(($contagem_pendente_no_prazo_geral / $total) * 100, 1);
        $pc_atrasado = round(($estatisticas['atrasado'] / $total) * 100, 1);
        
        $pc_ativos_no_prazo = $pc_andamento_no_prazo + $pc_pendente_no_prazo;
    }
    
    // LÓGICA: VISÃO RÁPIDA DA EQUIPE (MANTIDA)
    $sql_equipe_snapshot = "
        SELECT 
            u.id, u.nome, 
            COUNT(t.id) AS total_tarefas,
            SUM(CASE WHEN t.status_tarefa = 'concluido' THEN 1 ELSE 0 END) AS tarefas_concluidas
        FROM usuarios u
        LEFT JOIN tarefas t ON u.id = t.responsavel_id
        GROUP BY u.id, u.nome
        ORDER BY tarefas_concluidas DESC
        LIMIT 5";
        
    $stmt_equipe = $pdo->query($sql_equipe_snapshot);
    $snapshot_equipe = $stmt_equipe->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Erro no Dashboard Principal: " . $e->getMessage());
}
// =========================================================================
?>

<div id="main-content">
    <h1>Dashboard Principal 🚀</h1>
    <p class="text-muted">Visão consolidada da performance do sistema.</p>


    <h3 class="fw-bold text-primary mb-3">1. Resumo de Status Geral</h3>
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">TOTAL GERAL</div>
                    <div class="h3 mb-0"><?= $estatisticas['total'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">CONCLUÍDAS</div>
                    <div class="h3 mb-0"><?= $estatisticas['concluido'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">ATIVAS (NO PRAZO)</div>
                    <div class="h3 mb-0"><?= $contagem_pendente_no_prazo_geral + $contagem_andamento_no_prazo_geral ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">ATRASADAS</div>
                    <div class="h3 mb-0"><?= $estatisticas['atrasado'] ?></div>
                </div>
            </div>
        </div>
    </div>


    <h3 class="fw-bold text-primary mb-3">2. Gráficos de Status Consolidados</h3>
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header fw-bold">Status Geral do Projeto</div>
                <div class="card-body">
                    <canvas id="chartStatusGeral" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header fw-bold">Taxa de Conclusão Global</div>
                <div class="card-body">
                    <canvas id="chartDesempenho" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

        
    <h3 class="fw-bold text-primary mb-3">3. Visão Rápida da Equipe</h3>
    <div class="card shadow mb-4">
        <div class="card-header fw-bold">Top 5 Membros (Mais Ativos)</div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Membro</th>
                        <th>Total de Tarefas</th>
                        <th>Concluídas</th>
                        <th>% Concluído</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($snapshot_equipe) > 0): ?>
                        <?php foreach ($snapshot_equipe as $m): ?>
                            <?php 
                                $percentual = ($m['total_tarefas'] > 0) ? round(($m['tarefas_concluidas'] / $m['total_tarefas']) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($m['nome']) ?></td>
                                <td><?= $m['total_tarefas'] ?></td>
                                <td><?= $m['tarefas_concluidas'] ?></td>
                                <td>
                                    <span class="badge bg-<?= ($percentual >= 80) ? 'success' : (($percentual >= 50) ? 'warning' : 'danger') ?>">
                                        <?= $percentual ?>%
                                    </span>
                                </td>
                                <td>
                                    <a href="dashboard_membro.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-info">Ver Dashboard</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Nenhum membro ou tarefa encontrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // =========================================================================
    // DADOS CALCULADOS NO PHP
    // =========================================================================
    const pcConcluido = <?= $pc_concluido ?>;
    const pcAtivosNoPrazo = <?= $pc_ativos_no_prazo ?>;
    const pcAtrasado = <?= $pc_atrasado ?>;
    const pcNaoConcluido = 100 - pcConcluido; // Para o Gráfico 2

    // =========================================================================
    // GRÁFICO 1: STATUS GERAL DO PROJETO (Pizza)
    // =========================================================================
    const ctx1 = document.getElementById('chartStatusGeral');

    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Concluído (' + pcConcluido + '%)', 'Ativo (No Prazo) (' + pcAtivosNoPrazo + '%)', 'Atrasado (' + pcAtrasado + '%)'],
            datasets: [{
                data: [pcConcluido, pcAtivosNoPrazo, pcAtrasado],
                backgroundColor: [
                    '#198754', // Sucesso (Verde)
                    '#0dcaf0', // Info (Ciano)
                    '#dc3545'  // Perigo (Vermelho)
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' },
                title: { display: true, text: 'Distribuição Total de Tarefas' }
            }
        }
    });

    // =========================================================================
    // GRÁFICO 2: TAXA DE CONCLUSÃO GLOBAL (Donut)
    // =========================================================================
    const ctx2 = document.getElementById('chartDesempenho');
    
    new Chart(ctx2, {
        type: 'doughnut', // Donut Chart
        data: {
            labels: ['Concluído (' + pcConcluido + '%)', 'Restante (Ativas/Atrasadas) (' + pcNaoConcluido + '%)'],
            datasets: [{
                data: [pcConcluido, pcNaoConcluido],
                backgroundColor: [
                    '#198754', // Concluído (Verde)
                    '#ffc107'   // Restante (Amarelo)
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' },
                title: { display: true, text: 'Taxa de Conclusão (Total)' }
            }
        }
    });
});
</script>
</body>
</html>
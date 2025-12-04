<?php
/**
 * ARQUIVO: relatorios.php
 * DESCRIÇÃO: Relatórios e Análises agregadas de todas as tarefas do sistema (v5 Títulos Formatados).
 */
require_once 'auth.php';
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; 

$status = $_GET['status'] ?? '';
$mensagem = $_GET['msg'] ?? '';

// --- Função para Exibir Alerta ---
if (!function_exists('display_alert')) {
    function display_alert($status, $msg) {
        if (!empty($status) && !empty($msg)) {
            $class = $status === 'sucesso' ? 'alert-success' : 'alert-danger';
            echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($msg);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }
    }
}

// =========================================================================
// LÓGICA DE BUSCA DE DADOS GERAIS DO SISTEMA
// =========================================================================
$data_hoje_db = date('Y-m-d'); 
$estatisticas = [
    'total' => 0,
    'concluido' => 0,
    'pendente' => 0,
    'em_andamento' => 0,
    'atrasado' => 0, 
];
$atrasado_pendente_geral = 0; 
$atrasado_em_andamento_geral = 0;

try {
    // 1. Calcula as contagens brutas por status (Sistema Geral)
    $statuses = ['concluido', 'pendente', 'em_andamento'];
    foreach ($statuses as $s) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE status_tarefa = :status");
        $stmt->bindParam(':status', $s);
        $stmt->execute();
        $estatisticas[$s] = $stmt->fetchColumn();
        $estatisticas['total'] += $estatisticas[$s];
    }
    
    // 2. Calcula as tarefas ATRASADAS (Geral)
    $sql_atraso = "SELECT COUNT(*) FROM tarefas 
                   WHERE status_tarefa IN ('pendente', 'em_andamento') 
                   AND prazo < :data_hoje";
    $stmt_atraso = $pdo->prepare($sql_atraso);
    $stmt_atraso->bindParam(':data_hoje', $data_hoje_db);
    $stmt_atraso->execute();
    $estatisticas['atrasado'] = $stmt_atraso->fetchColumn();
    
    // Atrasadas que vieram de "Pendente" (Para ajuste de porcentagem geral)
    $sql_atraso_pendente = "SELECT COUNT(*) FROM tarefas 
                           WHERE status_tarefa = 'pendente' 
                           AND prazo < :data_hoje";
    $stmt_ap = $pdo->prepare($sql_atraso_pendente);
    $stmt_ap->bindParam(':data_hoje', $data_hoje_db);
    $stmt_ap->execute();
    $atrasado_pendente_geral = $stmt_ap->fetchColumn();

    // Atrasadas que vieram de "Em Andamento" (Para ajuste de porcentagem geral)
    $sql_atraso_andamento = "SELECT COUNT(*) FROM tarefas 
                           WHERE status_tarefa = 'em_andamento' 
                           AND prazo < :data_hoje";
    $stmt_aa = $pdo->prepare($sql_atraso_andamento);
    $stmt_aa->bindParam(':data_hoje', $data_hoje_db);
    $stmt_aa->execute();
    $atrasado_em_andamento_geral = $stmt_aa->fetchColumn();

    // 3. Calcula as contagens ajustadas (Geral)
    $contagem_pendente_no_prazo_geral = $estatisticas['pendente'] - $atrasado_pendente_geral;
    $contagem_andamento_no_prazo_geral = $estatisticas['em_andamento'] - $atrasado_em_andamento_geral;

    // 4. Calcula as porcentagens (Geral)
    $pc_concluido = 0;
    $pc_andamento_no_prazo = 0;
    $pc_pendente_no_prazo = 0;
    $pc_atrasado = 0;
    
    if ($estatisticas['total'] > 0) {
        $total = $estatisticas['total'];

        $pc_concluido = round(($estatisticas['concluido'] / $total) * 100, 0);
        $pc_andamento_no_prazo = round(($contagem_andamento_no_prazo_geral / $total) * 100, 0);
        $pc_pendente_no_prazo = round(($contagem_pendente_no_prazo_geral / $total) * 100, 0);
        $pc_atrasado = round(($estatisticas['atrasado'] / $total) * 100, 0);
    }

    // =========================================================================
    // LÓGICA: DADOS PARA GRÁFICO DE STATUS POR USUÁRIO (SEÇÃO DE GRÁFICO)
    // =========================================================================
    $dados_grafico = [
        'labels' => [], 
        'series' => [ 
            'Concluído' => [],
            'Atrasado' => [],
            'Pendente (No Prazo)' => [],
            'Em Andamento (No Prazo)' => [],
        ]
    ];

    $stmt_membros = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC");
    $membros = $stmt_membros->fetchAll(PDO::FETCH_ASSOC);

    foreach ($membros as $membro) {
        $membro_id = $membro['id'];
        $dados_grafico['labels'][] = htmlspecialchars($membro['nome']);

        $stats = ['total' => 0, 'concluido' => 0, 'pendente' => 0, 'em_andamento' => 0];
        foreach (['concluido', 'pendente', 'em_andamento'] as $s) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :id AND status_tarefa = :status");
            $stmt->bindParam(':id', $membro_id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $s);
            $stmt->execute();
            $stats[$s] = $stmt->fetchColumn();
            $stats['total'] += $stats[$s];
        }

        if ($stats['total'] === 0) {
            $dados_grafico['series']['Concluído'][] = 0;
            $dados_grafico['series']['Atrasado'][] = 0;
            $dados_grafico['series']['Pendente (No Prazo)'][] = 0;
            $dados_grafico['series']['Em Andamento (No Prazo)'][] = 0;
            continue;
        }

        $sql_atraso = "SELECT COUNT(*) FROM tarefas 
                       WHERE responsavel_id = :id AND status_tarefa IN ('pendente', 'em_andamento') AND prazo < :data_hoje";
        $stmt_atraso = $pdo->prepare($sql_atraso);
        $stmt_atraso->bindParam(':id', $membro_id, PDO::PARAM_INT);
        $stmt_atraso->bindParam(':data_hoje', $data_hoje_db);
        $stmt_atraso->execute();
        $atrasado = $stmt_atraso->fetchColumn();

        $sql_atraso_pendente = "SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :id AND status_tarefa = 'pendente' AND prazo < :data_hoje";
        $stmt_ap = $pdo->prepare($sql_atraso_pendente);
        $stmt_ap->bindParam(':id', $membro_id, PDO::PARAM_INT);
        $stmt_ap->bindParam(':data_hoje', $data_hoje_db);
        $stmt_ap->execute();
        $atrasado_pendente_user = $stmt_ap->fetchColumn();

        $sql_atraso_andamento = "SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :id AND status_tarefa = 'em_andamento' AND prazo < :data_hoje";
        $stmt_aa = $pdo->prepare($sql_atraso_andamento);
        $stmt_aa->bindParam(':id', $membro_id, PDO::PARAM_INT);
        $stmt_aa->bindParam(':data_hoje', $data_hoje_db);
        $stmt_aa->execute();
        $atrasado_em_andamento_user = $stmt_aa->fetchColumn();

        $contagem_pendente_no_prazo_user = $stats['pendente'] - $atrasado_pendente_user;
        $contagem_andamento_no_prazo_user = $stats['em_andamento'] - $atrasado_em_andamento_user;

        $total = $stats['total'];
        $dados_grafico['series']['Concluído'][] = round(($stats['concluido'] / $total) * 100, 1);
        $dados_grafico['series']['Atrasado'][] = round(($atrasado / $total) * 100, 1);
        $dados_grafico['series']['Pendente (No Prazo)'][] = round(($contagem_pendente_no_prazo_user / $total) * 100, 1);
        $dados_grafico['series']['Em Andamento (No Prazo)'][] = round(($contagem_andamento_no_prazo_user / $total) * 100, 1);
    }


} catch (Exception $e) {
    error_log("Erro no Relatórios: " . $e->getMessage());
    $status = 'erro';
    $mensagem = 'Ocorreu um erro ao carregar as estatísticas: ' . $e->getMessage();
}
// =========================================================================
?>

<div id="main-content">
    <?php display_alert($status, $mensagem); ?>
    
    <h1>Relatórios e Análises Gerais 📊</h1>
    <p class="text-muted">Visão geral da performance de todas as tarefas do sistema.</p>


    <h3 class="fw-bold text-primary mb-3">1. Resumo de Status</h3>
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">TOTAL GERAL</div>
                    <div class="h4 mb-0"><?= $estatisticas['total'] ?> Tarefas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">CONCLUÍDAS</div>
                    <div class="h4 mb-0"><?= $estatisticas['concluido'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">DENTRO DO PRAZO</div>
                    <div class="h4 mb-0"><?= $contagem_pendente_no_prazo_geral + $contagem_andamento_no_prazo_geral ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger shadow-sm">
                <div class="card-body">
                    <div class="text-uppercase fw-bold">ATRASADAS</div>
                    <div class="h4 mb-0"><?= $estatisticas['atrasado'] ?></div>
                </div>
            </div>
        </div>
    </div>


    <h3 class="fw-bold text-primary mb-3">2. Distribuição por Status (%)</h3>
    <div class="card shadow mb-5">
        <div class="card-header fw-bold">Percentuais Ajustados</div>
        <div class="card-body">
            
            <p><strong>Concluídas (<?= $pc_concluido ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_concluido ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success" style="width: <?= $pc_concluido ?>%"></div>
            </div>

            <p><strong>Em Andamento (No Prazo) (<?= $pc_andamento_no_prazo ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_andamento_no_prazo ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-warning" style="width: <?= $pc_andamento_no_prazo ?>%"></div>
            </div>
            
            <p><strong>Pendentes (No Prazo) (<?= $pc_pendente_no_prazo ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_pendente_no_prazo ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-info" style="width: <?= $pc_pendente_no_prazo ?>%"></div>
            </div>

            <p><strong>Atrasadas (<?= $pc_atrasado ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_atrasado ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-danger" style="width: <?= $pc_atrasado ?>%"></div>
            </div>
            
        </div>
    </div>


    <h3 class="fw-bold text-primary mb-3">3. Gráfico de Status de Tarefas por Usuário (%)</h3>
    <div class="card shadow mb-4">
        <div class="card-header fw-bold">Comparativo de Performance Individual</div>
        <div class="card-body">
            <p class="text-muted">Este gráfico compara a distribuição de status de tarefas (Concluído, Atrasado, etc.) para cada membro da equipe.</p>
            
            <canvas id="chartStatusPorUsuario"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartStatusPorUsuario');
    
    // Dados preparados em PHP
    const dadosGrafico = <?= json_encode($dados_grafico, JSON_UNESCAPED_UNICODE) ?>;
    
    // Configuração das Cores
    const cores = {
        'Concluído': '#198754', // Sucesso (Verde)
        'Atrasado': '#dc3545', // Perigo (Vermelho)
        'Pendente (No Prazo)': '#0dcaf0', // Info (Ciano/Azul Claro)
        'Em Andamento (No Prazo)': '#ffc107' // Aviso (Amarelo)
    };

    const datasets = [];
    
    // Mapeia os dados PHP para o formato Chart.js
    Object.keys(dadosGrafico.series).forEach(label => {
        datasets.push({
            label: label,
            data: dadosGrafico.series[label],
            backgroundColor: cores[label],
        });
    });

    if (dadosGrafico.labels.length > 0) {
        new Chart(ctx, {
            type: 'bar', // Gráfico de barras
            data: {
                labels: dadosGrafico.labels, // Nomes dos usuários
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true, // Barras empilhadas
                        title: { display: true, text: 'Membro da Equipe' }
                    },
                    y: {
                        stacked: true, // Barras empilhadas
                        min: 0,
                        max: 100, // Eixo Y vai até 100%
                        title: { display: true, text: 'Porcentagem (%)' }
                    }
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    title: {
                        display: true,
                        text: 'Distribuição Percentual de Status de Tarefas por Usuário'
                    }
                }
            }
        });
    } else {
         // Exibe mensagem caso não haja usuários ou dados
         ctx.parentElement.innerHTML = '<p class="text-center text-muted">Nenhum dado de usuário disponível para o gráfico.</p>';
    }
});
</script>

</body>
</html>
<?php
/**
 * ARQUIVO: dashboard_membro.php
 * DESCRIÇÃO: Dashboard individual de desempenho e estatísticas de tarefas do membro.
 */
require_once 'auth.php';
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; 

// Verifica se o ID do membro foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: equipe.php?status=erro&msg=" . urlencode("ID do membro inválido ou não fornecido."));
    exit();
}

$membro_id = (int)$_GET['id'];
$status = $_GET['status'] ?? '';
$mensagem = $_GET['msg'] ?? '';

// Função para exibir alertas (reutilizada de outros arquivos)
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
// LÓGICA DE BUSCA DE DADOS DO MEMBRO E ESTATÍSTICAS
// =========================================================================
$membro = null;
$data_hoje_db = date('Y-m-d'); // Data de hoje para comparação de prazo
$estatisticas = [
    'total' => 0,
    'concluido' => 0,
    'pendente' => 0,
    'em_andamento' => 0,
    'atrasado' => 0, 
];
$atrasado_pendente = 0;
$atrasado_em_andamento = 0;


try {
    // 1. Busca os dados básicos do membro
    $stmt_membro = $pdo->prepare("SELECT nome, foto_perfil_url FROM usuarios WHERE id = :id");
    $stmt_membro->bindParam(':id', $membro_id, PDO::PARAM_INT);
    $stmt_membro->execute();
    $membro = $stmt_membro->fetch(PDO::FETCH_ASSOC);

    if (!$membro) {
        throw new Exception("Membro não encontrado.");
    }

    // 2. Calcula as contagens brutas por status
    $statuses = ['concluido', 'pendente', 'em_andamento'];
    foreach ($statuses as $s) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :id AND status_tarefa = :status");
        $stmt->bindParam(':id', $membro_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $s);
        $stmt->execute();
        $estatisticas[$s] = $stmt->fetchColumn();
        $estatisticas['total'] += $estatisticas[$s];
    }
    
    // 3. Calcula as tarefas ATRASADAS totais e por sub-categoria
    
    // Total de Atrasadas (usado no card e na porcentagem geral de atraso)
    $sql_atraso = "SELECT COUNT(*) FROM tarefas 
                   WHERE responsavel_id = :id 
                   AND status_tarefa IN ('pendente', 'em_andamento') 
                   AND prazo < :data_hoje";
    $stmt_atraso = $pdo->prepare($sql_atraso);
    $stmt_atraso->bindParam(':id', $membro_id, PDO::PARAM_INT);
    $stmt_atraso->bindParam(':data_hoje', $data_hoje_db);
    $stmt_atraso->execute();
    $estatisticas['atrasado'] = $stmt_atraso->fetchColumn();
    
    // Atrasadas que vieram de "Pendente"
    $sql_atraso_pendente = "SELECT COUNT(*) FROM tarefas 
                           WHERE responsavel_id = :id 
                           AND status_tarefa = 'pendente' 
                           AND prazo < :data_hoje";
    $stmt_atraso_pendente = $pdo->prepare($sql_atraso_pendente);
    $stmt_atraso_pendente->bindParam(':id', $membro_id, PDO::PARAM_INT);
    $stmt_atraso_pendente->bindParam(':data_hoje', $data_hoje_db);
    $stmt_atraso_pendente->execute();
    $atrasado_pendente = $stmt_atraso_pendente->fetchColumn();

    // Atrasadas que vieram de "Em Andamento"
    $sql_atraso_andamento = "SELECT COUNT(*) FROM tarefas 
                           WHERE responsavel_id = :id 
                           AND status_tarefa = 'em_andamento' 
                           AND prazo < :data_hoje";
    $stmt_atraso_andamento = $pdo->prepare($sql_atraso_andamento);
    $stmt_atraso_andamento->bindParam(':id', $membro_id, PDO::PARAM_INT);
    $stmt_atraso_andamento->bindParam(':data_hoje', $data_hoje_db);
    $stmt_atraso_andamento->execute();
    $atrasado_em_andamento = $stmt_atraso_andamento->fetchColumn();

    // 4. Calcula as contagens ajustadas (apenas tarefas dentro do prazo)
    $contagem_pendente_no_prazo = $estatisticas['pendente'] - $atrasado_pendente;
    $contagem_andamento_no_prazo = $estatisticas['em_andamento'] - $atrasado_em_andamento;

    // 5. Calcula as porcentagens
    $pc_concluido = 0;
    $pc_andamento_no_prazo = 0;
    $pc_pendente_no_prazo = 0;
    $pc_atrasado = 0;
    
    if ($estatisticas['total'] > 0) {
        $total = $estatisticas['total'];

        $pc_concluido = round(($estatisticas['concluido'] / $total) * 100, 0);
        $pc_andamento_no_prazo = round(($contagem_andamento_no_prazo / $total) * 100, 0);
        $pc_pendente_no_prazo = round(($contagem_pendente_no_prazo / $total) * 100, 0);
        $pc_atrasado = round(($estatisticas['atrasado'] / $total) * 100, 0);
    }


} catch (Exception $e) {
    error_log("Erro no dashboard do membro: " . $e->getMessage());
    $status = 'erro';
    $mensagem = 'Ocorreu um erro ao carregar as estatísticas: ' . $e->getMessage();
}
// =========================================================================
?>

<div id="main-content">
    <?php display_alert($status, $mensagem); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <img src="<?= htmlspecialchars($membro['foto_perfil_url'] ?? 'https://via.placeholder.com/50/ADB5BD/FFFFFF?text=U') ?>" 
                 alt="Foto de Perfil" 
                 class="rounded-circle me-2" 
                 style="width: 40px; height: 40px; object-fit: cover;">
            Dashboard de Tarefas: <?= htmlspecialchars($membro['nome'] ?? 'Membro Desconhecido') ?>
        </h2>
        <a href="equipe.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar à Equipe
        </a>
    </div>

    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold">TOTAL DE TAREFAS</div>
                            <div class="h3 mb-0"><?= $estatisticas['total'] ?></div>
                        </div>
                        <i class="bi bi-list-task h1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold">CONCLUÍDAS</div>
                            <div class="h3 mb-0"><?= $estatisticas['concluido'] ?></div>
                        </div>
                        <i class="bi bi-check-circle-fill h1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold">EM ANDAMENTO</div>
                            <div class="h3 mb-0"><?= $estatisticas['em_andamento'] ?></div>
                        </div>
                        <i class="bi bi-gear-fill h1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold">ATRASADAS</div>
                            <div class="h3 mb-0"><?= $estatisticas['atrasado'] ?></div>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill h1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header fw-bold">Distribuição de Status (%)</div>
        <div class="card-body">
            
            <p><strong>Concluídas (<?= $pc_concluido ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_concluido ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success" style="width: <?= $pc_concluido ?>%"></div>
            </div>

            <p><strong>Em Andamento (<?= $pc_andamento_no_prazo ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_andamento_no_prazo ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-warning" style="width: <?= $pc_andamento_no_prazo ?>%"></div>
            </div>
            
            <p><strong>Pendentes (<?= $pc_pendente_no_prazo ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_pendente_no_prazo ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-info" style="width: <?= $pc_pendente_no_prazo ?>%"></div>
            </div>

            <p><strong>Atrasadas (<?= $pc_atrasado ?>%)</strong></p>
            <div class="progress mb-3" role="progressbar" aria-valuenow="<?= $pc_atrasado ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-danger" style="width: <?= $pc_atrasado ?>%"></div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
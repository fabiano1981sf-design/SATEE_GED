<?php 
/**
 * ARQUIVO: equipe.php
 * DESCRIÇÃO: Dashboard de performance dos membros (usuários do sistema).
 */
require_once 'auth.php';
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; 

// Verifica permissão de Admin antes de qualquer output
if ($_SESSION['usuario_perfil'] !== 'admin') {
    header("Location: index.php?status=erro&msg=" . urlencode("Acesso negado. Apenas administradores."));
    exit();
}

$status = $_GET['status'] ?? '';
$mensagem = $_GET['msg'] ?? '';

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
// LÓGICA DE BUSCA DE TODOS OS USUÁRIOS (MEMBROS)
// =========================================================================
$membros = []; 
try {
    // Busca id, nome, perfil E A URL DA FOTO
    $sql = "SELECT id, nome, perfil, foto_perfil_url FROM usuarios ORDER BY nome ASC";
    $stmt = $pdo->query($sql);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Erro ao buscar membros da equipe: " . $e->getMessage());
    $status = 'erro';
    $mensagem = 'Erro ao carregar lista de membros. Por favor, tente novamente.';
}
// =========================================================================
?>

<div id="main-content">
    <?php display_alert($status, $mensagem); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard de Performance da Equipe <i class="bi bi-people"></i></h1>
        <div class="d-flex">
            
            
        </div>
    </div>

    <div class="row">
        <?php if (empty($membros)): ?>
            <div class="col-12"><p class="text-center text-muted">Nenhum usuário encontrado.</p></div>
        <?php else: ?>
            <?php foreach ($membros as $membro): 
                $membro_id = $membro['id'];
                
				// --- CÁLCULO DE MÉTRICAS INDIVIDUAIS ---
				
// 1. Total de Tarefas Atribuídas
                try {
                    $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :id");
                    $stmt_total->bindParam(':id', $membro_id, PDO::PARAM_INT);
                    $stmt_total->execute();
                    $total_tarefas = $stmt_total->fetchColumn();
                } catch (\PDOException $e) { $total_tarefas = 0; }

                // 2. Total de Tarefas Concluídas
                try {
                    $stmt_concluidas = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :id AND status_tarefa = 'concluido'");
                    $stmt_concluidas->bindParam(':id', $membro_id, PDO::PARAM_INT);
                    $stmt_concluidas->execute();
                    $concluidas = $stmt_concluidas->fetchColumn();
                } catch (\PDOException $e) { $concluidas = 0; }
                
                // 3. Porcentagem de Conclusão
                $percentual = $total_tarefas > 0 ? round(($concluidas / $total_tarefas) * 100, 0) : 0;
                
                // Classe do badge
                $badge_class = $membro['perfil'] === 'admin' ? 'bg-danger' : 'bg-success';
                $progress_class = $percentual == 100 ? 'bg-success' : 'bg-warning';
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($membro['foto_perfil_url'] ?? 'https://via.placeholder.com/50/ADB5BD/FFFFFF?text=U') ?>" 
                                         alt="Foto de <?= htmlspecialchars($membro['nome']) ?>" 
                                         class="rounded-circle me-2" 
                                         style="width: 30px; height: 30px; object-fit: cover;">
                                    <?= htmlspecialchars($membro['nome']) ?>
                                </h5>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($membro['perfil'])) ?></span>
                            </div>
                            <small class="text-muted">ID: <?= $membro_id ?></small>
                            <hr>

                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h6 class="text-muted">TOTAL</h6>
                                    <h3 class="fw-bold text-primary"><?= $total_tarefas ?></h3>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted">CONCLUÍDAS</h6>
                                    <h3 class="fw-bold text-success"><?= $concluidas ?></h3>
                                </div>
                            </div>

                            <h6 class="text-muted">Progresso de Conclusão</h6>
                            <div class="progress" role="progressbar" aria-valuenow="<?= $percentual ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar <?= $progress_class ?>" style="width: <?= $percentual ?>%">
                                    <?= $percentual ?>%
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end bg-light">
                            <a href="dashboard_membro.php?id=<?= $membro_id ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
/**
 * ARQUIVO: listagem_registros.php
 * DESCRIÇÃO: Listagem de todas as tarefas cadastradas.
 */
require_once 'auth.php';
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php';

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
// LÓGICA DE BUSCA DE TAREFAS CORRIGIDA
// (JOIN com a tabela usuarios para obter o NOME do responsável)
// =========================================================================
$tarefas = []; 
try {
    $sql = "
        SELECT 
            t.id, 
            t.titulo, 
            t.prazo, 
            t.status_tarefa, 
            u.nome AS nome_responsavel 
        FROM tarefas t
        LEFT JOIN usuarios u ON t.responsavel_id = u.id
        ORDER BY t.prazo DESC";
        
    $stmt = $pdo->query($sql); 
    $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Erro ao buscar tarefas: " . $e->getMessage());
    $status = 'erro';
    $mensagem = 'Erro ao carregar lista de tarefas. Por favor, tente novamente.';
}
// =========================================================================
?>

<div id="main-content">
    <?php display_alert($status, $mensagem); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Visão Geral das Tarefas <i class="bi bi-list-columns-reverse"></i></h1>
        <div class="d-flex">
            

            
            <a href="form_tarefa.php" class="btn btn-success shadow-sm"><i class="bi bi-plus-circle me-1"></i> Nova Tarefa</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Prazo</th>
                            <th>Status</th>
                            <th>Responsável</th>
                            <th style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tarefas)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Nenhuma tarefa encontrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tarefas as $tarefa): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tarefa['id']) ?></td>
                                    <td><?= htmlspecialchars($tarefa['titulo']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($tarefa['prazo']))) ?></td>
                                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $tarefa['status_tarefa']))) ?></td>
                                    <td><?= htmlspecialchars($tarefa['nome_responsavel'] ?? 'N/A') ?></td>
                                    <td>
                                        <a href="form_tarefa.php?id=<?= $tarefa['id'] ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-pencil"></i></a>
                                        <a href="excluir_tarefa.php?id=<?= $tarefa['id'] ?>" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
/**
 * ARQUIVO: form_tarefa.php
 * DESCRIÇÃO: Formulário para criar nova tarefa ou editar tarefa existente.
 */
require_once 'auth.php';
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; 

// --- 1. Lógica de Carregamento de Dados (Edição) ---
$tarefa = [
    'id' => null,
    'titulo' => '',
    'descricao' => '',
    'prazo' => '', 
    'responsavel_id' => '',
    'status_tarefa' => 'pendente'
];
$titulo_pagina = "Nova Tarefa";
$data_hoje = date('Y-m-d');
$is_atrasado = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT id, titulo, descricao, prazo, responsavel_id, status_tarefa FROM tarefas WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            $tarefa = $resultado;
            $titulo_pagina = "Editar Tarefa #" . $id;

            // --- CÁLCULO DINÂMICO DO STATUS ATRASADO (NOVA LÓGICA) ---
            // A tarefa está atrasada se: 
            // 1. Não estiver concluída.
            // 2. O prazo estiver no passado.
            if ($tarefa['status_tarefa'] !== 'concluido' && !empty($tarefa['prazo']) && $tarefa['prazo'] < $data_hoje) {
                $is_atrasado = true;
            }
            // -----------------------------------------------------------
            
        } else {
            header('Location: form_tarefa.php?status=erro&msg=' . urlencode('Tarefa não encontrada.'));
            exit;
        }
    } catch (\PDOException $e) {
        error_log("Erro ao carregar tarefa: " . $e->getMessage());
    }
}

// --- 2. Busca de Responsáveis (Usuários) ---
$responsaveis = [];
try {
    $stmt_resp = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC");
    $responsaveis = $stmt_resp->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Erro ao buscar responsáveis: " . $e->getMessage());
}

// --- 3. Função para Exibir Alerta (Mensagens de Sucesso/Erro) ---
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
?>

<div id="main-content">
    <?php display_alert($status, $mensagem); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= $titulo_pagina ?> <i class="bi bi-file-earmark-text"></i></h1>
        <a href="listagem_registros.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar à Lista
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            
            <form action="salvar_tarefa.php" method="POST">
                
                <?php if ($tarefa['id']): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($tarefa['id']) ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="titulo" class="form-label">Título da Tarefa <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($tarefa['titulo']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= htmlspecialchars($tarefa['descricao']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="prazo" class="form-label">Prazo <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="prazo" name="prazo" value="<?= htmlspecialchars($tarefa['prazo']) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="responsavel_id" class="form-label">Responsável <span class="text-danger">*</span></label>
                        <select class="form-select" id="responsavel_id" name="responsavel_id" required>
                            <option value="">Selecione um membro</option>
                            <?php foreach ($responsaveis as $resp): ?>
                                <option value="<?= htmlspecialchars($resp['id']) ?>"
                                    <?= ($tarefa['responsavel_id'] == $resp['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($resp['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="status_tarefa" class="form-label">Status da Tarefa</label>
                        <select class="form-select" id="status_tarefa" name="status_tarefa">
                            
                            <option value="atrasado" 
                                <?= $is_atrasado ? 'selected' : '' ?>
                                class="<?= $is_atrasado ? 'text-danger fw-bold' : '' ?>">
                                ATRASADA (Vencida)
                            </option>
                            
                            <?php 
                                // Para as opções abaixo, só selecionamos se NÃO for uma tarefa atrasada
                                $status_selecionado = !$is_atrasado ? $tarefa['status_tarefa'] : ''; 
                            ?>
                            <option value="pendente" <?= ($status_selecionado == 'pendente') ? 'selected' : '' ?>>Pendente</option>
                            <option value="em_andamento" <?= ($status_selecionado == 'em_andamento') ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="concluido" <?= ($status_selecionado == 'concluido') ? 'selected' : '' ?>>Concluída</option>
                        </select>
                    </div>
                </div>

                <div class="text-end pt-3">
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="bi bi-save me-1"></i> Salvar Tarefa
                    </button>
                </div>
            </form>
            
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
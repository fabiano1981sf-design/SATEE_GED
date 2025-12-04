<?php
/**
 * ARQUIVO: salvar_tarefa.php
 * DESCRIÇÃO: Processa o envio do formulário de tarefa (INSERT ou UPDATE).
 */
require_once 'auth.php'; // Protege o script
require_once 'conexao.php'; // Inclui a conexão PDO

// Redireciona em caso de acesso direto sem dados POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listagem_registros.php');
    exit;
}

// 1. Obter e Sanitizar Dados
$id_tarefa = $_POST['id'] ?? null;
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$prazo = $_POST['prazo'] ?? null; // Coluna correta para a data de vencimento
$responsavel_id = $_POST['responsavel_id'] ?? null; // ID do usuário responsável

// 2. Lógica de Validação e Status
$status_recebido = $_POST['status_tarefa'] ?? 'pendente'; 
$status_validos = ['pendente', 'em_andamento', 'concluido'];
$data_hoje = date('Y-m-d'); 

// Variável que será usada para salvar no banco
$status_tarefa = $status_recebido; 

// --- CORREÇÃO PARA ATRASO DINÂMICO ---
if (!in_array($status_recebido, $status_validos)) {
    // 2a. Se o status recebido é 'atrasado' ou inválido:
    
    // Força o status a ser 'pendente' no banco (já que 'atrasado' é calculado)
    $status_tarefa = 'pendente'; 

    // Se o usuário selecionou 'atrasado' no formulário:
    if ($status_recebido === 'atrasado') {
        
        // 2b. Garante que o prazo esteja no passado para o dashboard contar corretamente.
        // Se a data de prazo (que veio do formulário) for vazia ou no futuro:
        if (empty($prazo) || $prazo >= $data_hoje) {
             // Força o prazo para ser ontem, garantindo que seja contado como atrasado.
             $prazo = date('Y-m-d', strtotime('-1 day')); 
        }
    }
}
// --- FIM DA CORREÇÃO PARA ATRASO DINÂMICO ---


// 3. Validação Mínima
if (empty($titulo) || empty($prazo) || empty($responsavel_id)) {
    header('Location: form_tarefa.php?id=' . $id_tarefa . '&status=erro&msg=' . urlencode('Preencha todos os campos obrigatórios.'));
    exit;
}

try {
    if ($id_tarefa) {
        // --- ATUALIZAÇÃO (UPDATE) ---
        $sql = "UPDATE tarefas SET
                    titulo = :titulo,
                    descricao = :descricao,
                    prazo = :prazo,
                    responsavel_id = :responsavel_id,
                    status_tarefa = :status_tarefa
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_tarefa, PDO::PARAM_INT);
        $mensagem_sucesso = "Tarefa atualizada com sucesso!";

    } else {
        // --- INSERÇÃO (INSERT) ---
        $sql = "INSERT INTO tarefas (titulo, descricao, prazo, responsavel_id, status_tarefa)
                VALUES (:titulo, :descricao, :prazo, :responsavel_id, :status_tarefa)";
        
        $stmt = $pdo->prepare($sql);
        $mensagem_sucesso = "Nova tarefa cadastrada com sucesso!";
    }

    // 4. Bind e Execução Comum
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':prazo', $prazo); // Usa o valor $prazo, que pode ter sido ajustado
    $stmt->bindParam(':responsavel_id', $responsavel_id, PDO::PARAM_INT);
    $stmt->bindParam(':status_tarefa', $status_tarefa); // Usa o valor $status_tarefa ajustado
    
    $stmt->execute();

    // Redireciona após o sucesso
    header('Location: listagem_registros.php?status=sucesso&msg=' . urlencode($mensagem_sucesso));
    exit;

} catch (\PDOException $e) {
    // Redireciona em caso de erro no banco de dados
    error_log("Erro ao salvar tarefa: " . $e->getMessage());
    $erro_msg = 'Erro ao salvar a tarefa no banco de dados. Tente novamente.';
    
    $redirect_url = $id_tarefa ? "form_tarefa.php?id={$id_tarefa}" : "form_tarefa.php";
    
    header('Location: ' . $redirect_url . '&status=erro&msg=' . urlencode($erro_msg));
    exit;
}
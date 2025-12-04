<?php
/**
 * ARQUIVO: excluir_tarefa.php
 * DESCRIÇÃO: Processa a requisição de exclusão de uma tarefa.
 */

require_once 'conexao.php';

// 1. VERIFICAÇÃO DO ID
$tarefa_id = $_GET['id'] ?? null;

if (empty($tarefa_id) || !is_numeric($tarefa_id)) {
    header("Location: listagem_registros.php?status=erro&msg=" . urlencode("ID da tarefa inválido ou não fornecido."));
    exit();
}

// 2. LÓGICA DE EXCLUSÃO
$operacao_sucesso = false;
$mensagem_status = "";
$redirecionamento_url = "listagem_registros.php";

try {
    // Busca o título da tarefa antes de deletar (para usar na mensagem)
    $stmt_titulo = $pdo->prepare("SELECT titulo FROM tarefas WHERE id = :id");
    $stmt_titulo->bindParam(':id', $tarefa_id, PDO::PARAM_INT);
    $stmt_titulo->execute();
    $tarefa = $stmt_titulo->fetch();
    $titulo_tarefa = $tarefa['titulo'] ?? "Tarefa #{$tarefa_id}";


    // Executa o DELETE
    $sql = "DELETE FROM tarefas WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $tarefa_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $operacao_sucesso = true;
        $mensagem_status = "A tarefa '{$titulo_tarefa}' foi excluída com sucesso!";
    } else {
        $mensagem_status = "Tarefa não encontrada ou já excluída.";
    }

} catch (\PDOException $e) {
    $mensagem_status = "Erro ao excluir a tarefa. Detalhe: " . $e->getMessage();
}

// 3. REDIRECIONAMENTO FINAL
$status_final = $operacao_sucesso ? "sucesso" : "erro";
header("Location: {$redirecionamento_url}?status={$status_final}&msg=" . urlencode($mensagem_status));
exit();
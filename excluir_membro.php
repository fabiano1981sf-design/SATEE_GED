<?php
/**
 * ARQUIVO: excluir_membro.php
 * DESCRIÇÃO: Processa a requisição de exclusão de um membro.
 */

require_once 'conexao.php';

// 1. VERIFICAÇÃO DO ID
$membro_id = $_GET['id'] ?? null;

if (empty($membro_id) || !is_numeric($membro_id)) {
    header("Location: equipe.php?status=erro&msg=" . urlencode("ID do membro inválido ou não fornecido."));
    exit();
}

// 2. LÓGICA DE EXCLUSÃO (Com Verificação de Foto)
$operacao_sucesso = false;
$mensagem_status = "";
$redirecionamento_url = "equipe.php";

try {
    // A. Busca a foto_url antes de deletar (para limpar o arquivo)
    $stmt_foto = $pdo->prepare("SELECT foto_url FROM membros WHERE id = :id");
    $stmt_foto->bindParam(':id', $membro_id, PDO::PARAM_INT);
    $stmt_foto->execute();
    $membro = $stmt_foto->fetch();

    $foto_url = $membro['foto_url'] ?? null;
    $nome_membro = $membro['nome'] ?? "Membro #{$membro_id}";

    // B. Executa o DELETE
    // O banco de dados já está configurado para setar 'responsavel_id' como NULL nas tarefas (ON DELETE SET NULL),
    // o que evita erros de Foreign Key.

    $sql = "DELETE FROM membros WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $membro_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $operacao_sucesso = true;
        $mensagem_status = "O membro '{$nome_membro}' foi excluído com sucesso!";
        
        // C. Deleta o arquivo físico da foto, se existir
        if (!empty($foto_url) && file_exists($foto_url)) {
            unlink($foto_url);
        }
    } else {
        $mensagem_status = "Membro não encontrado ou já excluído.";
    }

} catch (\PDOException $e) {
    // Erro do BD. Pode ser um problema de Foreign Key se o ON DELETE SET NULL falhar.
    $mensagem_status = "Erro ao excluir o membro. Detalhe: " . $e->getMessage();
}

// 3. REDIRECIONAMENTO FINAL
$status_final = $operacao_sucesso ? "sucesso" : "erro";
header("Location: {$redirecionamento_url}?status={$status_final}&msg=" . urlencode($mensagem_status));
exit();
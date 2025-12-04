<?php
/**
 * ARQUIVO: salvar_membro.php
 * DESCRIÇÃO: Processa os dados do formulário de membro, faz o upload da foto e interage com o banco de dados.
 */

require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: equipe.php");
    exit();
}

// 1. COLETA E LIMPEZA DE DADOS
$nome = trim($_POST['nome'] ?? '');
$cargo = trim($_POST['cargo'] ?? '');
$email = trim($_POST['email'] ?? '');
$status_membro = trim($_POST['status_membro'] ?? 'ativo');
$membro_id = $_POST['membro_id'] ?? null; 
$foto_url_antiga = $_POST['foto_url_antiga'] ?? '';
$foto_url = $foto_url_antiga; // Assume a foto antiga por padrão

// 2. VALIDAÇÃO BÁSICA
$erros = [];
if (empty($nome)) $erros[] = "O nome é obrigatório.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "O email é inválido ou obrigatório.";

// 3. LÓGICA DE UPLOAD DA FOTO
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto'];
    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $nome_arquivo_novo = 'membro_' . uniqid() . '.' . $extensao;
    $diretorio_destino = 'uploads/fotos_membros/';
    $caminho_completo_servidor = $diretorio_destino . $nome_arquivo_novo;

    $tipos_permitidos = ['jpg', 'jpeg', 'png'];
    $tamanho_maximo = 2 * 1024 * 1024; // 2MB

    if (!in_array($extensao, $tipos_permitidos)) {
        $erros[] = "Tipo de arquivo não permitido. Use JPG ou PNG.";
    } elseif ($file['size'] > $tamanho_maximo) {
        $erros[] = "O arquivo é muito grande (máximo de 2MB).";
    } else {
        // Tenta mover o arquivo
        if (move_uploaded_file($file['tmp_name'], $caminho_completo_servidor)) {
            $foto_url = $caminho_completo_servidor;
            
            // Se houver foto antiga e ela for diferente da nova, deleta a antiga
            if ($foto_url_antiga && $foto_url_antiga != $foto_url && file_exists($foto_url_antiga)) {
                unlink($foto_url_antiga);
            }
        } else {
            $erros[] = "Erro ao mover o arquivo de upload. Verifique as permissões da pasta 'uploads/fotos_membros'.";
        }
    }
}


if (count($erros) > 0) {
    // Falha na validação/upload - Redireciona de volta para o formulário
    $msg_erro = "Falha: " . implode(" ", $erros);
    $redirecionamento = "form_membro.php" . ($membro_id ? "?id={$membro_id}" : "");
    header("Location: {$redirecionamento}?status=erro&msg=" . urlencode($msg_erro));
    exit();

} else {
    // 4. LÓGICA DE SALVAMENTO NO BANCO
    $operacao_sucesso = false;
    $mensagem_status = "";
    $redirecionamento_url = "equipe.php";

    try {
        $params = [
            ':nome' => $nome,
            ':cargo' => $cargo,
            ':email' => $email,
            ':status_membro' => $status_membro,
            ':foto_url' => $foto_url // NOVO PARÂMETRO
        ];
        
        if ($membro_id) {
            // Edição (UPDATE)
            $sql = "UPDATE membros SET nome = :nome, cargo = :cargo, email = :email, foto_url = :foto_url, status_membro = :status_membro WHERE id = :id";
            $params[':id'] = $membro_id; 
            $mensagem_status = "O membro '{$nome}' foi atualizado com sucesso!";
        } else {
            // Criação (INSERT)
            $sql = "INSERT INTO membros (nome, cargo, email, foto_url, status_membro) VALUES (:nome, :cargo, :email, :foto_url, :status_membro)";
            $mensagem_status = "Novo membro '{$nome}' adicionado com sucesso!";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params); 

        $operacao_sucesso = true;

    } catch (\PDOException $e) {
        // Se der erro no BD, tenta remover a foto que acabou de ser salva para evitar lixo
        if (!empty($foto_url) && $foto_url != $foto_url_antiga && file_exists($foto_url)) {
            unlink($foto_url);
        }
        $mensagem_status = "Erro: E-mail já existe ou falha no BD.";
        $redirecionamento_url = "form_membro.php" . ($membro_id ? "?id={$membro_id}" : "");
    }

    // 5. REDIRECIONAMENTO FINAL
    $status_final = $operacao_sucesso ? "sucesso" : "erro";
    header("Location: {$redirecionamento_url}?status={$status_final}&msg=" . urlencode($mensagem_status));
    exit();
}
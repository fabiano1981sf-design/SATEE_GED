<?php
/**
 * ARQUIVO: salvar_usuario.php
 * DESCRIÇÃO: Processa o cadastro/edição de usuários (incluindo a foto de perfil).
 */

require_once 'conexao.php';
require_once 'auth.php'; // Protege contra acesso direto

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redireciona com o ID do usuário se houver, garantindo um redirecionamento limpo
    $user_id = $_GET['id'] ?? null;
    $redirecionamento_limpo = "perfil.php" . ($user_id ? "?id={$user_id}" : "");
    header("Location: {$redirecionamento_limpo}");
    exit();
}

// 1. COLETA E LIMPEZA DE DADOS
$user_id = $_POST['user_id'] ?? null;
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? ''); 
$senha = $_POST['senha'] ?? '';
$perfil = trim($_POST['perfil'] ?? 'user');
$foto_url_antiga = $_POST['foto_url_antiga'] ?? '';
$foto_perfil_url = $foto_url_antiga; 

$modo_edicao = !empty($user_id);

// Se não for admin, garante que o perfil enviado é o que já estava ou 'user'
if ($_SESSION['usuario_perfil'] !== 'admin') {
    $perfil = $modo_edicao ? $_SESSION['usuario_perfil'] : 'user';
}

// 2. VALIDAÇÃO BÁSICA
$erros = [];
if (empty($nome)) $erros[] = "O nome é obrigatório.";
if (!$modo_edicao && (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))) $erros[] = "O email é inválido ou obrigatório para novos usuários.";
if (!$modo_edicao && empty($senha)) $erros[] = "A senha é obrigatória para novos usuários.";


// 3. LÓGICA DE UPLOAD DA FOTO
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto'];
    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // CORREÇÃO APLICADA AQUI: Define um nome base seguro para o arquivo
    // Usamos o ID do usuário (ou 'novo') e garantimos que não haja caracteres inválidos
    $nome_base = $user_id ? "user_{$user_id}" : 'novo_user_' . time(); 
    $nome_arquivo_novo = $nome_base . '.' . $extensao;
    
    // Garante que o diretório termina com barra
    $diretorio_destino = 'uploads/fotos_usuarios/'; 
    // Garante que o diretório de destino exista
    if (!is_dir($diretorio_destino)) {
        // Tenta criar o diretório recursivamente com permissões de leitura/escrita
        if (!mkdir($diretorio_destino, 0755, true)) {
            $erros[] = "Erro ao criar o diretório de upload. Verifique as permissões.";
        }
    }
    
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
            $foto_perfil_url = $caminho_completo_servidor;
            
            // Deleta a foto antiga APENAS se o caminho for diferente
            if ($foto_url_antiga && $foto_url_antiga != $foto_perfil_url && file_exists($foto_url_antiga)) {
                // Adiciona verificação para não apagar a imagem default do placeholder
                if (strpos($foto_url_antiga, 'placeholder') === false) {
                     unlink($foto_url_antiga);
                }
            }
        } else {
            // AQUI ESTÁ O ERRO DO LOG: move_uploaded_file falhou.
            $erros[] = "Erro ao mover o arquivo de upload. Verifique as permissões da pasta 'uploads/fotos_usuarios'.";
        }
    }
}


if (count($erros) > 0) {
    // Falha na validação/upload - Redireciona
    $msg_erro = "Falha: " . implode(" ", $erros);
    $redirecionamento = "perfil.php" . ($user_id ? "?id={$user_id}" : "");
    header("Location: {$redirecionamento}&status=erro&msg=" . urlencode($msg_erro));
    exit();

} else {
    // 4. LÓGICA DE SALVAMENTO NO BANCO
    $operacao_sucesso = false;
    $mensagem_status = "";
    // CORREÇÃO APLICADA AQUI: Redirecionamento limpo
    $redirecionamento_url = "perfil.php?id=" . $user_id; 

    try {
        // ... (o código de salvamento no banco permanece o mesmo)
        
        $params = [
            ':nome' => $nome,
            ':perfil' => $perfil,
            ':foto_perfil_url' => $foto_perfil_url
        ];
        
        $set_senha = !empty($senha) ? ", senha_hash = :senha_hash" : "";

        if ($modo_edicao) {
            // EDICAO (UPDATE)
            $sql = "UPDATE usuarios SET nome = :nome, perfil = :perfil, foto_perfil_url = :foto_perfil_url{$set_senha} WHERE id = :id";
            $params[':id'] = $user_id; 
            $mensagem_status = "O perfil de '{$nome}' foi atualizado com sucesso!";
            
            if (!empty($senha)) {
                $params[':senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
            }
            
        } else {
            // CADASTRO (INSERT)
            $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil, foto_perfil_url) 
                    VALUES (:nome, :email, :senha_hash, :perfil, :foto_perfil_url)";
            $params[':email'] = $email;
            $params[':senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
            $mensagem_status = "Novo usuário '{$nome}' cadastrado com sucesso!";
            $redirecionamento_url = "usuarios.php"; // Melhor redirecionar para a lista de usuários

        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params); 

        $operacao_sucesso = true;
        
        // Se for um novo usuário, pegamos o ID gerado para usar no redirecionamento limpo
        if (!$modo_edicao) {
            $user_id = $pdo->lastInsertId();
        }
        $redirecionamento_url = "perfil.php?id=" . $user_id; 

    } catch (\PDOException $e) {
        // Verifica erro de email duplicado (UNIQUE constraint)
        if ($e->getCode() == '23000') {
             $mensagem_status = "Erro: O e-mail já está cadastrado no sistema.";
        } else {
             $mensagem_status = "Erro grave ao processar dados. Detalhe: " . $e->getMessage();
        }
       
        $redirecionamento_url = "perfil.php" . ($user_id ? "?id={$user_id}" : "");
    }

    // 5. REDIRECIONAMENTO FINAL
    $status_final = $operacao_sucesso ? "sucesso" : "erro";
    // CORREÇÃO FINAL: Garante que os parâmetros de URL sejam concatenados corretamente
    header("Location: {$redirecionamento_url}&status={$status_final}&msg=" . urlencode($mensagem_status));
    exit();
}
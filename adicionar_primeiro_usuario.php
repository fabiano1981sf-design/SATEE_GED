<?php
/**
 * ARQUIVO: adicionar_primeiro_usuario.php
 * DESCRIÇÃO: Script de uso único para cadastrar o primeiro usuário ADMINISTRADOR.
 * * ATENÇÃO: Após o uso, REMOVA ou RENOMEIE este arquivo para evitar que seja 
 * executado por terceiros.
 */

// 1. INCLUSÃO DA CONEXÃO COM O BANCO DE DADOS
require_once 'conexao.php';

// =========================================================================
// 2. DADOS DO NOVO USUÁRIO (ALTERE AQUI!)
// =========================================================================
$nome = "Administrador Principal";
$email = "admin@sistema.com";
$senha_pura = "senhasegura123"; // Mude esta senha!
$perfil = "admin";
// =========================================================================


try {
    // 3. CRIAÇÃO DO HASH SEGURO DA SENHA
    $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

    // 4. PREPARAÇÃO DA QUERY SQL
    $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil) 
            VALUES (:nome, :email, :senha_hash, :perfil)";
            
    $stmt = $pdo->prepare($sql);
    
    // 5. VINCULAÇÃO E EXECUÇÃO DOS PARÂMETROS
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha_hash', $senha_hash);
    $stmt->bindParam(':perfil', $perfil);

    $stmt->execute();
    
    echo "<h1>🎉 Usuário Cadastrado com Sucesso!</h1>";
    echo "<p><strong>Nome:</strong> " . htmlspecialchars($nome) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Perfil:</strong> " . htmlspecialchars($perfil) . "</p>";
    echo "<p class='text-success'>Senha criptografada no banco (não exibida aqui).</p>";
    echo "<hr>";
    echo "<p><strong>Próximo Passo:</strong> Acesse <a href='login.php'>login.php</a> e utilize as credenciais acima.</p>";
    echo "<p class='text-danger'>⚠️ Lembre-se de **EXCLUIR OU RENOMEAR** este arquivo agora!</p>";

} catch (\PDOException $e) {
    if ($e->getCode() == '23000') {
        echo "<h1>❌ Erro ao Cadastrar Usuário</h1>";
        echo "<p><strong>Motivo:</strong> O e-mail <strong>" . htmlspecialchars($email) . "</strong> já está cadastrado no sistema (Email deve ser ÚNICO).</p>";
    } else {
        echo "<h1>❌ Erro Grave no Banco de Dados</h1>";
        echo "<p>Detalhes: " . $e->getMessage() . "</p>";
    }
}
?>
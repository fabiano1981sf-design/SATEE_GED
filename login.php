<?php
/**
 * ARQUIVO: login.php
 * DESCRIÇÃO: Tela de login e autenticação de usuários.
 */
session_start();
require_once 'conexao.php';

// Se o usuário já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$status = $_GET['status'] ?? '';
$mensagem = $_GET['msg'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $status = 'erro';
        $mensagem = 'Preencha todos os campos.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nome, senha_hash, perfil FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                // Login bem-sucedido
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];

                header("Location: index.php");
                exit();
            } else {
                $status = 'erro';
                $mensagem = 'Email ou senha incorretos.';
            }

        } catch (\PDOException $e) {
            $status = 'erro';
            $mensagem = 'Erro no banco de dados. Tente novamente mais tarde.';
            // Em ambiente de desenvolvimento, logar: error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gerencia-AT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { width: 100%; max-width: 400px; }
    </style>
</head>
<body>

<div class="login-card">
    <h1 class="text-center mb-4 text-primary"><i class="bi bi-gear-fill me-2"></i> Gerencia-AT</h1>
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white text-center">
            <h5 class="mb-0">Acesso ao Sistema</h5>
        </div>
        <div class="card-body p-4">
            
            <?php 
            // Função de alerta simples para login.
            if (!empty($status) && !empty($mensagem)) {
                $class = $status === 'sucesso' ? 'alert-success' : 'alert-danger';
                echo '<div class="alert ' . $class . '" role="alert">' . htmlspecialchars($mensagem) . '</div>';
            }
            ?>
            
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-in-right me-2"></i> Entrar</button>
                </div>
            </form>
        </div>
    </div>
    <p class="text-center text-muted mt-3"><small>&copy; 2025 Gerencia AT</small></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
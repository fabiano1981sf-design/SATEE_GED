<?php 
/**
 * ARQUIVO: perfil.php
 * DESCRIÇÃO: Formulário para cadastro/edição de usuários e perfil.
 */
require_once 'auth.php';
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; 

$status = $_GET['status'] ?? '';
$mensagem = $_GET['msg'] ?? '';

// =========================================================================
// LÓGICA CORRIGIDA PARA DETERMINAR O MODO (EDITAR, NOVO, MEU PERFIL)
// =========================================================================

$user_id_param = $_GET['id'] ?? null;
$user_id_editar = null;

// Define o MODO de operação
if ($user_id_param === 'novo') {
    // Caso 1: Cadastrar Novo Usuário (Aberto via botão de Admin)
    $modo_edicao = false;
    $titulo_pagina = "Cadastrar Novo Usuário";
    
    // Verifica a permissão de Admin para cadastrar
    if ($_SESSION['usuario_perfil'] !== 'admin') {
        header("Location: perfil.php?status=erro&msg=" . urlencode("Acesso negado. Apenas administradores podem cadastrar novos usuários."));
        exit();
    }
    
} elseif (!empty($user_id_param) && $user_id_param != $_SESSION['usuario_id']) {
    // Caso 2: Editar Outro Usuário (Aberto via lista de Admin)
    $user_id_editar = (int)$user_id_param;
    $modo_edicao = true;
    $titulo_pagina = "Editar Usuário #{$user_id_editar}";

} else {
    // Caso 3: Meu Perfil (Acesso padrão ou ?id=ID_LOGADO)
    $user_id_editar = $_SESSION['usuario_id'];
    $modo_edicao = true;
    $titulo_pagina = "Meu Perfil";
}

// Variáveis de inicialização do formulário
$nome = "";
$email = "";
$perfil = "user";
$foto_perfil_url = "";

try {
    // Busca dados do usuário (somente se estiver em modo edição)
    if ($modo_edicao) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $user_id_editar, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch();

        if ($usuario) {
            $nome = $usuario['nome'];
            $email = $usuario['email'];
            $perfil = $usuario['perfil'];
            $foto_perfil_url = $usuario['foto_perfil_url'] ?? "";
            
            // Permissão de edição
            // Se tentar editar o perfil de outro e não for admin
            if ($user_id_editar != $_SESSION['usuario_id'] && $_SESSION['usuario_perfil'] !== 'admin') {
                header("Location: perfil.php?status=erro&msg=" . urlencode("Você não tem permissão para editar este perfil."));
                exit();
            }

        } else {
            // Se o ID não for encontrado (ex: usuário deletado), volta para o Meu Perfil
            header("Location: perfil.php?status=erro&msg=" . urlencode("Usuário não encontrado."));
            exit();
        }
    }
} catch (\PDOException $e) {
    // Lidar com erro no banco
    if (function_exists('display_alert')) {
        display_alert('erro', 'Erro ao carregar dados do usuário.');
    }
}
?>

<div id="main-content">
    <?php 
    if (function_exists('display_alert')) {
        display_alert($status, $mensagem); 
    }
    ?>

    <h1 class="mb-3"><?= $titulo_pagina ?> <i class="bi bi-person-lines-fill"></i></h1>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Informações do Usuário e Segurança</h5>
        </div>
        <div class="card-body">
            
            <form action="salvar_usuario.php" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id_editar) ?>">
                <input type="hidden" name="foto_url_antiga" value="<?= htmlspecialchars($foto_perfil_url) ?>">
                
                <div class="row g-3">
                    
                    <div class="col-md-9 order-md-1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nome" class="form-label fw-bold">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required placeholder="Ex: Fabiano da Silva">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="exemplo@empresa.com" <?= $modo_edicao ? 'disabled' : '' ?>>
                                <?php if($modo_edicao): ?>
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                                    <div class="form-text">O email não pode ser alterado após o cadastro.</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="senha" class="form-label fw-bold">Nova Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" placeholder="Deixe em branco para não alterar" <?= !$modo_edicao ? 'required' : '' ?>>
                                <?php if(!$modo_edicao): ?>
                                    <div class="invalid-feedback">A senha é obrigatória para novos usuários.</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="perfil" class="form-label fw-bold">Nível de Acesso</label>
                                <select class="form-select" id="perfil" name="perfil" <?= $_SESSION['usuario_perfil'] !== 'admin' ? 'disabled' : '' ?>>
                                    <option value="user" <?= $perfil == 'user' ? 'selected' : '' ?>>Usuário Comum</option>
                                    <option value="admin" <?= $perfil == 'admin' ? 'selected' : '' ?>>Administrador</option>
                                </select>
                                <?php if($_SESSION['usuario_perfil'] !== 'admin'): ?>
                                    <input type="hidden" name="perfil" value="<?= htmlspecialchars($perfil) ?>">
                                    <div class="form-text">Apenas administradores podem alterar o nível de acesso.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 order-md-2 text-center">
                        <label class="form-label fw-bold w-100">Foto de Perfil</label>
                        
                        <img id="previewFoto" 
                             src="<?= !empty($foto_perfil_url) ? htmlspecialchars($foto_perfil_url) : 'https://via.placeholder.com/150/ADB5BD/FFFFFF?text=USER' ?>" 
                             alt="Pré-visualização da Foto" 
                             class="img-thumbnail rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover;">

                        <input type="file" class="form-control form-control-sm" id="foto" name="foto" accept="image/*">
                        <div class="form-text">Máx. 2MB (JPG, PNG).</div>
                    </div>
                    
                    <div class="col-12 order-md-3"><hr class="my-4"></div>
                    
                    <div class="col-12 order-md-4 text-end">
                        <a href="index.php" class="btn btn-secondary me-2">Voltar</a>
                        
                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-save me-1"></i> <?= $modo_edicao ? 'Salvar Alterações' : 'Cadastrar Usuário' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Pré-visualização da imagem
    document.getElementById('foto').addEventListener('change', function(event) {
        if (event.target.files && event.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewFoto').src = e.target.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    });

    // Script para habilitar a validação visual do Bootstrap
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>
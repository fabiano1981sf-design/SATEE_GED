<?php 
/**
 * ARQUIVO: form_membro.php
 * DESCRIÇÃO: Formulário para cadastro e edição de membros da equipe.
 */
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; // Inclui a conexão

$status = $_GET['status'] ?? '';
$mensagem = $_GET['msg'] ?? '';

// 1. Lógica de Edição: Verifica se um ID de membro foi passado
$membro_id = $_GET['id'] ?? null;
$modo_edicao = $membro_id ? true : false;
$titulo_pagina = $modo_edicao ? "Editar Membro #{$membro_id}" : "Novo Membro";

// 2. Valores Padrão
$nome = "";
$cargo = "";
$email = "";
$foto_url = ""; // Novo campo
$status_membro = "ativo";

try {
    // 3. BUSCA DE DADOS DO MEMBRO (apenas em modo de edição)
    if ($modo_edicao) {
        $stmt = $pdo->prepare("SELECT * FROM membros WHERE id = :id");
        $stmt->bindParam(':id', $membro_id, PDO::PARAM_INT);
        $stmt->execute();
        $membro = $stmt->fetch();

        if ($membro) {
            $nome = $membro['nome'];
            $cargo = $membro['cargo'];
            $email = $membro['email'];
            $foto_url = $membro['foto_url'] ?? ""; // Carrega a URL da foto
            $status_membro = $membro['status_membro'];
        } else {
            display_alert('erro', 'Membro não encontrado.');
            $modo_edicao = false; 
        }
    }
} catch (\PDOException $e) {
    display_alert('erro', 'Erro ao carregar dados do membro.');
}
?>

<div id="main-content">
    <?php display_alert($status, $mensagem); ?>

    <h1 class="mb-3"><?= $titulo_pagina ?> <i class="bi bi-person-circle"></i></h1>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Informações Pessoais e Foto</h5>
        </div>
        <div class="card-body">
            
            <form action="salvar_membro.php" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="membro_id" value="<?= htmlspecialchars($membro_id) ?>">
                <input type="hidden" name="foto_url_antiga" value="<?= htmlspecialchars($foto_url) ?>"> <div class="row g-3">
                    
                    <div class="col-md-9 order-md-1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nome" class="form-label fw-bold">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required placeholder="Ex: Maria da Silva">
                                <div class="invalid-feedback">Por favor, insira o nome completo.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cargo" class="form-label fw-bold">Cargo/Função</label>
                                <input type="text" class="form-control" id="cargo" name="cargo" value="<?= htmlspecialchars($cargo) ?>" placeholder="Ex: Desenvolvedor Front-end">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="exemplo@empresa.com">
                                <div class="invalid-feedback">Por favor, insira um e-mail válido.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status_membro" class="form-label fw-bold">Status</label>
                                <select class="form-select" id="status_membro" name="status_membro">
                                    <option value="ativo" <?= $status_membro == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="ausente" <?= $status_membro == 'ausente' ? 'selected' : '' ?>>Ausente</option>
                                    <option value="licenca" <?= $status_membro == 'licenca' ? 'selected' : '' ?>>Licença</option>
                                    <option value="desligado" <?= $status_membro == 'desligado' ? 'selected' : '' ?>>Desligado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 order-md-2 text-center">
                        <label class="form-label fw-bold w-100">Foto do Perfil</label>
                        
                        <img id="previewFoto" 
                             src="<?= !empty($foto_url) ? htmlspecialchars($foto_url) : 'https://via.placeholder.com/150/ADB5BD/FFFFFF?text=SEM+FOTO' ?>" 
                             alt="Pré-visualização da Foto" 
                             class="img-thumbnail rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover;">

                        <input type="file" class="form-control form-control-sm" id="foto" name="foto" accept="image/*">
                        <div class="form-text">Máx. 2MB (JPG, PNG).</div>
                    </div>
                    
                    <div class="col-12 order-md-3"><hr class="my-4"></div>
                    
                    <div class="col-12 order-md-4 text-end">
                        <a href="equipe.php" class="btn btn-secondary me-2">Cancelar</a>
                        
                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-save me-1"></i> <?= $modo_edicao ? "Salvar Alterações" : "Adicionar Membro" ?>
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
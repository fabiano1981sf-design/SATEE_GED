<?php 
/**
 * ARQUIVO: usuarios.php
 * DESCRIÇÃO: Lista todos os usuários do sistema (Acesso apenas para Admin).
 */
require_once 'auth.php';
include 'header.php';
include 'sidebar.php';
require_once 'conexao.php'; 

// Verifica permissão de Admin
if ($_SESSION['usuario_perfil'] !== 'admin') {
    header("Location: index.php?status=erro&msg=" . urlencode("Acesso negado. Apenas administradores."));
    exit();
}

$status = $_GET['status'] ?? '';
$mensagem = $_GET['msg'] ?? '';

// 1. BUSCA DE DADOS DOS USUÁRIOS
try {
    $sql = "SELECT id, nome, email, perfil, foto_perfil_url, data_cadastro FROM usuarios ORDER BY perfil DESC, nome ASC";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll();

} catch (\PDOException $e) {
    $usuarios = [];
    // Display alert function assumed to be defined in index.php or header.php
    display_alert('erro', 'Erro ao carregar a lista de usuários.');
}
?>

<div id="main-content">
    <?php display_alert($status, $mensagem); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gerenciamento de Usuários <i class="bi bi-person-gear"></i></h1>
		<a href="perfil.php?id=novo" class="btn btn-primary shadow-sm"><i class="bi bi-person-plus-fill me-1"></i> Cadastrar Novo Usuário</a>
        <!--a href="perfil.php?id=0" class="btn btn-primary shadow-sm"><i class="bi bi-person-plus-fill me-1"></i> Cadastrar Novo Usuário</a-->
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 20px;">#</th>
                            <th style="width: 80px;">Foto</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th style="width: 150px;">Perfil</th>
                            <th style="width: 150px;">Cadastro</th>
                            <th style="width: 100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr><td colspan="7" class="text-center text-muted">Nenhum usuário cadastrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php 
                                    $foto_src = !empty($usuario['foto_perfil_url']) && file_exists($usuario['foto_perfil_url'])
                                                ? htmlspecialchars($usuario['foto_perfil_url'])
                                                : "https://via.placeholder.com/50/ADB5BD/FFFFFF?text=U";
                                    $badge_class = $usuario['perfil'] === 'admin' ? 'text-bg-danger' : 'text-bg-info';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                                    <td><img src="<?= $foto_src ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;"></td>
                                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td><span class="badge <?= $badge_class ?>"><?= ucfirst($usuario['perfil']) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></td>
                                    <td>
                                        <a href="perfil.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <?php if ($usuario['id'] != $_SESSION['usuario_id']): // Não pode excluir a si mesmo ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Excluir" disabled><i class="bi bi-trash"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include 'includes/db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$atividade = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM atividades WHERE id = ?");
    $stmt->execute([$id]);
    $atividade = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$atividade) {
    header('Location: index.php');
    exit;
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h4 class="mb-0">Editar Atividade</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="save.php">
                    <input type="hidden" name="id" value="<?= $atividade['id'] ?>">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($atividade['nome']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= htmlspecialchars($atividade['descricao']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ativa" <?= $atividade['status'] == 'ativa' ? 'selected' : '' ?>>Ativa</option>
                            <option value="inativa" <?= $atividade['status'] == 'inativa' ? 'selected' : '' ?>>Inativa</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning">Atualizar</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
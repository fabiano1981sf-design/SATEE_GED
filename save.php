<?php
session_start();  // Para mensagens de sessão
include 'includes/db.php';

if ($_POST) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $status = $_POST['status'];

    if (empty($nome)) {
        $_SESSION['error'] = 'Nome é obrigatório!';
        header('Location: ' . ($_POST['id'] ? 'edit.php?id=' . $id : 'pages/form.php'));
        exit;
    }

    try {
        if ($id > 0) {
            // Atualizar
            $stmt = $pdo->prepare("UPDATE atividades SET nome = ?, descricao = ?, status = ? WHERE id = ?");
            $stmt->execute([$nome, $descricao, $status, $id]);
            $_SESSION['success'] = 'Atividade atualizada com sucesso!';
        } else {
            // Inserir novo
            $stmt = $pdo->prepare("INSERT INTO atividades (nome, descricao, status) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $descricao, $status]);
            $_SESSION['success'] = 'Atividade cadastrada com sucesso!';
        }
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erro ao salvar: ' . $e->getMessage();
        header('Location: ' . ($_POST['id'] ? 'edit.php?id=' . $id : 'pages/form.php'));
        exit;
    }
}
?>
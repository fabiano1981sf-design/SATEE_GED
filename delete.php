<?php
session_start();
include 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM atividades WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = 'Atividade deletada com sucesso!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erro ao deletar: ' . $e->getMessage();
    }
}

header('Location: index.php');
exit;
?>
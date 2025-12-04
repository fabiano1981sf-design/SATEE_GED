<?php
/**
 * ARQUIVO: auth.php
 * DESCRIÇÃO: Verifica se o usuário está logado em todas as páginas protegidas.
 */
session_start();

if (!isset($_SESSION['usuario_id'])) {
    // Redireciona para a página de login se não houver sessão ativa
    header("Location: login.php");
    exit();
}
// O restante do script pode continuar a ser executado
?>
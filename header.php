<?php
/**
 * ARQUIVO: header.php
 * DESCRIÇÃO: Estrutura HTML inicial, estilos e função de alerta.
 */

// Função para exibir o alerta de sucesso/erro
function display_alert($status, $message) {
    if (empty($status) || empty($message)) return;
    $class = $status === 'sucesso' ? 'alert-success' : 'alert-danger';
    $icon = $status === 'sucesso' ? '🎉' : '❌';
    $title = $status === 'sucesso' ? 'Sucesso!' : 'Erro!';
    
    echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
    echo '<strong>' . $icon . ' ' . $title . '</strong> ' . htmlspecialchars(urldecode($message));
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>';
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema FAB-v3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body {
            background-color: #f8f9fa;
        }
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 56px; 
            background-color: #343a40; 
            color: white;
            z-index: 1030;
        }
        #main-content {
            margin-left: var(--sidebar-width); 
            padding: 20px;
            padding-top: 76px;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        .nav-link.active, .nav-link:hover {
            color: white;
            background-color: #495057;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <span class="d-none d-sm-inline">Sistema FAB-v3</span>
            <span class="d-inline d-sm-none">G-AT</span>
        </a>

        <div class="d-flex">
            <span class="navbar-text me-3 d-none d-md-inline">Olá, Fabiano!</span>
            <button class="btn btn-outline-light btn-sm" type="button">Sair</button>
        </div>
    </div>
</nav>
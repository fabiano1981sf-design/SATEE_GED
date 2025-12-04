<?php
/**
 * ARQUIVO: sidebar.php
 * DESCRIÇÃO: Menu de navegação lateral (Sidebar) do sistema.
 */
// Obtém o nome do script atual para definir o item ativo
$current_page = basename($_SERVER['PHP_SELF']);

// Inicia a sessão (necessário para pegar o nome e perfil)

$usuario_logado = $_SESSION['usuario_nome'] ?? 'Visitante';
$perfil_logado = $_SESSION['usuario_perfil'] ?? 'user';
?>
<div id="sidebar">
    <ul class="nav flex-column p-3">
        <li class="nav-item">
            <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="index.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current_page == 'listagem_registros.php' ? 'active' : '' ?>" href="listagem_registros.php">
                <i class="bi bi-list-task me-2"></i> Tarefas
            </a>
        </li>
		
		
		        <li class="nav-item">
            <a class="nav-link <?= $current_page == 'calendario.php' ? 'active' : '' ?>" href="calendario.php">
                <i class="bi bi-calendar-event me-2"></i> Calendário
            </a>
        </li>
		
		
		
        <li class="nav-item">
            <a class="nav-link <?= $current_page == 'equipe.php' ? 'active' : '' ?>" href="equipe.php">
                <i class="bi bi-people me-2"></i> Equipe
            </a>
        </li>
		
		
<ul class="nav flex-column">
    
    <li class="nav-item">
        <a class="nav-link" href="ged.php">
            <i class="fa fa-envelope" aria-hidden="true"></i> 
            GED - Documentos
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="configuracao.php">
            <i class="fa fa-cog" aria-hidden="true"></i> 
            Configurações
        </a>
    </li>
    
    </ul>
		
<a class="nav-link <?= $current_page == 'relatorios.php' ? 'active' : '' ?>" href="relatorios.php">
    <i class="bi bi-file-earmark-bar-graph me-2"></i> Relatórios
</a>
        
        <?php if ($perfil_logado === 'admin'): // Link exclusivo para Admin ?>
        <li class="nav-item">
            <a class="nav-link <?= $current_page == 'usuarios.php' ? 'active' : '' ?>" href="usuarios.php">
                <i class="bi bi-person-gear me-2"></i> Gerenciar Usuários
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-item mt-3">
            <hr class="text-secondary">
            
            <span class="d-block text-white-50 p-2 pt-0 small">Logado como: <?= htmlspecialchars($usuario_logado) ?></span>
            
            <a class="nav-link <?= $current_page == 'perfil.php' ? 'active' : '' ?>" href="perfil.php">
                <i class="bi bi-person-circle me-2"></i> Meu Perfil
            </a>
            
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Sair
            </a>
        </li>
    </ul>
</div>
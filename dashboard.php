<?php 
    // Inclui o cabeçalho e os estilos
    include 'header.php';
    
    // Inclui o menu lateral
    include 'sidebar.php';
?>

<div id="main-content">
    
    <h1 class="mb-4">Dashboard de Atividades 📈</h1>

    <div class="row g-4 mb-5">
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-dark shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Pendentes</h5>
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <p class="card-text fs-2 fw-bold">12</p>
                    <small>Tarefas que requerem atenção</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Concluídas</h5>
                        <i class="bi bi-check-circle fs-3"></i>
                    </div>
                    <p class="card-text fs-2 fw-bold">45</p>
                    <small>Total finalizado este mês</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-danger text-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Críticas</h5>
                        <i class="bi bi-exclamation-triangle fs-3"></i>
                    </div>
                    <p class="card-text fs-2 fw-bold">3</p>
                    <small>Tarefas com prazo expirado</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Projetos Ativos</h5>
                        <i class="bi bi-folder2-open fs-3"></i>
                    </div>
                    <p class="card-text fs-2 fw-bold text-primary">8</p>
                    <small>Gerenciamento geral de projetos</small>
                </div>
            </div>
        </div>
    </div>
    <hr>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Atividades Recentes <i class="bi bi-list-task"></i></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Título da Tarefa</th>
                            <th scope="col">Prazo</th>
                            <th scope="col">Status</th>
                            <th scope="col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">101</th>
                            <td>Configurar Servidor de Testes</td>
                            <td><span class="badge text-bg-danger">28/05/2025</span></td>
                            <td><span class="badge text-bg-danger">Atrasado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" title="Deletar"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">102</th>
                            <td>Revisão de Código do Módulo X</td>
                            <td><span class="badge text-bg-warning text-dark">05/12/2025</span></td>
                            <td><span class="badge text-bg-warning text-dark">Em Andamento</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" title="Deletar"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">103</th>
                            <td>Reunião de Alinhamento Semanal</td>
                            <td><span class="badge text-bg-success">02/12/2025</span></td>
                            <td><span class="badge text-bg-success">Concluído</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" title="Deletar"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="#" class="btn btn-link">Ver Todas as Tarefas <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <br>
    
    <div class="text-center mt-4">
        <button class="btn btn-lg btn-primary shadow">
            <i class="bi bi-plus-circle me-2"></i> Nova Tarefa
        </button>
    </div>
    
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
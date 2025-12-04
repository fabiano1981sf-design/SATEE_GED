<?php
/**
 * ARQUIVO: api_calendario.php
 * DESCRIÇÃO: API que retorna os dados das tarefas em formato JSON para o FullCalendar.
 */
header('Content-Type: application/json');

require_once 'conexao.php';
// Nota: Não é necessário 'auth.php' aqui se o FullCalendar estiver em uma página protegida,
// mas para robustez, o auth.php deve ter iniciado a sessão.
session_start();

// Opcional: Se quiser filtrar tarefas pelo usuário logado
$usuario_id = $_SESSION['usuario_id'] ?? null;

// Mapeamento de Status para cores de eventos
$status_cores = [
    'pendente' => '#6c757d',     // Cinza (secondary)
    'em_andamento' => '#ffc107', // Amarelo (warning)
    'concluido' => '#198754',    // Verde (success)
    'atrasado' => '#dc3545',     // Vermelho (danger)
];

$eventos = [];

try {
    // Busca as tarefas
    $sql = "SELECT id, titulo, prazo, status_tarefa FROM tarefas WHERE prazo IS NOT NULL";
    
    // Opcional: Adicionar filtro para o usuário logado
    // if ($usuario_id) {
    //     $sql .= " AND responsavel_id = :user_id";
    // }
    
    $stmt = $pdo->prepare($sql);
    
    // if ($usuario_id) {
    //     $stmt->bindParam(':user_id', $usuario_id, PDO::PARAM_INT);
    // }
    
    $stmt->execute();
    $tarefas = $stmt->fetchAll();

    // Transforma os resultados do banco em Eventos do FullCalendar
    foreach ($tarefas as $tarefa) {
        $eventos[] = [
            'id' => $tarefa['id'],
            'title' => $tarefa['titulo'],
            'start' => $tarefa['prazo'], // FullCalendar usa 'start'
            'url' => 'form_tarefa.php?id=' . $tarefa['id'], // Link para edição
            'backgroundColor' => $status_cores[$tarefa['status_tarefa']] ?? '#6c757d',
            'borderColor' => $status_cores[$tarefa['status_tarefa']] ?? '#6c757d',
            // Adiciona a classe para ícone
            'classNames' => [
                'prioridade-' . strtolower($tarefa['status_tarefa'])
            ]
        ];
    }

} catch (\PDOException $e) {
    // Em caso de erro, retorna um array vazio (ou um erro estruturado)
    error_log("Erro ao buscar tarefas para o calendário: " . $e->getMessage());
}

// Retorna os eventos em formato JSON
echo json_encode($eventos);

?>
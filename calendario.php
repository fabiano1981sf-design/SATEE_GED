<?php 
/**
 * ARQUIVO: calendario.php
 * DESCRIÇÃO: Exibe as tarefas em um calendário interativo usando FullCalendar.
 */
require_once 'auth.php'; 
include 'header.php';
include 'sidebar.php';
// Nota: A conexão com o banco não é necessária aqui, apenas no api_calendario.php
?>

<div id="main-content">
    <h1 class="mb-4">Calendário de Tarefas <i class="bi bi-calendar-event"></i></h1>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/pt-br.global.min.js"></script> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        // Configurações gerais
        initialView: 'dayGridMonth', // Visão inicial: Mês
        locale: 'pt-br', // Idioma em Português
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Opções de visualização
        },
        height: 'auto', // Ajusta a altura automaticamente
        editable: false, // Se as tarefas podem ser arrastadas e redimensionadas

        // Fonte de Eventos (Onde buscar os dados das tarefas)
        events: 'api_calendario.php', 

        // Ações ao clicar em um evento (tarefa)
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // Impede a ação de link padrão

            // Redireciona para a página de edição da tarefa
            if (info.event.url) {
                window.location.href = info.event.url;
            } else {
                alert('Tarefa: ' + info.event.title + '\nPrazo: ' + info.event.start);
            }
        },
        
        // Ação ao clicar em um slot vazio do dia (para criar nova tarefa)
        dateClick: function(info) {
            // Abre o formulário de nova tarefa com a data preenchida
            var dataFormatada = info.dateStr;
            window.location.href = 'form_tarefa.php?prazo=' + dataFormatada;
        }
    });

    calendar.render();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
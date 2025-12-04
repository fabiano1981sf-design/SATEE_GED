<?php
/**
 * ARQUIVO: conexao.php
 * DESCRIÇÃO: Estabelece e retorna a conexão PDO com o banco de dados.
 */

// Inclui as configurações
require_once 'config.php';

$pdo = null; // Inicializa a variável PDO

try {
    // 1. Constrói a string DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    // 2. Opções de configuração do PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // 3. Cria a instância de conexão (PDO)
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (\PDOException $e) {
    // Trata erros de conexão. Em produção, registre o erro e não exiba detalhes.
    
    echo "<h1>Erro de Conexão com o Banco de Dados!</h1>";
    echo "<p>Verifique o arquivo <strong>config.php</strong> ou se o banco de dados <strong>" . DB_NAME . "</strong> existe.</p>";
    // echo "Detalhes do Erro: " . $e->getMessage(); 
    
    // Termina a execução do script
    exit(); 
}

// O objeto $pdo agora está disponível para uso nas suas páginas.
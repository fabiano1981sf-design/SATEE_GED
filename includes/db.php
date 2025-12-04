<?php
$host = 'localhost';
$dbname = 'gerencia_atv2';
$username = 'root';  // Ajuste se necessário
$password = 'root';      // Senha do MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
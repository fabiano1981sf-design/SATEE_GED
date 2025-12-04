<?php
/**
 * ARQUIVO: logout.php
 * DESCRIÇÃO: Destrói a sessão e redireciona para a página de login.
 */

session_start();
session_destroy();
header("Location: login.php");
exit();
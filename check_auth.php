<?php
session_start();

if (!isset($_SESSION['id_funcionario'])) {
    // Se não estiver logado, redireciona para a página de login com uma mensagem de erro
    header('Location: login.php?error=Acesso negado. Por favor, faça o login.');
    exit;
}
?>
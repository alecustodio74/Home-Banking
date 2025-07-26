<?php
// logout.php

// Inicia a sessão
session_start();

// Remove apenas as variáveis de sessão específicas do cliente
unset($_SESSION['cliente_id']);
unset($_SESSION['nome_cliente']);
unset($_SESSION['email_cliente']);
// Se você tiver outras variáveis específicas do cliente, adicione-as aqui
unset($_SESSION['user_type']); // Remove o tipo de usuário da sessão

// Limpa todas as variáveis de sessão
//$_SESSION = array();

// Destrói a sessão
//session_destroy();

// Redireciona para a página de login
header("Location: login.php");
exit();
?>

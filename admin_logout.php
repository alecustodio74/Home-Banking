<?php
// admin_logout.php

// Inicia a sessão
session_start();

// Remove apenas as variáveis de sessão específicas do cliente
unset($_SESSION['gerente_id']);
unset($_SESSION['gerente_nome']);
unset($_SESSION['gerente_email']);
unset($_SESSION['agencia_id']);
// Se você tiver outras variáveis específicas do cliente, adicione-as aqui
unset($_SESSION['user_type']); // Remove o tipo de usuário da sessão

// Limpa todas as variáveis de sessão
//$_SESSION = array();

// Destrói a sessão
//session_destroy();

// Redireciona para a página de login
header("Location: admin_login.php");
exit();
?>

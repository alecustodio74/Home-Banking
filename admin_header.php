<?php
// Inicia a sessão, se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de conexão com o banco de dados (conexao.php)
// Certifique-se de que este arquivo contém as configurações corretas do banco de dados
require_once("conexao.php");

// Verifica se o administrador está logado
if (!isset($_SESSION['admin_id'])) {
    // Se o administrador não estiver logado, redireciona para a página de login
    header("Location: admin_login.php");
    exit(); // Encerra a execução do script para garantir o redirecionamento
}

// Obtém o nome do administrador da sessão para exibir na barra de navegação
$adminNome = $_SESSION['admin_nome'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Bancário - Área Administrativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #34495e;
            color: white;
        }
        .navbar-brand {
            color: white;
            font-weight: bold;
        }
        .navbar-nav .nav-link {
            color: white;
            margin-right: 10px;
        }
        .navbar-nav .nav-link:hover {
            color: #f1c40f;
        }
        .container {
            margin-top: 20px;
        }
        .navbar-text {
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">Sistema Bancário</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['admin_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastrar_cliente.php">Cadastrar Cliente</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastrar_conta.php">Cadastrar Conta</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="selecionar_cliente.php">Selecionar Cliente</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="exibir_extrato.php">Exibir Extrato</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="listar_clientes.php">Listar Clientes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="listar_contas.php">Listar Contas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_logout.php">Sair do Sistema</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <span class="navbar-text">
                Olá, <?php echo $adminNome; ?>
            </span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <div class="container mt-4">

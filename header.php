<?php
// header.php
// Inicia a sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtém o nome do cliente da sessão para exibir na barra de navegação
// Usa 'nome_gerente' que é definido em admin_login.php
$cliente_nome = isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : 'Cliente';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Banking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      .navbar {
        background-color: #343a40;
      }
      .navbar-brand {
        color: #ffffff;
      }
      .navbar-nav .nav-link {
        color: #ffffff;
        margin-left: 10px;
      }
      .navbar-nav .nav-link:hover {
        color: #cccccc;
      }
      .dropdown-menu {
        background-color: #343a40;
        border: 1px solid #495057;
      }
      .dropdown-item {
        color: #ffffff;
      }
       .dropdown-item:hover, .dropdown-item:focus {
            background-color: #495057;
            color: #ffffff;
        }

      @media (max-width: 991px) {
        .dropdown-menu {
            background-color: #343a40;
            border: none;
        }
        .navbar-nav .nav-link {
            margin-left: 0;
        }
      }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-university"></i> Home Banking
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="menuConta" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> Olá, <?= htmlspecialchars($cliente_nome) ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="menuConta">
                                <li><a class="dropdown-item" href="criar_conta.php"><i class="fas fa-plus-circle"></i> Criar Conta</a></li>
                                <li><a class="dropdown-item" href="depositar.php"><i class="fas fa-money-bill-wave"></i> Depositar</a></li>
                                <li><a class="dropdown-item" href="sacar.php"><i class="fas fa-hand-holding-usd"></i> Sacar</a></li>
                                <li><a class="dropdown-item" href="transferir_pix.php"><i class="fas fa-exchange-alt"></i> Transferir PIX</a></li>
                                <li><a class="dropdown-item" href="pagar_boleto.php"><i class="fas fa-barcode"></i> Pagar Boleto</a></li>
                                <li><a class="dropdown-item" href="extrato.php"><i class="fas fa-file-invoice-dollar"></i> Extrato</a></li>
                                <li><a class="dropdown-item" href="sobre.php"><i class="fas fa-exclamation-circle"></i> Sobre</a></li>
                                <?php if ($_SESSION['email'] == 'admin@email.com'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="cadastrar_tipo_transacao.php"><i class="fas fa-plus"></i> Cadastrar Tipo Transação</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Entrar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="registrar.php"><i class="fas fa-user-plus"></i> Registrar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
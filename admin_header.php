<?php
// admin_header.php
// Cabeçalho para as páginas administrativas do sistema bancário, específico para gerentes
// Este arquivo NÃO deve conter lógica de sessão ou redirecionamento.
// A sessão já deve ter sido iniciada e validada na página principal que o inclui.

// Inicia a sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtém o nome do gerente da sessão para exibir na barra de navegação
// Usa 'nome_gerente' que é definido em admin_login.php
$gerente_nome = isset($_SESSION['gerente_nome']) ? $_SESSION['gerente_nome'] : 'Gerente';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Gerente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos gerais do corpo e navbar */
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #34495e;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
            transition: color 0.3s ease;
        }
        .navbar-brand:hover, .nav-link:hover {
            color: #eeeeee !important;
        }
        .navbar-toggler-icon {
            background-color: #ffffff;
        }
        .logout-btn {
            margin-left: auto;
        }
        .navbar-text {
            color: white;
        }

        /* Estilos dos itens do dashboard (copia do index.php) */
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px 0;
        }

        .dashboard-item {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 280px;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            padding: 10px;
        }

        .dashboard-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .dashboard-item h2 {
            font-size: 1.1em;
            margin-bottom: 8px;
            color: #333;
        }

        .dashboard-item a {
            text-decoration: none;
            color: #007bff;
            font-size: 0.9em;
            font-weight: 500;
        }

        .dashboard-item a:hover {
            text-decoration: underline;
        }

        /* Estilos para os cards de dados */
        .container {
            margin-top: 20px;
        }
        h1, h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background-color: #f0f0f0;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .card-title {
            color: #2c3e50;
            margin-bottom: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .card-body {
            padding: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .table thead th {
            background-color: #3498db;
            color: white;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #2980b9;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .table tbody tr:hover {
            background-color: #ecf0f1;
            transition: background-color 0.3s ease;
        }
        .table td, .table th {
            border-bottom: 1px solid #ddd;
            padding: 15px;
            text-align: left;
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #217dbb;
            transform: translateY(-2px);
        }
        .btn-danger {
            background-color: #e74c3c;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        .alert {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid transparent;
        }
        .alert-danger {
            background-color: #ffe1e1;
            color: #d8000c;
            border-color: #d8000c;
        }
        .text-center {
            text-align: center;
        }
        .my-4 {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="gerente.php">Banco Gerente</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    
                    <li class="nav-item">
                        <a class="nav-link" href="admin_registrar.php">Cadastrar Cliente</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_criarconta.php">Cadastrar Conta</a>
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
                </ul>
                <span class="navbar-text me-3">Olá, <?= htmlspecialchars($gerente_nome) ?></span>
                <a href="admin_logout.php" class="btn btn-danger">Sair</a>
            </div>
        </div>
    </nav>
    <div class="container"> <!-- Este div.container é aberto aqui e será fechado em footer.php -->

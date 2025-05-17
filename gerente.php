<?php
// gerente.php
// Página para exibir as funcionalidades do gerente

session_start();

// Inclui o arquivo de conexão com o banco de dados
require_once("conexao.php");
require_once("admin_header.php");

// Verifica se o gerente está logado
if (!isset($_SESSION['admin_id'])) { // Correção: Removida a verificação redundante com '==='
    // Redireciona para a página de login se não estiver logado
    header("Location: admin_login.php");
    exit();
}

// Dados do gerente logado
$gerente_id = $_SESSION['admin_id'];
$agencia_id = $_SESSION['agencia_id']; // Obtém o ID da agência do gerente

// Função para buscar os dados do gerente
function buscarDadosGerente($pdo, $gerente_id) {
    try {
        $stmt = $pdo->prepare("SELECT g.nome, g.email, a.nome as nome_agencia, a.cidade as cidade_agencia
                             FROM gerentes g
                             INNER JOIN agencias a ON g.agencia_id = a.id
                             WHERE g.id = :gerente_id");
        $stmt->bindParam(':gerente_id', $gerente_id, PDO::PARAM_INT);
        $stmt->execute();
        $dados_gerente = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dados_gerente) {
            throw new Exception("Gerente não encontrado."); // Tratamento de erro específico
        }
        return $dados_gerente;
    } catch (PDOException $e) {
        // Log do erro
        error_log("Erro ao buscar dados do gerente: " . $e->getMessage());
        return null; // Retorna null em caso de erro
    } catch (Exception $e) {
        error_log("Erro: " . $e->getMessage());
        return null;
    }
}

// Função para buscar os clientes da agência do gerente
function buscarClientesDaAgencia($pdo, $agencia_id) {
    try {
        $stmt = $pdo->prepare("SELECT id, nome, email FROM clientes WHERE agencia_id = :agencia_id");
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$clientes) {
             return [];
        }
        return $clientes;
    } catch (PDOException $e) {
        // Log do erro
        error_log("Erro ao buscar clientes da agência: " . $e->getMessage());
        return []; // Retorna um array vazio em caso de erro
    }
}

// Função para buscar os empréstimos da agência do gerente
function buscarEmprestimosDaAgencia($pdo, $agencia_id) {
    try {
        $stmt = $pdo->prepare("SELECT e.id, c.nome as nome_cliente, e.valor, e.data_inicio, e.data_fim, e.status
                             FROM emprestimos e
                             INNER JOIN clientes c ON e.cliente_id = c.id
                             WHERE c.agencia_id = :agencia_id");
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        $emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$emprestimos){
            return [];
        }
        return $emprestimos;
    } catch (PDOException $e) {
        // Log do erro
        error_log("Erro ao buscar empréstimos da agência: " . $e->getMessage());
        return []; // Retorna um array vazio em caso de erro
    }
}

// Função para buscar as contas da agência do gerente
function buscarContasDaAgencia($pdo, $agencia_id) {
    try {
        $stmt = $pdo->prepare("SELECT ct.id, c.nome as nome_cliente, ct.tipo, ct.saldo, ct.data_criacao
                             FROM contas ct
                             INNER JOIN clientes c ON ct.cliente_id = c.id
                             WHERE c.agencia_id = :agencia_id");
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$contas) {
            return [];
        }
        return $contas;
    } catch (PDOException $e) {
        // Log do erro
        error_log("Erro ao buscar contas da agência: " . $e->getMessage());
        return []; // Retorna um array vazio em caso de erro
    }
}

// Busca os dados do gerente
$dados_gerente = buscarDadosGerente($pdo, $gerente_id);

// Busca os clientes, empréstimos e contas da agência
$clientes = buscarClientesDaAgencia($pdo, $agencia_id);
$emprestimos = buscarEmprestimosDaAgencia($pdo, $agencia_id);
$contas = buscarContasDaAgencia($pdo, $agencia_id);

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página do Gerente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
            margin-left: auto;
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
        .logout-btn {
            margin-left: auto;
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
    

    <div class="container">
        <?php if (isset($mensagem_erro)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>

        <h1 class="my-4">Painel do Gerente</h1>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dados do Gerente</h2>
            </div>
            <div class="card-body">
                <?php if ($dados_gerente): ?>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($dados_gerente['nome']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($dados_gerente['email']); ?></p>
                    <p><strong>Agência:</strong> <?php echo htmlspecialchars($dados_gerente['nome_agencia'] . " - " . $dados_gerente['cidade_agencia']); ?></p>
                <?php else: ?>
                    <p class="text-center">Erro ao recuperar os dados do gerente.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Clientes da Agência</h2>
            </div>
            <div class="card-body">
                <?php if (empty($clientes)): ?>
                    <p class="text-center">Não há clientes cadastrados nesta agência.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Empréstimos da Agência</h2>
            </div>
            <div class="card-body">
                <?php if (empty($emprestimos)): ?>
                    <p class="text-center">Não há empréstimos cadastrados nesta agência.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th>Data Início</th>
                                    <th>Data Fim</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($emprestimos as $emprestimo): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($emprestimo['id']); ?></td>
                                        <td><?php echo htmlspecialchars($emprestimo['nome_cliente']); ?></td>
                                        <td><?php echo htmlspecialchars($emprestimo['valor']); ?></td>
                                        <td><?php echo htmlspecialchars($emprestimo['data_inicio']); ?></td>
                                        <td><?php echo htmlspecialchars($emprestimo['data_fim']); ?></td>
                                        <td><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Contas da Agência</h2>
            </div>
            <div class="card-body">
                <?php if (empty($contas)): ?>
                    <p class="text-center">Não há contas cadastradas nesta agência.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Saldo</th>
                                    <th>Data Criação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contas as $conta): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($conta['id']); ?></td>
                                        <td><?php echo htmlspecialchars($conta['nome_cliente']); ?></td>
                                        <td><?php echo htmlspecialchars($conta['tipo']); ?></td>
                                        <td><?php echo htmlspecialchars($conta['saldo']); ?></td>
                                        <td><?php echo htmlspecialchars($conta['data_criacao']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
require_once("footer.php");
?>
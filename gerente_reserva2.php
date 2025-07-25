<?php
// gerente.php
// Página para exibir as funcionalidades do gerente

session_start();

// Inclui o arquivo de conexão com o banco de dados
require_once("conexao.php");
require_once("admin_header.php");



// Dados do gerente logado (agora sabemos que estão definidos)
$gerente_id = $_SESSION['gerente_id'];
$agencia_id = $_SESSION['agencia_id'];

// --- FUNÇÕES DE BUSCA DE DADOS ---

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
            error_log("Gerente com ID {$gerente_id} não encontrado no banco de dados.");
            return null;
        }
        return $dados_gerente;
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar dados do gerente: " . $e->getMessage());
        return null;
    }
}

// Função para buscar os clientes da agência do gerente
function buscarClientesDaAgencia($pdo, $agencia_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT cl.id, cl.nome, cl.email
            FROM clientes cl
            INNER JOIN contas c ON cl.id = c.cliente_id
            WHERE c.agencia_id = :agencia_id
        ");
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar clientes da agência: " . $e->getMessage());
        return [];
    }
}

// Função para buscar as contas da agência do gerente
function buscarContasDaAgencia($pdo, $agencia_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT ct.id, cl.nome as nome_cliente, ct.tipo, ct.saldo, ct.data_criacao
            FROM contas ct
            INNER JOIN clientes cl ON ct.cliente_id = cl.id
            WHERE ct.agencia_id = :agencia_id
        ");
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar contas da agência: " . $e->getMessage());
        return [];
    }
}

// Função para buscar os empréstimos da agência do gerente
function buscarEmprestimosDaAgencia($pdo, $agencia_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT e.id, cl.nome as nome_cliente, e.valor, e.data_inicio, e.data_fim, e.status
            FROM emprestimos e
            INNER JOIN contas ct ON e.conta_id = ct.id
            INNER JOIN clientes cl ON ct.cliente_id = cl.id
            WHERE ct.agencia_id = :agencia_id
        ");
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar empréstimos da agência: " . $e->getMessage());
        return [];
    }
}

// --- BUSCA DE DADOS PARA A PÁGINA ---
$dados_gerente = buscarDadosGerente($pdo, $gerente_id);
$clientes = buscarClientesDaAgencia($pdo, $agencia_id);
$contas = buscarContasDaAgencia($pdo, $agencia_id);
$emprestimos = buscarEmprestimosDaAgencia($pdo, $agencia_id);

// Se os dados do gerente não puderem ser recuperados, exibe uma mensagem de erro
if (!$dados_gerente) {
    $mensagem_erro = "Não foi possível carregar os dados do gerente. Por favor, tente fazer login novamente.";
}

// --- INCLUI O CABEÇALHO (que agora contém as tags HTML de abertura e estilos) ---
require_once("admin_header.php");
?>

        <h1 class="mt-4">Bem-vindo, <?= htmlspecialchars($dados_gerente['nome'] ?? 'Gerente') ?>!</h1>

        <?php if (isset($mensagem_erro)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($mensagem_erro) ?>
            </div>
        <?php endif; ?>

        <!-- Seção de Ações do Gerente com o estilo do dashboard-container -->
        <div class="dashboard-container">
            <div class="dashboard-item">
                <h2><i class="fas fa-user-plus"></i> Cadastrar Cliente</h2>
                <a href="cadastrar_cliente.php">Registrar novo cliente</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-credit-card"></i> Cadastrar Conta</h2>
                <a href="criar_conta.php">Criar nova conta bancária</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-search"></i> Selecionar Cliente</h2>
                <a href="selecionar_cliente.php">Buscar e gerenciar cliente</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-file-alt"></i> Exibir Extrato</h2>
                <a href="exibir_extrato.php">Ver extrato de cliente</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-users"></i> Listar Clientes</h2>
                <a href="listar_clientes.php">Ver todos os clientes</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-university"></i> Listar Contas</h2>
                <a href="listar_contas.php">Ver todas as contas</a>
            </div>
        </div>

        <h2 class="my-4 text-center">Informações da Agência</h2>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Dados do Gerente</div>
            <div class="card-body">
                <?php if ($dados_gerente): ?>
                    <p><strong>Nome:</strong> <?= htmlspecialchars($dados_gerente['nome']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($dados_gerente['email']) ?></p>
                    <p><strong>Agência:</strong> <?= htmlspecialchars($dados_gerente['nome_agencia']) ?> - <?= htmlspecialchars($dados_gerente['cidade_agencia']) ?></p>
                <?php else: ?>
                    <p class="text-center">Erro ao recuperar os dados do gerente.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">Clientes da Agência</div>
            <div class="card-body">
                <?php if (empty($clientes)): ?>
                    <p class="text-center">Nenhum cliente encontrado nesta agência.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>ID</th><th>Nome</th><th>Email</th></tr></thead>
                            <tbody>
                                <?php foreach ($clientes as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['id']) ?></td>
                                        <td><?= htmlspecialchars($c['nome']) ?></td>
                                        <td><?= htmlspecialchars($c['email']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">Contas da Agência</div>
            <div class="card-body">
                <?php if (empty($contas)): ?>
                    <p class="text-center">Nenhuma conta encontrada nesta agência.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>ID</th><th>Cliente</th><th>Tipo</th><th>Saldo</th><th>Data Criação</th></tr></thead>
                            <tbody>
                                <?php foreach ($contas as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['id']) ?></td>
                                        <td><?= htmlspecialchars($c['nome_cliente']) ?></td>
                                        <td><?= htmlspecialchars($c['tipo']) ?></td>
                                        <td>R$ <?= number_format($c['saldo'], 2, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($c['data_criacao']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-warning text-white">Empréstimos da Agência</div>
            <div class="card-body">
                <?php if (empty($emprestimos)): ?>
                    <p class="text-center">Nenhum empréstimo encontrado nesta agência.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>ID</th><th>Cliente</th><th>Valor</th><th>Início</th><th>Fim</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($emprestimos as $e): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($e['id']) ?></td>
                                        <td><?= htmlspecialchars($e['nome_cliente']) ?></td>
                                        <td>R$ <?= number_format($e['valor'], 2, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($e['data_inicio']) ?></td>
                                        <td><?= htmlspecialchars($e['data_fim']) ?></td>
                                        <td><?= htmlspecialchars($e['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php require_once("footer.php"); ?>

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

// --- BUSCA DE DADOS PARA A PÁGINA ---
$dados_gerente = buscarDadosGerente($pdo, $gerente_id);

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
                <a href="admin_registrar.php">Registrar novo cliente</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-credit-card"></i> Cadastrar Conta</h2>
                <a href="admin_criarconta.php">Criar nova conta bancária</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-search"></i> Selecionar Cliente</h2>
                <a href="selecionar_cliente.php">Buscar e gerenciar cliente</a>
            </div>
            <div class="dashboard-item">
                <h2><i class="fas fa-file-alt"></i> Exibir Extrato</h2>
                <a href="admin_extrato.php">Ver extrato de cliente</a>
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
                    <p><strong>Agência:</strong> <?= $agencia_id ?> - <?= htmlspecialchars($dados_gerente['nome_agencia']) ?> - <?= htmlspecialchars($dados_gerente['cidade_agencia']) ?></p>
                <?php else: ?>
                    <p class="text-center">Erro ao recuperar os dados do gerente.</p>
                <?php endif; ?>
            </div>
        </div>
     
<?php require_once("footer.php"); ?>

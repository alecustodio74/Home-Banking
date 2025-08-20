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

// Função para buscar os dados do gerente (necessária para o admin_header.php)
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

// Função para buscar os clientes da agência do gerente com detalhes de conta, agência e banco
function buscarClientesDaAgencia($pdo, $agencia_id) {
    try {
        // Consulta SQL para conectar clientes, contas, agências, tipos de conta e bancos,
        // filtrando pela agencia_id do gerente logado.
        $sql = "
            SELECT
                cl.id AS cliente_id,
                cl.nome AS nome_cliente,
                cl.email AS email_cliente,
                co.numero_conta,
                tc.nome AS tipo_conta,
                co.saldo AS saldo_conta,
                a.nome AS nome_agencia,
                a.cidade AS cidade_agencia,
                b.nome AS nome_banco,
                co.data_criacao
            FROM
                clientes AS cl
            INNER JOIN
                contas AS co ON cl.id = co.cliente_id
            INNER JOIN
                agencias AS a ON co.agencia_id = a.id
            INNER JOIN
                tipos_conta AS tc ON co.tipo_conta_id = tc.id
            INNER JOIN
                bancos AS b ON a.banco_id = b.id
            WHERE
                a.id = :agencia_id
            ORDER BY
                cl.nome ASC;
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $clientes_encontrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $clientes_encontrados ?: [];
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar clientes da agência: " . $e->getMessage());
        return [];
    }
}

// --- BUSCA DE DADOS PARA A PÁGINA ---
$dados_gerente = buscarDadosGerente($pdo, $gerente_id);
$clientes = buscarClientesDaAgencia($pdo, $agencia_id);

// Se os dados do gerente não puderem ser recuperados, exibe uma mensagem de erro
if (!$dados_gerente) {
    $mensagem_erro = "Não foi possível carregar os dados do gerente. Por favor, tente fazer login novamente.";
}

// --- INCLUI O CABEÇALHO (que agora contém as tags HTML de abertura e estilos) ---
// Este include DEVE vir APÓS toda a lógica PHP e validação de sessão/dados.
require_once("admin_header.php");
?>

<h1 class="mt-4 text-center">Listagem de Contas da Agência</h1>

<?php if (isset($mensagem_erro)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($mensagem_erro) ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-info text-white">Contas da Agência <?= $agencia_id ?></div>
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <p class="text-center">Nenhuma conta encontrada nesta agência.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID Cliente</th>
                            <th>Nome Cliente</th>
                            <!-- <th>Email Cliente</th> -->
                            <th>Número Conta</th>
                            <th>Tipo Conta</th>
                            <th>Saldo Conta</th>
                            <th>Nome Agência</th>
                            <th>Cidade Agência</th>
                            <!-- <th>Nome Banco</th> -->
                            <th>Data Criação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['cliente_id']) ?></td>
                                <td><?= htmlspecialchars($c['nome_cliente']) ?></td>
                                <!-- <td><?= htmlspecialchars($c['email_cliente']) ?></td> -->
                                <td><?= htmlspecialchars($c['numero_conta']) ?></td>
                                <td><?= htmlspecialchars($c['tipo_conta']) ?></td>
                                <td>R$ <?= number_format($c['saldo_conta'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($c['nome_agencia']) ?></td>
                                <td><?= htmlspecialchars($c['cidade_agencia']) ?></td>
                                <!-- <td><?= htmlspecialchars($c['nome_banco']) ?></td> -->
                                <td><?= htmlspecialchars(date ('d/m/Y', strtotime($c['data_criacao']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="text-center mt-3 mb-5">
    <a href="gerente.php" class="btn btn-secondary">Voltar ao Painel do Gerente</a>
</div>     
<?php require_once("footer.php"); ?>

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

// --- BUSCA DE DADOS PARA A PÁGINA ---
$contas = buscarContasDaAgencia($pdo, $agencia_id);

// --- INCLUI O CABEÇALHO (que agora contém as tags HTML de abertura e estilos) ---
require_once("admin_header.php");
?>

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
       
<?php require_once("footer.php"); ?>

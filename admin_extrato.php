<?php
require_once("conexao.php");
require_once("admin_header.php");

// Lógica para verificar se o usuário está logado e tem contas
$conta_id = $_GET['conta_id'] ?? null;
$extrato = [];
$saldo_final = 0;

if ($conta_id && isset($_SESSION['cliente_id'])) {
    // Verificar se a conta pertence ao cliente logado (importante para segurança)
    $stmt_check_conta = $pdo->prepare("SELECT id FROM contas WHERE id = :conta_id AND cliente_id = :cliente_id");
    $stmt_check_conta->bindParam(':conta_id', $conta_id);
    $stmt_check_conta->bindParam(':cliente_id', $_SESSION['cliente_id']);
    $stmt_check_conta->execute();

    if ($stmt_check_conta->rowCount() > 0) {
        // Buscar extrato da conta
        $stmt_extrato = $pdo->prepare("
            SELECT t.data_hora, tt.nome AS tipo, t.valor, t.descricao, t.conta_destino, t.codigo_boleto
            FROM transacoes t
            JOIN tipos_transacao tt ON t.tipo_transacao_id = tt.id
            WHERE t.conta_id = :conta_id
            ORDER BY t.data_hora DESC
        ");
        $stmt_extrato->bindParam(':conta_id', $conta_id);
        $stmt_extrato->execute();
        $extrato = $stmt_extrato->fetchAll(PDO::FETCH_ASSOC);

        // Buscar saldo final da conta
        $stmt_saldo = $pdo->prepare("SELECT saldo FROM contas WHERE id = :conta_id");
        $stmt_saldo->bindParam(':conta_id', $conta_id);
        $stmt_saldo->execute();
        $saldo_result = $stmt_saldo->fetch(PDO::FETCH_ASSOC);
        $saldo_final = $saldo_result['saldo'] ?? 0;
    } else {
        echo "<div class='alert alert-danger'>Conta inválida ou não pertence ao usuário.</div>";
    }
}

// Buscar contas do usuário para o select
$contas = [];
if (isset($_SESSION['cliente_id'])) {
    $stmt_contas = $pdo->prepare("SELECT c.id, c.numero_conta, a.nome as nome_agencia, a.cidade, cl.nome as nome_cliente, a.id as agencia_id
                                FROM contas c
                                JOIN agencias a ON c.agencia_id = a.id
                                JOIN clientes cl ON c.cliente_id = cl.id
                                WHERE c.cliente_id = :cliente_id");
    $stmt_contas->bindParam(':cliente_id', $_SESSION['cliente_id']);
    $stmt_contas->execute();
    $contas = $stmt_contas->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-5">
    <h2>Extrato da Conta</h2>
    <form method="GET">
        <div class="mb-3">
            <label for="conta_id" class="form-label">Selecionar Conta:</label>
            <select class="form-control" id="conta_id" name="conta_id" required onchange="this.form.submit()">
                <option value="">Selecione uma conta</option>
                <?php foreach ($contas as $conta): ?>
                    <option value="<?php echo $conta['id']; ?>" <?php if ($conta['id'] == $conta_id) echo 'selected'; ?>>
                        <?php echo "Agência " . $conta['agencia_id'] . " - " . $conta['nome_agencia'] . " - " . $conta['cidade'] . " - Conta: " . $conta['numero_conta'] . " - Cliente: " . $conta['nome_cliente']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($extrato): ?>
        <h3>Transações:</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Descrição</th>
                    <th>Destino/Boleto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($extrato as $transacao): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($transacao['data_hora'])); ?></td>
                        <td><?php echo $transacao['tipo']; ?></td>
                        <td>R$ <?php echo number_format($transacao['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo $transacao['descricao']; ?></td>
                        <td><?php echo $transacao['conta_destino'] ?? $transacao['codigo_boleto'] ?? '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4 class="mt-3">Saldo Final: R$ <?php echo number_format($saldo_final, 2, ',', '.'); ?></h4>
    <?php elseif ($conta_id): ?>
        <p>Não foram encontradas transações para esta conta.</p>
    <?php else: ?>
        <p>Selecione uma conta para visualizar o extrato.</p>
    <?php endif; ?>
</div>

<?php require_once("footer.php"); ?>

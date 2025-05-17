<?php
require_once("conexao.php");
require_once("header.php");

// Verifica se o utilizador está logado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}

$mensagem_erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conta_id = filter_input(INPUT_POST, 'conta_id', FILTER_SANITIZE_NUMBER_INT);
    $valor = str_replace(',', '.', str_replace('.', '', $_POST['valor'])); // Remove formatação e substitui vírgula por ponto
    $valor = filter_var($valor, FILTER_VALIDATE_FLOAT);

    if (empty($conta_id) || empty($valor)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } elseif ($valor <= 0) {
        $mensagem_erro = "O valor do depósito deve ser maior que zero.";
    } elseif ($valor > 10000) {
        $mensagem_erro = "O valor máximo para depósito é de R$ 10.000,00.";
    } else {
        try {
            // Inicia a transação
            $pdo->beginTransaction();

            // Atualiza o saldo da conta
            $stmt_atualizar_saldo = $pdo->prepare("UPDATE contas SET saldo = saldo + :valor WHERE id = :conta_id");
            $stmt_atualizar_saldo->bindParam(':valor', $valor);
            $stmt_atualizar_saldo->bindParam(':conta_id', $conta_id);
            $stmt_atualizar_saldo->execute();

            // Registra a transação
            $tipo_transacao_id = 1; // ID para depósito
            $stmt_inserir_transacao = $pdo->prepare("INSERT INTO transacoes (conta_id, tipo_transacao_id, valor, data_hora) VALUES (:conta_id, :tipo_transacao_id, :valor, NOW())");
            $stmt_inserir_transacao->bindParam(':conta_id', $conta_id);
            $stmt_inserir_transacao->bindParam(':tipo_transacao_id', $tipo_transacao_id);
            $stmt_inserir_transacao->bindParam(':valor', $valor);
            $stmt_inserir_transacao->execute();

            // Commita a transação
            $pdo->commit();

            echo "<div class='alert alert-success'>Depósito realizado com sucesso!</div>";
        } catch (PDOException $e) {
            // Rollback em caso de erro
            $pdo->rollBack();
            $mensagem_erro = "Erro ao realizar o depósito: " . $e->getMessage();
        }
    }
}

// Busca as contas do cliente logado
try {
    $stmt_contas = $pdo->prepare("SELECT c.id, c.numero_conta, a.nome as nome_agencia, a.cidade, cl.nome as nome_cliente, a.id as agencia_id
                                FROM contas c
                                JOIN agencias a ON c.agencia_id = a.id
                                JOIN clientes cl ON c.cliente_id = cl.id
                                WHERE c.cliente_id = :cliente_id");
    $stmt_contas->bindParam(':cliente_id', $_SESSION['cliente_id']);
    $stmt_contas->execute();
    $contas = $stmt_contas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar contas: " . $e->getMessage();
    $contas = []; // Garante que $contas seja um array para evitar erros
}
?>

<div class="container mt-5">
    <h2>Realizar Depósito</h2>
    <?php if ($mensagem_erro): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $mensagem_erro; ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="conta_id" class="form-label">Conta:</label>
            <select class="form-control" id="conta_id" name="conta_id" required>
                <?php if (count($contas) > 0): ?>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?php echo $conta['id']; ?>">
                            <?php echo "Agência " . $conta['agencia_id'] . " - " . $conta['nome_agencia'] . " - " . $conta['cidade'] . " - Conta: " . $conta['numero_conta'] . " - Cliente: " . $conta['nome_cliente']; ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nenhuma conta encontrada para este cliente.</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="valor" class="form-label">Valor do Depósito (R$):</label>
            <input type="text" class="form-control" id="valor" name="valor" placeholder="Ex: 100,00" required>
            <small class="form-text text-muted">Valor máximo: R$ 10.000,00</small>
        </div>
        <button type="submit" class="btn btn-primary">Depositar</button>
    </form>
</div>

<script>
    // Simple input mask for the deposit amount
    document.getElementById('valor').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        let formattedValue = '';
        if (value.length > 0) {
            formattedValue = (parseInt(value) / 100).toLocaleString('pt-BR', { 
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }
        e.target.value = formattedValue;
    });
</script>

<?php require_once("footer.php"); ?>

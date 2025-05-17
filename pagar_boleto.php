<?php
require_once("conexao.php");
require_once("header.php");

// Lógica para verificar se o usuário está logado e tem contas

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conta_id = filter_input(INPUT_POST, 'conta_id', FILTER_SANITIZE_NUMBER_INT);
    $codigo_boleto = trim(htmlspecialchars($_POST['codigo_boleto'], ENT_QUOTES, 'UTF-8'));
    $valor = filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    if ($valor > 0 && !empty($codigo_boleto)) {
        try {
            $pdo->beginTransaction();

            // Verificar saldo da conta
            $stmt_saldo = $pdo->prepare("SELECT saldo FROM contas WHERE id = :conta_id");
            $stmt_saldo->bindParam(':conta_id', $conta_id);
            $stmt_saldo->execute();
            $saldo_result = $stmt_saldo->fetch(PDO::FETCH_ASSOC);
            $saldo_atual = $saldo_result['saldo'] ?? 0;

            // **Em um sistema real, você provavelmente integraria com uma API de pagamentos de boleto para validar o código e obter o valor.**
            // Aqui, estamos apenas simulando o pagamento.

            if ($saldo_atual >= $valor) {
                // Debitar da conta
                $stmt = $pdo->prepare("UPDATE contas SET saldo = saldo - :valor WHERE id = :conta_id");
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':conta_id', $conta_id);
                $stmt->execute();

                // Registrar a transação
                $tipo_pagamento_id = 4; // Supondo que 'Pagamento Boleto' tenha ID 4
                $stmt = $pdo->prepare("INSERT INTO transacoes (conta_id, tipo_transacao_id, valor, descricao, codigo_boleto) VALUES (:conta_id, :tipo_pagamento_id, :valor, 'Pagamento de Boleto', :codigo_boleto)");
                $stmt->bindParam(':conta_id', $conta_id);
                $stmt->bindParam(':tipo_pagamento_id', $tipo_pagamento_id);
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':codigo_boleto', $codigo_boleto);
                $stmt->execute();

                $pdo->commit();
                echo "<div class='alert alert-success'>Pagamento do boleto no valor de R$" . number_format($valor, 2, ',', '.') . " realizado com sucesso!</div>";
            } else {
                echo "<div class='alert alert-danger'>Saldo insuficiente para realizar o pagamento do boleto.</div>";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Erro ao realizar o pagamento do boleto: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Informe o código do boleto e o valor.</div>";
    }
}

// Buscar contas do usuário para o select
$contas = [];
if (isset($_SESSION['cliente_id'])) {
    $stmt = $pdo->prepare("SELECT id, numero_conta FROM contas WHERE cliente_id = :cliente_id");
    $stmt->bindParam(':cliente_id', $_SESSION['cliente_id']);
    $stmt->execute();
    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-5">
    <h2>Pagar Boleto</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="conta_id" class="form-label">Conta para Pagamento:</label>
            <select class="form-control" id="conta_id" name="conta_id" required>
                <?php foreach ($contas as $conta): ?>
                    <option value="<?php echo $conta['id']; ?>"><?php echo $conta['numero_conta']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="codigo_boleto" class="form-label">Código do Boleto:</label>
            <input type="text" class="form-control" id="codigo_boleto" name="codigo_boleto" required>
        </div>
        <div class="mb-3">
            <label for="valor" class="form-label">Valor do Boleto:</label>
            <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
        </div>
        <button type="submit" class="btn btn-primary">Pagar Boleto</button>
    </form>
</div>

<?php require_once("footer.php"); ?>
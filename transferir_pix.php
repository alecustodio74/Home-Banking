<?php
require_once("conexao.php");
require_once("header.php");

// Lógica para verificar se o usuário está logado e tem contas

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Filtra e valida os dados de entrada
    $conta_id = filter_input(INPUT_POST, 'conta_id', FILTER_SANITIZE_NUMBER_INT);
    $chave_pix_destino = trim(htmlspecialchars($_POST['chave_pix_destino'], ENT_QUOTES, 'UTF-8')); // Remove espaços e caracteres especiais
    $valor = str_replace(',', '.', str_replace('.', '', $_POST['valor'])); // Remove formatação e substitui vírgula por ponto
    $valor = filter_var($valor, FILTER_VALIDATE_FLOAT); // Valida como float

    $erro = false; // Variável para controlar se houve algum erro
    $mensagem_erro = ""; // Variável para armazenar mensagens de erro

    // Validações dos dados de entrada
    if ($valor <= 0) {
        $erro = true;
        $mensagem_erro .= "Informe um valor válido. ";
    }
    if (empty($chave_pix_destino)) {
        $erro = true;
        $mensagem_erro .= "Informe a chave PIX de destino.";
    }

    // Validação da Chave PIX
    if (!$erro) {
        try {
            // Modifiquei a query para buscar a chave PIX na tabela correta (pix_chaves)
            $stmt_chave_destino = $pdo->prepare("SELECT c.id, c.numero_conta
                                                FROM contas c
                                                JOIN pix_chaves p ON c.cliente_id = p.cliente_id -- Corrigido o JOIN
                                                WHERE p.chave = :chave_pix_destino");
            $stmt_chave_destino->bindParam(':chave_pix_destino', $chave_pix_destino);
            $stmt_chave_destino->execute();
            $conta_destino_result = $stmt_chave_destino->fetch(PDO::FETCH_ASSOC);
            $conta_destino_id = $conta_destino_result['id'] ?? null; // Obtém o ID da conta de destino
            $numero_conta_destino = $conta_destino_result['numero_conta'] ?? null;

            if (!$conta_destino_id) {
                $erro = true;
                $mensagem_erro .= "Chave PIX de destino inválida.";
            }
        } catch (PDOException $e) {
            $erro = true;
            $mensagem_erro .= "Erro ao validar chave PIX: " . $e->getMessage();
        }
    }

    // Executa a transferência somente se não houver erros de validação
    if (!$erro) {
        try {
            $pdo->beginTransaction();

            // Verificar saldo da conta de origem
            $stmt_saldo_origem = $pdo->prepare("SELECT saldo, numero_conta FROM contas WHERE id = :conta_id");
            $stmt_saldo_origem->bindParam(':conta_id', $conta_id);
            $stmt_saldo_origem->execute();
            $saldo_origem_result = $stmt_saldo_origem->fetch(PDO::FETCH_ASSOC);
            $saldo_atual_origem = $saldo_origem_result['saldo'] ?? 0;
            $numero_conta_origem = $saldo_origem_result['numero_conta'] ?? null;

            // Verifica se há saldo suficiente
            if ($saldo_atual_origem >= $valor) {
                // Debitar da conta de origem
                $stmt_debitar = $pdo->prepare("UPDATE contas SET saldo = saldo - :valor WHERE id = :conta_id");
                $stmt_debitar->bindParam(':valor', $valor);
                $stmt_debitar->bindParam(':conta_id', $conta_id);
                $stmt_debitar->execute();

                // Registrar a transação de débito
                $tipo_transferencia_id = 3; // Supondo que 'Transferência PIX' tenha ID 3
                $descricao = "Transferência PIX para " . $numero_conta_destino; // Variável para a descrição
                $stmt_debitar_transacao = $pdo->prepare("INSERT INTO transacoes (conta_id, tipo_transacao_id, valor, descricao, conta_destino) VALUES (:conta_id, :tipo_transferencia_id, :valor, :descricao, :conta_destino)");
                $stmt_debitar_transacao->bindParam(':conta_id', $conta_id);
                $stmt_debitar_transacao->bindParam(':tipo_transferencia_id', $tipo_transferencia_id);
                $stmt_debitar_transacao->bindParam(':valor', $valor);
                $stmt_debitar_transacao->bindParam(':descricao', $descricao); // Usando a variável
                $stmt_debitar_transacao->bindParam(':conta_destino', $numero_conta_destino);
                $stmt_debitar_transacao->execute();

                $pdo->commit();
                echo "<div class='alert alert-success'>Transferência PIX de R$" . number_format($valor, 2, ',', '.') . " para " . $chave_pix_destino . " realizada com sucesso!</div>";
            } else {
                echo "<div class='alert alert-danger'>" . ($saldo_atual_origem < $valor ? "Saldo insuficiente." : "Chave PIX de destino inválida.") . "</div>";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Erro ao realizar a transferência PIX: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>$mensagem_erro</div>";
    }
}

// Buscar contas do usuário para o select de origem
$contas = [];
if (isset($_SESSION['cliente_id'])) {
    $stmt = $pdo->prepare("SELECT c.id, c.numero_conta, a.nome as nome_agencia, a.cidade, cl.nome as nome_cliente, a.id as agencia_id
                                 FROM contas c
                                 JOIN agencias a ON c.agencia_id = a.id
                                 JOIN clientes cl ON c.cliente_id = cl.id
                                 WHERE c.cliente_id = :cliente_id");
    $stmt->bindParam(':cliente_id', $_SESSION['cliente_id']);
    $stmt->execute();
    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Buscar chaves PIX do usuário
$chaves_pix = [];
if (isset($_SESSION['cliente_id'])) {
    $stmt_pix = $pdo->prepare("SELECT chave FROM pix_chaves WHERE cliente_id = :cliente_id");
    $stmt_pix->bindParam(':cliente_id', $_SESSION['cliente_id']);
    $stmt_pix->execute();
    $chaves_pix = $stmt_pix->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="container mt-5">
    <h2>Transferência PIX</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="conta_id" class="form-label">Conta de Origem:</label>
            <select class="form-control" id="conta_id" name="conta_id" required>
                <?php if (count($contas) > 0): ?>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?php echo $conta['id']; ?>">
                            <?php echo "Agência " . $conta['agencia_id'] . " - " . $conta['nome_agencia'] . " - " . $conta['cidade'] . " , Conta: " . $conta['numero_conta'] . " - Cliente: " . $conta['nome_cliente']; ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nenhuma conta encontrada para este cliente.</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="chave_pix_destino" class="form-label">Chave PIX de Destino:</label>
            <select class="form-control" id="chave_pix_destino_select" name="chave_pix_destino_select">
                <option value="">Selecione a Chave PIX de Destino</option>
                <?php foreach ($chaves_pix as $chave_pix): ?>
                    <option value="<?php echo $chave_pix; ?>"><?php echo $chave_pix; ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" class="form-control mt-2" id="chave_pix_destino_input" name="chave_pix_destino" placeholder="Ou digite a chave PIX" >
            <small class="form-text text-muted">Você pode selecionar uma chave cadastrada ou digitar uma nova.</small>
            <a href="cadastrar_chave_pix.php" class="btn btn-sm btn-link mt-2">Cadastrar nova chave PIX</a>
            <input type="hidden" id="chave_pix_destino" name="chave_pix_destino">
        </div>
        <div class="mb-3">
            <label for="valor" class="form-label">Valor da Transferência (R$):</label>
            <input type="text" step="0.01" class="form-control" id="valor" name="valor" required>
            <small class="form-text text-muted">Valor máximo: R$ 10.000,00</small>
        </div>
        <button type="submit" class="btn btn-primary">Transferir</button>
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

    // JavaScript para atualizar o campo oculto com a chave PIX selecionada ou digitada
    const chavePixSelect = document.getElementById('chave_pix_destino_select');
    const chavePixInput = document.getElementById('chave_pix_destino_input');
    const chavePixDestino = document.getElementById('chave_pix_destino'); // Campo oculto

    chavePixSelect.addEventListener('change', function() {
        if (this.value) {
            chavePixDestino.value = this.value; // Atualiza o campo oculto com o valor selecionado
            chavePixInput.value = ''; // Limpa o campo de texto
            chavePixInput.disabled = true; // Desabilita o campo de texto
        } else {
            chavePixDestino.value = chavePixInput.value; // Mantém o valor do campo de texto
            chavePixInput.disabled = false; // Habilita o campo de texto
        }
    });

    chavePixInput.addEventListener('input', function() {
        chavePixDestino.value = this.value; // Atualiza o campo oculto com o valor digitado
        chavePixSelect.value = ''; // Limpa o select
    });
</script>

<?php require_once("footer.php"); ?>

<?php
// admin_extrato.php
// Página para o gerente visualizar o extrato de contas de clientes da sua agência.

session_start(); // Inicia a sessão

// Inclui o arquivo de conexão com o banco de dados
require_once("conexao.php");

// --- VERIFICAÇÃO DE LOGIN E PERMISSÃO DO GERENTE ---
// Verifica se o gerente está logado e se o ID da agência e o tipo de usuário estão definidos na sessão.
if (!isset($_SESSION['gerente_id']) || !isset($_SESSION['agencia_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'gerente') {
    header("Location: admin_login.php"); // Redireciona para a página de login se não for um gerente logado
    exit();
}

$gerente_id = $_SESSION['gerente_id'];
$agencia_id_gerente = $_SESSION['agencia_id']; // ID da agência do gerente logado

$cliente_selecionado_id = $_GET['cliente_id'] ?? null;
$conta_selecionada_id = $_GET['conta_id'] ?? null;
$extrato = [];
$saldo_final = 0;
$mensagem_erro = "";

// --- FUNÇÃO PARA BUSCAR CLIENTES DA AGÊNCIA DO GERENTE ---
function buscarClientesDaAgenciaGerente($pdo, $agencia_id) {
    try {
        // Seleciona clientes que possuem contas associadas a esta agência.
        // Garante que apenas clientes com contas na agência do gerente sejam listados.
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.id, c.nome, c.cpf
            FROM clientes c
            JOIN contas co ON c.id = co.cliente_id
            WHERE co.agencia_id = :agencia_id
            ORDER BY c.nome ASC
        ");
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar clientes da agência: " . $e->getMessage());
        return [];
    }
}

// --- LÓGICA PARA BUSCAR EXTRATO E SALDO ---
if ($cliente_selecionado_id && $conta_selecionada_id) {
    // 1. Verificar se a conta selecionada pertence ao cliente E à agência do gerente logado (segurança)
    $stmt_check_conta = $pdo->prepare("SELECT c.id FROM contas c WHERE c.id = :conta_id AND c.cliente_id = :cliente_id AND c.agencia_id = :agencia_id");
    $stmt_check_conta->bindParam(':conta_id', $conta_selecionada_id, PDO::PARAM_INT);
    $stmt_check_conta->bindParam(':cliente_id', $cliente_selecionado_id, PDO::PARAM_INT);
    $stmt_check_conta->bindParam(':agencia_id', $agencia_id_gerente, PDO::PARAM_INT);
    $stmt_check_conta->execute();

    if ($stmt_check_conta->rowCount() > 0) {
        // 2. Buscar extrato da conta
        $stmt_extrato = $pdo->prepare("
            SELECT t.data_hora, tt.nome AS tipo, t.valor, t.descricao, t.conta_destino, t.codigo_boleto, t.conta_origem
            FROM transacoes t
            JOIN tipos_transacao tt ON t.tipo_transacao_id = tt.id
            WHERE t.conta_id = :conta_id
            ORDER BY t.data_hora DESC
        ");
        $stmt_extrato->bindParam(':conta_id', $conta_selecionada_id, PDO::PARAM_INT);
        $stmt_extrato->execute();
        $extrato = $stmt_extrato->fetchAll(PDO::FETCH_ASSOC);

        // 3. Buscar saldo final da conta
        $stmt_saldo = $pdo->prepare("SELECT saldo FROM contas WHERE id = :conta_id");
        $stmt_saldo->bindParam(':conta_id', $conta_selecionada_id, PDO::PARAM_INT);
        $stmt_saldo->execute();
        $saldo_result = $stmt_saldo->fetch(PDO::FETCH_ASSOC);
        $saldo_final = $saldo_result['saldo'] ?? 0;
    } else {
        $mensagem_erro = "A conta selecionada é inválida ou não pertence a um cliente da sua agência.";
    }
}

// --- BUSCAR CLIENTES PARA O PRIMEIRO SELECT ---
$clientes_agencia = buscarClientesDaAgenciaGerente($pdo, $agencia_id_gerente);

// --- INCLUI O CABEÇALHO ADMINISTRATIVO ---
require_once("admin_header.php");
?>

<div class="container mt-5">
    <h2 class="mb-4">Extrato de Contas (Gerente)</h2>

    <?php if ($mensagem_erro): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($mensagem_erro); ?>
        </div>
    <?php endif; ?>

    <form method="GET" id="formExtratoGerente">
        <div class="mb-3">
            <label for="cliente_id" class="form-label">Selecionar Cliente:</label>
            <select class="form-control" id="cliente_id_select" name="cliente_id" required>
                <option value="">Selecione um cliente</option>
                <?php foreach ($clientes_agencia as $cliente): ?>
                    <option value="<?php echo htmlspecialchars($cliente['id']); ?>" <?php if ($cliente['id'] == $cliente_selecionado_id) echo 'selected'; ?>>
                       <?php echo htmlspecialchars($cliente['nome'] /* . " (CPF: " . $cliente['cpf'] . ")"*/); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="conta_id" class="form-label">Selecionar Conta:</label>
            <select class="form-control" id="conta_id_select" name="conta_id" required onchange="this.form.submit()">
                <option value="">Selecione uma conta</option>
                <?php if ($cliente_selecionado_id && !empty($contas_cliente_agencia)): ?>
                    <?php
                    // Se um cliente foi selecionado, vamos pre-popular as contas dele aqui
                    // A lista completa será carregada via AJAX, mas para a primeira carga da página
                    // se já houver um cliente e conta no GET, precisamos ter a opção selecionada.
                    ?>
                    <?php
                        // Nota: As contas serão carregadas via AJAX. No entanto, se a página
                        // for recarregada com `cliente_id` e `conta_id` na URL, precisamos garantir
                        // que a opção `selected` apareça. O JavaScript cuidará do resto.
                        if (isset($conta_selecionada_id) && $conta_selecionada_id != '') {
                            // Fetch the selected account details to display it
                            try {
                                $stmt_conta_detalhe = $pdo->prepare("SELECT numero_conta, agencia_id FROM contas WHERE id = :conta_id");
                                $stmt_conta_detalhe->bindParam(':conta_id', $conta_selecionada_id, PDO::PARAM_INT);
                                $stmt_conta_detalhe->execute();
                                $detalhe = $stmt_conta_detalhe->fetch(PDO::FETCH_ASSOC);
                                if ($detalhe) {
                                    echo '<option value="' . htmlspecialchars($conta_selecionada_id) . '" selected>';
                                    echo htmlspecialchars("Conta: " . $detalhe['numero_conta'] . " (Agência: " . $detalhe['agencia_id'] . ")");
                                    echo '</option>';
                                }
                            } catch (PDOException $e) {
                                error_log("Erro ao buscar detalhe da conta selecionada: " . $e->getMessage());
                            }
                        }
                    ?>
                <?php endif; ?>
            </select>
        </div>
    </form>

    <?php if ($extrato): ?>
        <h3 class="mt-4">Transações:</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Descrição</th>
                    <th>Destino/Origem/Boleto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($extrato as $transacao): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($transacao['data_hora'])); ?></td>
                        <td><?php echo htmlspecialchars($transacao['tipo']); ?></td>
                        <td class="<?php echo ($transacao['tipo'] === 'Depósito' || $transacao['tipo'] === 'Transferência PIX Recebida') ? 'text-success' : 'text-danger'; ?>">
                            R$ <?php echo number_format($transacao['valor'], 2, ',', '.'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($transacao['descricao']); ?></td>
                        <td>
                            <?php
                            if (!empty($transacao['conta_destino'])) {
                                echo "Destino: " . htmlspecialchars($transacao['conta_destino']);
                            } elseif (!empty($transacao['conta_origem'])) {
                                echo "Origem: " . htmlspecialchars($transacao['conta_origem']);
                            } elseif (!empty($transacao['codigo_boleto'])) {
                                echo "Boleto: " . htmlspecialchars($transacao['codigo_boleto']);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4 class="mt-3 text-end">Saldo Final: <span class="text-primary">R$ <?php echo number_format($saldo_final, 2, ',', '.'); ?></span></h4>
    <?php elseif ($cliente_selecionado_id && $conta_selecionada_id): ?>
        <p class="alert alert-info mt-4">Não foram encontradas transações para esta conta.</p>
    <?php else: ?>
        <p class="alert alert-info mt-4">Selecione um cliente e uma conta para visualizar o extrato.</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clienteSelect = document.getElementById('cliente_id_select');
        const contaSelect = document.getElementById('conta_id_select');
        const formExtratoGerente = document.getElementById('formExtratoGerente');
        const gerenteAgenciaId = <?php echo json_encode($agencia_id_gerente); ?>;

        // Função para carregar as contas de um cliente específico via AJAX
        function carregarContasPorCliente(clienteId) {
            if (!clienteId) {
                contaSelect.innerHTML = '<option value="">Selecione uma conta</option>';
                contaSelect.disabled = true;
                return;
            }

            contaSelect.disabled = false;
            contaSelect.innerHTML = '<option value="">Carregando contas...</option>'; // Mensagem de carregamento

            // Faz a requisição AJAX para get_contas_cliente_agencia.php
            fetch(`get_contas_cliente_agencia.php?cliente_id=${clienteId}&agencia_id=${gerenteAgenciaId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao carregar contas: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    contaSelect.innerHTML = '<option value="">Selecione uma conta</option>'; // Opção padrão
                    if (data.length > 0) {
                        data.forEach(conta => {
                            const option = document.createElement('option');
                            option.value = conta.id;
                            option.textContent = `Conta: ${conta.numero_conta} (Agência: ${conta.nome_agencia})`;
                            // Se a conta já estava selecionada (no caso de recarga da página com GET params)
                            if (conta.id == <?php echo json_encode($conta_selecionada_id); ?>) {
                                option.selected = true;
                            }
                            contaSelect.appendChild(option);
                        });
                    } else {
                        contaSelect.innerHTML = '<option value="">Nenhuma conta encontrada para este cliente nesta agência</option>';
                        contaSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Erro no fetch de contas:', error);
                    contaSelect.innerHTML = '<option value="">Erro ao carregar contas</option>';
                    contaSelect.disabled = true;
                    alert('Erro ao carregar as contas do cliente. Tente novamente mais tarde.');
                });
        }

        // Listener para o dropdown de clientes
        clienteSelect.addEventListener('change', function() {
            const selectedClientId = this.value;
            carregarContasPorCliente(selectedClientId);
            // Ao mudar o cliente, limpa o extrato anterior se não for submeter o form imediatamente
            if (selectedClientId && clienteIdWasSelectedBefore) { // Use uma flag ou lógica para evitar submissão dupla
                formExtratoGerente.submit();
            }
        });

        // Condição para carregar contas na primeira carga da página se um cliente já estiver selecionado
        const initialSelectedClientId = <?php echo json_encode($cliente_selecionado_id); ?>;
        if (initialSelectedClientId) {
            carregarContasPorCliente(initialSelectedClientId);
        } else {
            // Desabilita o select de contas se nenhum cliente estiver selecionado inicialmente
            contaSelect.disabled = true;
        }

        // Variável para controlar se o cliente já foi selecionado (para evitar submissão inicial desnecessária)
        // Isso é uma pequena otimização. Pode ser removida se o comportamento for indesejável.
        let clienteIdWasSelectedBefore = initialSelectedClientId !== null;
    });
</script>

<?php require_once("footer.php"); ?>
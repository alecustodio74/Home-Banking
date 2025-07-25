<?php
require_once("conexao.php");
require_once("admin_header.php");

// Lógica para verificar se o usuário está logado
//if (!isset($_SESSION['cliente_id'])) {
//    header("Location: login.php");
//    exit();
//}

$mensagem_erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_conta_id = filter_input(INPUT_POST, 'tipo_conta_id', FILTER_SANITIZE_NUMBER_INT);
    $agencia_id = filter_input(INPUT_POST, 'agencia_id', FILTER_SANITIZE_NUMBER_INT);
    $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_SANITIZE_NUMBER_INT); // Adicionado cliente_id

    if (empty($tipo_conta_id) || empty($agencia_id) || empty($cliente_id)) { // Adicionado cliente_id
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Buscar o código do tipo de conta
            $stmt_tipo_conta = $pdo->prepare("SELECT codigo FROM tipos_conta WHERE id = :tipo_conta_id");
            $stmt_tipo_conta->bindParam(':tipo_conta_id', $tipo_conta_id);
            $stmt_tipo_conta->execute();
            $tipo_conta_result = $stmt_tipo_conta->fetch(PDO::FETCH_ASSOC);
            $codigo_tipo_conta = $tipo_conta_result['codigo'];

            // Buscar o último número sequencial usado para este tipo de conta
            $stmt_max_conta = $pdo->prepare("SELECT MAX(SUBSTRING(numero_conta, 3, 3)) as max_sequencial FROM contas WHERE SUBSTRING(numero_conta, 1, 2) = :codigo_tipo_conta");
            $stmt_max_conta->bindParam(':codigo_tipo_conta', $codigo_tipo_conta);
            $stmt_max_conta->execute();
            $max_sequencial_result = $stmt_max_conta->fetch(PDO::FETCH_ASSOC);
            $max_sequencial = $max_sequencial_result['max_sequencial'] ?? 0;

            // Calcular o próximo número sequencial
            $proximo_sequencial = str_pad($max_sequencial + 1, 3, '0', STR_PAD_LEFT);
            $numero_conta = $codigo_tipo_conta . $proximo_sequencial;

            // Inserir nova conta
            $stmt = $pdo->prepare("INSERT INTO contas (cliente_id, tipo_conta_id, numero_conta, agencia_id, saldo) VALUES (:cliente_id, :tipo_conta_id, :numero_conta, :agencia_id, 0)");
            $stmt->bindParam(':cliente_id', $cliente_id); // Usando o cliente_id do POST
            $stmt->bindParam(':tipo_conta_id', $tipo_conta_id);
            $stmt->bindParam(':numero_conta', $numero_conta);
            $stmt->bindParam(':agencia_id', $agencia_id);
            $stmt->execute();

            echo "<div class='alert alert-success'>Conta criada com sucesso! Número da conta: " . $numero_conta . "</div>";
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao criar a conta: " . $e->getMessage();
        }
    }
}

// Buscar tipos de conta para o select
$stmt_tipos_conta = $pdo->prepare("SELECT id, nome FROM tipos_conta");
$stmt_tipos_conta->execute();
$tipos_conta = $stmt_tipos_conta->fetchAll(PDO::FETCH_ASSOC);

// Buscar agencias para o select
$stmt_agencias = $pdo->prepare("SELECT id, nome, cidade FROM agencias");
$stmt_agencias->execute();
$agencias = $stmt_agencias->fetchAll(PDO::FETCH_ASSOC);

// Buscar clientes por nome ou CPF
$clientes = [];
if (isset($_GET['busca_cliente'])) {
    $busca = trim(htmlspecialchars($_GET['busca_cliente'], ENT_QUOTES, 'UTF-8'));
    $tipo_busca = $_GET['tipo_busca']; // Pode ser 'nome' ou 'cpf'

    if ($tipo_busca == 'nome') {
        $stmt_clientes = $pdo->prepare("SELECT id, nome FROM clientes WHERE nome LIKE :busca");
        $stmt_clientes->bindValue(':busca', '%' . $busca . '%');
    } elseif ($tipo_busca == 'cpf') {
        $stmt_clientes = $pdo->prepare("SELECT id, nome FROM clientes WHERE cpf = :busca");
        $stmt_clientes->bindParam(':busca', $busca);
    } else {
        $stmt_clientes = $pdo->prepare("SELECT id, nome FROM clientes WHERE nome LIKE :busca");
        $stmt_clientes->bindValue(':busca', '%' . $busca . '%');
    }
    $stmt_clientes->execute();
    $clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt_clientes_lista = $pdo->prepare("SELECT id, nome FROM clientes");
    $stmt_clientes_lista->execute();
    $clientes_lista = $stmt_clientes_lista->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-5">
    <h2>Criar Nova Conta</h2>
    <?php if ($mensagem_erro) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $mensagem_erro; ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="tipo_conta_id" class="form-label">Tipo de Conta:</label>
            <select class="form-control" id="tipo_conta_id" name="tipo_conta_id" required>
                <?php foreach ($tipos_conta as $tipo_conta) : ?>
                    <option value="<?php echo $tipo_conta['id']; ?>"><?php echo $tipo_conta['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="agencia_id" class="form-label">Agência:</label>
            <select class="form-control" id="agencia_id" name="agencia_id" required>
                <?php foreach ($agencias as $agencia) : ?>
                    <option value="<?php echo $agencia['id']; ?>"><?php echo $agencia['nome'] . " - " . $agencia['cidade']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="cliente_id" class="form-label">Cliente:</label>
            <select class="form-control" id="cliente_id" name="cliente_id" required>
                <?php foreach ($clientes_lista as $cliente) : ?>
                    <option value="<?php echo $cliente['id']; ?>"><?php echo $cliente['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Criar Conta</button>
    </form>

    <hr>
    <form method="GET" class="mb-3">
        <div class="input-group">
            <select class="form-select" name="tipo_busca">
                <option value="nome">Nome</option>
                <option value="cpf">CPF</option>
            </select>
            <input type="text" class="form-control" name="busca_cliente" placeholder="Buscar cliente por nome ou CPF">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
        </div>
    </form>
    <?php if (isset($_GET['busca_cliente'])) : ?>
        <h3>Resultados da Busca:</h3>
        <?php if (count($clientes) > 0) : ?>
            <ul>
                <?php foreach ($clientes as $cliente) : ?>
                    <li><?php echo $cliente['nome']; ?> - <a href="?cliente_id=<?php echo $cliente['id']; ?>">Selecionar</a></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Nenhum cliente encontrado.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once("footer.php"); ?>

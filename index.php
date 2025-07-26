<?php
session_start();
require_once("conexao.php");

// Verifica se o usuário está logado e se é um cliente
//if (!isset($_SESSION['cliente_id']) || $_SESSION['user_type'] !== 'cliente_id') {
//if (!isset($_SESSION['cliente_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'cliente') {
    // Se não estiver logado, redireciona para a página de login
    //header("Location: login.php");
    //exit();
//}

// Obtém o saldo da conta do usuário logado
try {
    $stmt_saldo = $pdo->prepare("SELECT c.numero_conta, c.saldo FROM contas c INNER JOIN clientes cl ON c.cliente_id = cl.id WHERE cl.id = :cliente_id");
    $stmt_saldo->bindParam(':cliente_id', $_SESSION['cliente_id']);
    $stmt_saldo->execute();
    $saldo_result = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

    if ($saldo_result) {
        $numero_conta = $saldo_result['numero_conta'];
        $saldo = $saldo_result['saldo'];
    } else {
        $numero_conta = "N/A";
        $saldo = 0; // Ou qualquer valor padrão que você queira exibir
    }
} catch (PDOException $e) {
    echo "Erro ao obter o saldo: " . $e->getMessage();
    $numero_conta = "N/A";
    $saldo = 0;
}

require_once("header.php");
?>

<style>
    .dashboard-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding: 20px;
    }

    .dashboard-item {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 300px;
        height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
        padding: 10px;
    }

    .dashboard-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .dashboard-item h2 {
        font-size: 1.2em;
        margin-bottom: 10px;
        color: #333;
    }

    .dashboard-item a {
        text-decoration: none;
        color: #007bff;
        font-size: 1em;
    }

    .dashboard-item a:hover {
        text-decoration: underline;
    }

    #saldo-container {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: fit-content;
    }

    #saldo-atual {
        font-size: 1.5em;
        color: #28a745;
        margin-right: 10px;
    }

    .olho {
        cursor: pointer;
        font-size: 1.2em;
        color: #555;
    }
</style>

<div class="container">
    <h1 class="mt-4">Bem-vindo ao Home Banking</h1>

    <div id="saldo-container">
        <div>
            <p style="font-size: 1em; color: #333;">Número da Conta: <?php echo $numero_conta; ?></p>
            <span id="saldo-atual">Saldo Atual: R$ <?php echo number_format($saldo, 2, ',', '.'); ?></span>
        </div>
        <i id="olho" class="fas fa-eye olho"></i>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-item">
            <h2><i class="fas fa-plus-circle"></i> Criar Conta</h2>
            <a href="criar_conta.php">Criar uma nova conta</a>
        </div>
        <div class="dashboard-item">
            <h2><i class="fas fa-money-bill-wave"></i> Depositar</h2>
            <a href="depositar.php">Realizar um depósito</a>
        </div>
        <div class="dashboard-item">
            <h2><i class="fas fa-hand-holding-usd"></i> Sacar</h2>
            <a href="sacar.php">Realizar um saque</a>
        </div>
        <div class="dashboard-item">
            <h2><i class="fas fa-exchange-alt"></i> Transferir PIX</h2>
            <a href="transferir_pix.php">Fazer transferência PIX</a>
        </div>
        <div class="dashboard-item">
            <h2><i class="fas fa-barcode"></i> Pagar Boleto</h2>
            <a href="pagar_boleto.php">Pagar um boleto</a>
        </div>
        <div class="dashboard-item">
            <h2><i class="fas fa-file-invoice-dollar"></i> Extrato</h2>
            <a href="extrato.php">Verificar seu extrato</a>
        </div>
    </div>
</div>

<script>
    const saldoAtual = document.getElementById('saldo-atual');
    const olho = document.getElementById('olho');
    let saldoVisivel = true;

    olho.addEventListener('click', () => {
        if (saldoVisivel) {
            saldoAtual.textContent = 'Saldo Atual: ********';
            olho.classList.remove('fa-eye');
            olho.classList.add('fa-eye-slash');
            saldoVisivel = false;
        } else {
            saldoAtual.textContent = 'Saldo Atual: R$ <?php echo number_format($saldo, 2, ',', '.'); ?>';
            olho.classList.remove('fa-eye-slash');
            olho.classList.add('fa-eye');
            saldoVisivel = true;
        }
    });
</script>

<?php
require_once("footer.php");
?>

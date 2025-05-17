<?php
require_once("conexao.php");
require_once("header.php");

// Lógica para verificar se o usuário é administrador (você precisará implementar isso)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_transacao = trim(htmlspecialchars($_POST['nome_transacao'], ENT_QUOTES, 'UTF-8'));

    try {
        $stmt = $pdo->prepare("INSERT INTO tipos_transacao (nome) VALUES (:nome)");
        $stmt->bindParam(':nome', $nome_transacao);
        $stmt->execute();
        echo "<div class='alert alert-success'>Tipo de transação '" . $nome_transacao . "' cadastrado com sucesso!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Erro ao cadastrar o tipo de transação: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="container mt-5">
    <h2>Cadastrar Tipo de Transação</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="nome_transacao" class="form-label">Nome do Tipo de Transação:</label>
            <input type="text" class="form-control" id="nome_transacao" name="nome_transacao" required>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>
</div>

<?php require_once("footer.php"); ?>
<?php
// get_contas_cliente_agencia.php
// Endpoint AJAX para buscar contas de um cliente específico de uma agência.

session_start(); // Necessário para acessar o ID da agência do gerente na sessão
require_once("conexao.php");

header('Content-Type: application/json'); // Define o cabeçalho para indicar que a resposta é JSON

$cliente_id = filter_input(INPUT_GET, 'cliente_id', FILTER_SANITIZE_NUMBER_INT);
$agencia_id = filter_input(INPUT_GET, 'agencia_id', FILTER_SANITIZE_NUMBER_INT); // ID da agência do gerente

$contas = [];

// Garante que o cliente e a agência do gerente estão definidos
if ($cliente_id && $agencia_id && isset($_SESSION['gerente_id']) && $_SESSION['agencia_id'] == $agencia_id && $_SESSION['user_type'] === 'gerente') {
    try {
        // Busca as contas que pertencem ao cliente E à agência do gerente.
        $stmt = $pdo->prepare("
            SELECT co.id, co.numero_conta, a.nome AS nome_agencia
            FROM contas co
            JOIN agencias a ON co.agencia_id = a.id
            WHERE co.cliente_id = :cliente_id AND co.agencia_id = :agencia_id
            ORDER BY co.numero_conta ASC
        ");
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $stmt->bindParam(':agencia_id', $agencia_id, PDO::PARAM_INT);
        $stmt->execute();
        $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro no endpoint AJAX (get_contas_cliente_agencia.php): " . $e->getMessage());
        // Em caso de erro, retorna um array vazio ou uma mensagem de erro JSON
        echo json_encode(['error' => 'Erro ao buscar contas.']);
        exit();
    }
} else {
    // Caso o cliente_id ou agencia_id não sejam válidos, ou o gerente não esteja logado/autorizado
    echo json_encode(['error' => 'Parâmetros inválidos ou acesso não autorizado.']);
    exit();
}

echo json_encode($contas);
?>
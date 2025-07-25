<?php
    declare(strict_types=1);

    // $dominio: variável que recebe o string de conexão (é um padrão do PHP para
    //conexão com qualquer gerenciador de banco de dados)
    $host = 'localhost';
    $dbname = 'banking';
    $usuario = 'root';
    $senha = '123456'; //usuario e senha no MySQL Workbank ou outro banco como o Xampp

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
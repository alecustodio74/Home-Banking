<?php
// sobre.php
// Página Sobre o Projeto Home Banking

// Inclui o cabeçalho padrão
require_once("header.php");
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Sobre o Projeto Home Banking 1.0</h2>

    <div class="card shadow-lg">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h3 class="card-title">Informações do Projeto</h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Disciplina:</strong> Sistemas Operacionais II</li>
                            <li class="list-group-item"><strong>Desenvolvido em:</strong> HTML5, CSS3, PHP e MySQL</li>
                            <li class="list-group-item"><strong>Professor:</strong> Filipe Retali Melo Freixo dos Santos</li>
                            <li class="list-group-item"><strong>Data:</strong> Maio de 2025</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h3 class="card-title">Alunos</h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Alexandre Ricardo Custódio de Souza</li>
                            <li class="list-group-item">Nicolas da Silva Santos</li>
                            <li class="list-group-item">Renato Colhado Marques</li>
                            <li class="list-group-item">Walter Marciano Gomes Neto</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="card-title">Descrição do Projeto</h3>
                <p class="card-text">
                    Este projeto tem como objetivo desenvolver um sistema de Home Banking funcional,
                    utilizando tecnologias web como HTML5, CSS3, PHP e MySQL. O sistema simula
                    operações bancárias básicas, como transferências, consultas de saldo e extrato,
                    e cadastro de chaves PIX.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .card-title {
        color: #007bff;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .list-group-item {
        border: none;
        padding: 0.75rem 0;
    }

    .list-group-item:nth-child(odd) {
        background-color: #f8f9fa;
    }

    .card-text {
        line-height: 1.7;
        color: #343a40;
    }
</style>

<?php
// Inclui o rodapé padrão
require_once("footer.php");
?>
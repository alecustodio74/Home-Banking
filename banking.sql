create database if not exists banking;
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha VARCHAR(255),
    tipo ENUM('cliente','admin') DEFAULT 'cliente'
);

CREATE TABLE IF NOT EXISTS contas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    saldo DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_origem INT,
    conta_destino INT,
    valor DECIMAL(10,2),
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_id INT,
    descricao VARCHAR(255),
    valor DECIMAL(10,2),
    data_pagamento DATE,
    status ENUM('pendente', 'pago') DEFAULT 'pendente',
    FOREIGN KEY (conta_id) REFERENCES contas(id)
);
CREATE TABLE tipos_conta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    codigo VARCHAR(2) NOT NULL
);

INSERT INTO tipos_conta (nome, codigo) VALUES ('Corrente', '01');
INSERT INTO tipos_conta (nome, codigo) VALUES ('Poupança', '02');
INSERT INTO tipos_conta (nome, codigo) VALUES ('Salário', '03');

CREATE TABLE agencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cidade VARCHAR(255) NOT NULL
);
ALTER TABLE contas ADD COLUMN tipo_conta_id INT;

ALTER TABLE contas ADD COLUMN agencia_id INT;

CREATE TABLE bancos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
);

INSERT INTO bancos (nome) VALUES ('Home Banking SB');
INSERT INTO bancos (nome) VALUES ('Banco do Brasil');
INSERT INTO bancos (nome) VALUES ('Caixa Econômica Federal');
INSERT INTO bancos (nome) VALUES ('Itaú Unibanco');
INSERT INTO bancos (nome) VALUES ('Bradesco');
INSERT INTO bancos (nome) VALUES ('Santander');

ALTER TABLE transacoes ADD COLUMN chave_pix VARCHAR(255);

ALTER TABLE transacoes MODIFY COLUMN conta_id INT NOT NULL;

CREATE TABLE pix_chaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    chave VARCHAR(200) UNIQUE NOT NULL,
    tipo ENUM('CPF', 'CNPJ', 'Email', 'Telefone', 'Aleatoria') NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

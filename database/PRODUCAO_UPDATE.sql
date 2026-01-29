-- ================================================
-- SCRIPT DE ATUALIZAÇÃO PARA PRODUÇÃO
-- Sistema de Contas a Pagar + Fornecedores
-- Data: 29/01/2026
-- ================================================

-- IMPORTANTE: Faça backup completo do banco antes de executar!
-- Execute este script em ambiente de homologação primeiro

SET FOREIGN_KEY_CHECKS = 0;

-- ================================================
-- 1. CRIAÇÃO DAS TABELAS PRINCIPAIS
-- ================================================

-- Tabela: centros_custo
CREATE TABLE IF NOT EXISTS `centros_custo` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL,
    `tipo` ENUM('GRUPO', 'CNPJ') DEFAULT 'GRUPO',
    `empresa_id` BIGINT(20) UNSIGNED NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `centros_custo_empresa_id_foreign` (`empresa_id`),
    CONSTRAINT `centros_custo_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: categorias
CREATE TABLE IF NOT EXISTS `categorias` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL,
    `tipo` ENUM('FIXA', 'VARIAVEL', 'INVESTIMENTO') NOT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: subcategorias
CREATE TABLE IF NOT EXISTS `subcategorias` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `categoria_id` BIGINT(20) UNSIGNED NOT NULL,
    `nome` VARCHAR(255) NOT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `subcategorias_categoria_id_foreign` (`categoria_id`),
    CONSTRAINT `subcategorias_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: contas
CREATE TABLE IF NOT EXISTS `contas` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `subcategoria_id` BIGINT(20) UNSIGNED NOT NULL,
    `nome` VARCHAR(255) NOT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `contas_subcategoria_id_foreign` (`subcategoria_id`),
    CONSTRAINT `contas_subcategoria_id_foreign` FOREIGN KEY (`subcategoria_id`) REFERENCES `subcategorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: fornecedores
CREATE TABLE IF NOT EXISTS `fornecedores` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tipo_pessoa` ENUM('PF', 'PJ') NOT NULL,
    `cpf_cnpj` VARCHAR(18) NOT NULL UNIQUE,
    `razao_social` VARCHAR(255) NOT NULL,
    `nome_fantasia` VARCHAR(255) NULL,
    `cep` VARCHAR(9) NULL,
    `logradouro` VARCHAR(255) NULL,
    `numero` VARCHAR(20) NULL,
    `bairro` VARCHAR(100) NULL,
    `cidade` VARCHAR(100) NULL,
    `estado` VARCHAR(2) NULL,
    `complemento` VARCHAR(255) NULL,
    `observacoes` TEXT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `fornecedores_cpf_cnpj_unique` (`cpf_cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: fornecedor_contatos
CREATE TABLE IF NOT EXISTS `fornecedor_contatos` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `fornecedor_id` BIGINT(20) UNSIGNED NOT NULL,
    `nome` VARCHAR(255) NOT NULL,
    `cargo` VARCHAR(100) NULL,
    `email` VARCHAR(255) NULL,
    `telefone` VARCHAR(20) NULL,
    `principal` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `fornecedor_contatos_fornecedor_id_foreign` (`fornecedor_id`),
    CONSTRAINT `fornecedor_contatos_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: contas_fixas_pagar
CREATE TABLE IF NOT EXISTS `contas_fixas_pagar` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `centro_custo_id` BIGINT(20) UNSIGNED NOT NULL,
    `conta_id` BIGINT(20) UNSIGNED NOT NULL,
    `fornecedor_id` BIGINT(20) UNSIGNED NULL,
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10, 2) NOT NULL,
    `dia_vencimento` INT NOT NULL,
    `periodicidade` ENUM('SEMANAL','QUINZENAL','MENSAL','TRIMESTRAL','SEMESTRAL','ANUAL') DEFAULT 'MENSAL',
    `forma_pagamento` ENUM('PIX','BOLETO','TRANSFERENCIA','CARTAO_CREDITO','CARTAO_DEBITO','DINHEIRO','CHEQUE','DEBITO_AUTOMATICO') NULL,
    `data_inicial` DATE NULL,
    `data_fim` DATE NULL,
    `conta_financeira_id` BIGINT(20) UNSIGNED NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `contas_fixas_pagar_centro_custo_id_foreign` (`centro_custo_id`),
    KEY `contas_fixas_pagar_conta_id_foreign` (`conta_id`),
    KEY `contas_fixas_pagar_fornecedor_id_foreign` (`fornecedor_id`),
    KEY `contas_fixas_pagar_conta_financeira_id_foreign` (`conta_financeira_id`),
    CONSTRAINT `contas_fixas_pagar_centro_custo_id_foreign` FOREIGN KEY (`centro_custo_id`) REFERENCES `centros_custo` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contas_fixas_pagar_conta_id_foreign` FOREIGN KEY (`conta_id`) REFERENCES `contas` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contas_fixas_pagar_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL,
    CONSTRAINT `contas_fixas_pagar_conta_financeira_id_foreign` FOREIGN KEY (`conta_financeira_id`) REFERENCES `contas_financeiras` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: contas_pagar
CREATE TABLE IF NOT EXISTS `contas_pagar` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `centro_custo_id` BIGINT(20) UNSIGNED NOT NULL,
    `conta_id` BIGINT(20) UNSIGNED NOT NULL,
    `conta_financeira_id` BIGINT(20) UNSIGNED NULL,
    `conta_fixa_pagar_id` BIGINT(20) UNSIGNED NULL,
    `fornecedor_id` BIGINT(20) UNSIGNED NULL,
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10, 2) NOT NULL,
    `data_vencimento` DATE NOT NULL,
    `data_inicial` DATE NULL,
    `data_fim` DATE NULL,
    `periodicidade` ENUM('SEMANAL','QUINZENAL','MENSAL','TRIMESTRAL','SEMESTRAL','ANUAL') NULL,
    `status` ENUM('em_aberto', 'pago', 'vencido') DEFAULT 'em_aberto',
    `tipo` ENUM('avulsa', 'fixa') DEFAULT 'avulsa',
    `pago_em` DATETIME NULL,
    `forma_pagamento` VARCHAR(50) NULL,
    `observacoes` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `contas_pagar_centro_custo_id_foreign` (`centro_custo_id`),
    KEY `contas_pagar_conta_id_foreign` (`conta_id`),
    KEY `contas_pagar_conta_financeira_id_foreign` (`conta_financeira_id`),
    KEY `contas_pagar_conta_fixa_pagar_id_foreign` (`conta_fixa_pagar_id`),
    KEY `contas_pagar_fornecedor_id_foreign` (`fornecedor_id`),
    CONSTRAINT `contas_pagar_centro_custo_id_foreign` FOREIGN KEY (`centro_custo_id`) REFERENCES `centros_custo` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contas_pagar_conta_id_foreign` FOREIGN KEY (`conta_id`) REFERENCES `contas` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contas_pagar_conta_financeira_id_foreign` FOREIGN KEY (`conta_financeira_id`) REFERENCES `contas_financeiras` (`id`) ON DELETE SET NULL,
    CONSTRAINT `contas_pagar_conta_fixa_pagar_id_foreign` FOREIGN KEY (`conta_fixa_pagar_id`) REFERENCES `contas_fixas_pagar` (`id`) ON DELETE SET NULL,
    CONSTRAINT `contas_pagar_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================
-- 2. INSERÇÃO DE DADOS INICIAIS (SEEDERS)
-- ================================================

-- Centro de Custo Padrão
INSERT IGNORE INTO `centros_custo` (`id`, `nome`, `tipo`, `empresa_id`, `ativo`, `created_at`, `updated_at`) 
VALUES (1, 'Grupo - Despesas Gerais', 'GRUPO', NULL, 1, NOW(), NOW());

-- Categorias
INSERT IGNORE INTO `categorias` (`id`, `nome`, `tipo`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Despesas Fixas', 'FIXA', 1, NOW(), NOW()),
(2, 'Despesas Variáveis', 'VARIAVEL', 1, NOW(), NOW()),
(3, 'Investimentos', 'INVESTIMENTO', 1, NOW(), NOW());

-- Subcategorias (Despesas Fixas)
INSERT IGNORE INTO `subcategorias` (`id`, `categoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Escritório', 1, NOW(), NOW()),
(2, 1, 'Pessoal', 1, NOW(), NOW()),
(3, 1, 'Financeiro', 1, NOW(), NOW()),
(4, 1, 'Frota (Fixo)', 1, NOW(), NOW());

-- Subcategorias (Despesas Variáveis)
INSERT IGNORE INTO `subcategorias` (`id`, `categoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(5, 2, 'Escritório / Infraestrutura', 1, NOW(), NOW()),
(6, 2, 'Frota (Variável)', 1, NOW(), NOW()),
(7, 2, 'Operacional', 1, NOW(), NOW()),
(8, 2, 'Comercial', 1, NOW(), NOW()),
(9, 2, 'Impostos', 1, NOW(), NOW());

-- Subcategorias (Investimentos)
INSERT IGNORE INTO `subcategorias` (`id`, `categoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(10, 3, 'Estrutura', 1, NOW(), NOW());

-- Contas (Escritório)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Aluguel', 1, NOW(), NOW()),
(1, 'Água', 1, NOW(), NOW()),
(1, 'Energia Elétrica', 1, NOW(), NOW()),
(1, 'Internet', 1, NOW(), NOW()),
(1, 'Telefonia', 1, NOW(), NOW());

-- Contas (Pessoal)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(2, 'Salários', 1, NOW(), NOW()),
(2, 'Pró-labore', 1, NOW(), NOW()),
(2, 'INSS', 1, NOW(), NOW()),
(2, 'FGTS', 1, NOW(), NOW()),
(2, 'Vale Transporte', 1, NOW(), NOW()),
(2, 'Vale Alimentação', 1, NOW(), NOW());

-- Contas (Financeiro)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(3, 'Honorários Contábeis', 1, NOW(), NOW()),
(3, 'Taxas Bancárias', 1, NOW(), NOW()),
(3, 'Sistema / ERP', 1, NOW(), NOW());

-- Contas (Frota Fixo)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(4, 'Seguro Veicular', 1, NOW(), NOW()),
(4, 'IPVA', 1, NOW(), NOW()),
(4, 'Rastreamento', 1, NOW(), NOW());

-- Contas (Escritório / Infraestrutura Variável)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(5, 'Reparos Hidráulicos', 1, NOW(), NOW()),
(5, 'Reparos Elétricos', 1, NOW(), NOW()),
(5, 'Manutenção Predial', 1, NOW(), NOW());

-- Contas (Frota Variável)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(6, 'Combustível', 1, NOW(), NOW()),
(6, 'Manutenção Veicular', 1, NOW(), NOW()),
(6, 'Pedágio / Estacionamento', 1, NOW(), NOW());

-- Contas (Operacional)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(7, 'Materiais Elétricos', 1, NOW(), NOW()),
(7, 'Materiais de CFTV', 1, NOW(), NOW()),
(7, 'Materiais de Ar-Condicionado', 1, NOW(), NOW()),
(7, 'Ferramentas', 1, NOW(), NOW());

-- Contas (Comercial)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(8, 'Comissões', 1, NOW(), NOW()),
(8, 'Deslocamento Comercial', 1, NOW(), NOW());

-- Contas (Impostos)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(9, 'ISS', 1, NOW(), NOW()),
(9, 'Simples Nacional', 1, NOW(), NOW());

-- Contas (Estrutura/Investimentos)
INSERT IGNORE INTO `contas` (`subcategoria_id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(10, 'Compra de Equipamentos', 1, NOW(), NOW()),
(10, 'Compra de Ferramentas', 1, NOW(), NOW());

-- ================================================
-- 3. VERIFICAÇÃO FINAL
-- ================================================

-- Contar registros criados
SELECT 'Centros de Custo' as Tabela, COUNT(*) as Total FROM centros_custo
UNION ALL
SELECT 'Categorias', COUNT(*) FROM categorias
UNION ALL
SELECT 'Subcategorias', COUNT(*) FROM subcategorias
UNION ALL
SELECT 'Contas', COUNT(*) FROM contas
UNION ALL
SELECT 'Fornecedores', COUNT(*) FROM fornecedores
UNION ALL
SELECT 'Contas Fixas a Pagar', COUNT(*) FROM contas_fixas_pagar
UNION ALL
SELECT 'Contas a Pagar', COUNT(*) FROM contas_pagar;

-- ================================================
-- FIM DO SCRIPT
-- ================================================

/*
NOTAS IMPORTANTES:

1. Este script cria todas as tabelas necessárias para o módulo de Contas a Pagar
2. Insere dados iniciais (categorias, subcategorias, contas)
3. Mantém integridade referencial com foreign keys
4. Usa IGNORE para evitar duplicação de dados em execuções repetidas

PRÓXIMOS PASSOS APÓS EXECUTAR:
1. Verificar se todas as tabelas foram criadas
2. Verificar se os dados iniciais foram inseridos
3. Testar a criação de fornecedores
4. Testar a criação de contas a pagar
5. Testar o dashboard financeiro

ROLLBACK (se necessário):
Para reverter, execute:
DROP TABLE IF EXISTS contas_pagar;
DROP TABLE IF EXISTS contas_fixas_pagar;
DROP TABLE IF EXISTS fornecedor_contatos;
DROP TABLE IF EXISTS fornecedores;
DROP TABLE IF EXISTS contas;
DROP TABLE IF EXISTS subcategorias;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS centros_custo;
*/

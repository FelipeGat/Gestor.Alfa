-- ====================================================================
-- Script SQL para criação da tabela de anexos de cobranças
-- Data: 29/01/2026
-- Descrição: Tabela para armazenar anexos (NF e Boleto) das cobranças
-- ====================================================================

-- Criar tabela cobranca_anexos
CREATE TABLE `cobranca_anexos` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cobranca_id` BIGINT UNSIGNED NOT NULL,
    `tipo` VARCHAR(255) NOT NULL COMMENT 'nf ou boleto',
    `nome_original` VARCHAR(255) NOT NULL,
    `nome_arquivo` VARCHAR(255) NOT NULL,
    `caminho` VARCHAR(255) NOT NULL,
    `tamanho` INT NOT NULL COMMENT 'Tamanho em bytes',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `cobranca_anexos_cobranca_id_foreign` (`cobranca_id`),
    CONSTRAINT `cobranca_anexos_cobranca_id_foreign` 
        FOREIGN KEY (`cobranca_id`) 
        REFERENCES `cobrancas` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Instruções de uso:
-- ====================================================================
-- 1. Faça backup do banco de dados antes de executar
-- 2. Execute este script no ambiente de produção
-- 3. Verifique se a tabela foi criada: SHOW TABLES LIKE 'cobranca_anexos';
-- 4. Verifique a estrutura: DESCRIBE cobranca_anexos;
-- ====================================================================

-- Para reverter (apenas se necessário):
-- DROP TABLE IF EXISTS `cobranca_anexos`;
-- ====================================================================

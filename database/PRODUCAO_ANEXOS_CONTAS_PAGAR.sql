-- ============================================================
-- SCRIPT DE PRODUÇÃO - SISTEMA DE ANEXOS PARA CONTAS A PAGAR
-- Data: 29/01/2026
-- Commit: eae93dd
-- ============================================================

-- Criação da tabela conta_pagar_anexos
CREATE TABLE IF NOT EXISTS `conta_pagar_anexos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `conta_pagar_id` bigint(20) UNSIGNED NOT NULL,
  `tipo` enum('nf','boleto') NOT NULL COMMENT 'nf = Nota Fiscal, boleto = Boleto',
  `nome_original` varchar(255) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `caminho` varchar(255) NOT NULL,
  `tamanho` bigint(20) UNSIGNED NOT NULL COMMENT 'Tamanho em bytes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conta_pagar_anexos_nome_arquivo_unique` (`nome_arquivo`),
  KEY `conta_pagar_anexos_conta_pagar_id_index` (`conta_pagar_id`),
  KEY `conta_pagar_anexos_tipo_index` (`tipo`),
  CONSTRAINT `conta_pagar_anexos_conta_pagar_id_foreign` 
    FOREIGN KEY (`conta_pagar_id`) 
    REFERENCES `contas_pagar` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VERIFICAÇÕES PÓS-INSTALAÇÃO
-- ============================================================

-- Verificar se a tabela foi criada corretamente
SELECT 'Tabela conta_pagar_anexos criada com sucesso!' AS status;

-- Verificar estrutura da tabela
DESCRIBE conta_pagar_anexos;

-- Verificar foreign keys
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'conta_pagar_anexos'
    AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ============================================================
-- NOTAS IMPORTANTES
-- ============================================================
-- 1. Certifique-se de que o diretório storage/app/public/anexos/contas_pagar existe
-- 2. Permissões do diretório devem ser 755 ou 775
-- 3. O link simbólico do storage deve estar configurado (php artisan storage:link)
-- 4. Limite de upload no php.ini: upload_max_filesize e post_max_size >= 10MB
-- ============================================================

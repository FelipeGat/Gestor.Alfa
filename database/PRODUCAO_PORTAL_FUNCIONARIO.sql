-- ============================================
-- SCRIPT SQL PARA PRODUÇÃO
-- Portal do Funcionário - Sistema de Gestão de Tempo
-- Data: 30/01/2026
-- ============================================

-- MIGRATION 1: Adicionar campos de tempo nos atendimentos
-- Arquivo: 2026_01_30_165533_add_tempo_fields_to_atendimentos_table.php
ALTER TABLE `atendimentos` 
ADD COLUMN `iniciado_em` TIMESTAMP NULL DEFAULT NULL AFTER `data_atendimento`,
ADD COLUMN `finalizado_em` TIMESTAMP NULL DEFAULT NULL AFTER `iniciado_em`,
ADD COLUMN `tempo_execucao_segundos` INT NOT NULL DEFAULT 0 AFTER `finalizado_em` COMMENT 'Tempo efetivo trabalhado',
ADD COLUMN `tempo_pausa_segundos` INT NOT NULL DEFAULT 0 AFTER `tempo_execucao_segundos` COMMENT 'Tempo total pausado',
ADD COLUMN `em_execucao` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tempo_pausa_segundos` COMMENT 'Flag se está rodando agora',
ADD COLUMN `em_pausa` TINYINT(1) NOT NULL DEFAULT 0 AFTER `em_execucao` COMMENT 'Flag se está em pausa';

-- ============================================
-- MIGRATION 2: Criar tabela de pausas
-- Arquivo: 2026_01_30_165732_create_atendimento_pausas_table.php
CREATE TABLE `atendimento_pausas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `atendimento_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `tipo_pausa` ENUM('almoco', 'deslocamento', 'material', 'fim_dia') NOT NULL,
  `iniciada_em` TIMESTAMP NOT NULL,
  `encerrada_em` TIMESTAMP NULL DEFAULT NULL,
  `tempo_segundos` INT NOT NULL DEFAULT 0,
  `foto_inicio_path` VARCHAR(255) NULL DEFAULT NULL,
  `foto_retorno_path` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `atendimento_pausas_atendimento_id_foreign` 
    FOREIGN KEY (`atendimento_id`) REFERENCES `atendimentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `atendimento_pausas_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para performance
CREATE INDEX `atendimento_pausas_atendimento_id_index` ON `atendimento_pausas` (`atendimento_id`);
CREATE INDEX `atendimento_pausas_user_id_index` ON `atendimento_pausas` (`user_id`);
CREATE INDEX `atendimento_pausas_encerrada_em_index` ON `atendimento_pausas` (`encerrada_em`);

-- ============================================
-- MIGRATION 3: Adicionar rastreamento de usuários
-- Arquivo: 2026_01_30_175635_add_user_tracking_to_atendimentos_and_pausas.php

-- Campos na tabela atendimentos
ALTER TABLE `atendimentos` 
ADD COLUMN `iniciado_por_user_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `iniciado_em`,
ADD COLUMN `finalizado_por_user_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `finalizado_em`,
ADD CONSTRAINT `atendimentos_iniciado_por_user_id_foreign` 
  FOREIGN KEY (`iniciado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `atendimentos_finalizado_por_user_id_foreign` 
  FOREIGN KEY (`finalizado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Campo na tabela atendimento_pausas
ALTER TABLE `atendimento_pausas` 
ADD COLUMN `retomado_por_user_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `encerrada_em`,
ADD CONSTRAINT `atendimento_pausas_retomado_por_user_id_foreign` 
  FOREIGN KEY (`retomado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- ============================================
-- VERIFICAÇÃO: Status 'finalizacao' já deve existir
-- Se não existir, será necessário adicionar ao ENUM da coluna status_atual
-- Verifique sua estrutura atual antes de executar:

-- SELECT COLUMN_TYPE 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'atendimentos' 
-- AND COLUMN_NAME = 'status_atual';

-- Se necessário, adicione 'finalizacao' ao ENUM:
-- ALTER TABLE `atendimentos` 
-- MODIFY COLUMN `status_atual` ENUM('aberto', 'em_atendimento', 'finalizacao', 'concluido', 'cancelado', 'pendente_cliente') NOT NULL;

-- ============================================
-- REGISTRO NA TABELA DE MIGRAÇÕES
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_01_30_165533_add_tempo_fields_to_atendimentos_table', (SELECT MAX(batch) + 1 FROM migrations m)),
('2026_01_30_165732_create_atendimento_pausas_table', (SELECT MAX(batch) + 1 FROM migrations m)),
('2026_01_30_175635_add_user_tracking_to_atendimentos_and_pausas', (SELECT MAX(batch) + 1 FROM migrations m));

-- ============================================
-- NOTAS IMPORTANTES:
-- ============================================
-- 1. Este script adiciona campos e tabelas necessários para o Portal do Funcionário
-- 2. O status 'finalizacao' é usado quando técnico finaliza (antes da aprovação do gerente)
-- 3. Apenas atendimentos com status 'concluido' aparecem no portal do cliente
-- 4. As fotos são armazenadas em storage/app/public/atendimentos/fotos e pausas
-- 5. Certifique-se de que o symbolic link storage existe: php artisan storage:link
-- 6. Execute este script em ordem, de cima para baixo
-- 7. Faça backup do banco antes de executar em produção!

-- ============================================
-- VALIDAÇÃO PÓS-EXECUÇÃO:
-- ============================================
-- Verificar estrutura criada:
-- DESCRIBE atendimentos;
-- DESCRIBE atendimento_pausas;
-- 
-- Verificar constraints:
-- SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME 
-- FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME IN ('atendimentos', 'atendimento_pausas');

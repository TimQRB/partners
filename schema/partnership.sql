-- БД для лендинга «Заявки на сотрудничество»
-- В MySQL Workbench: открыть этот файл и выполнить (Execute, Ctrl+Shift+Enter)
-- Или из консоли: mysql -u root -p < schema/partnership.sql

CREATE DATABASE IF NOT EXISTS yii1_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yii1_db;

CREATE TABLE IF NOT EXISTS `tbl_partnership` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `org_name` varchar(255) NOT NULL DEFAULT '',
  `org_name_en` varchar(255) DEFAULT '',
  `org_type` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(100) NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `website` varchar(255) DEFAULT '',
  `contact_name` varchar(255) NOT NULL DEFAULT '',
  `contact_position` varchar(255) DEFAULT '',
  `contact_email` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(255) DEFAULT '',
  `contact_method` varchar(100) DEFAULT '',
  `cooperation_directions` text,
  `description` text,
  `description_en` text,
  `activity_areas` text,
  `interaction_format` text,
  `subtasks` text,
  `subtasks_en` text,
  `goals` text,
  `goals_en` text,
  `events` text,
  `materials` text,
  `file_path` varchar(500) DEFAULT NULL,
  `description_images` text,
  `data_consent` tinyint(1) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(128) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO `tbl_user` (`username`, `password`, `email`, `created_at`, `updated_at`)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ku.edu.kz', NOW(), NOW());

-- ============================================================
-- Миграция: добавить EN-столбцы в существующую таблицу
-- Выполнить один раз, если таблица уже создана без них:
-- ============================================================
-- ALTER TABLE `tbl_partnership` ADD COLUMN `org_name_en` VARCHAR(255) DEFAULT '' AFTER `org_name`;
-- ALTER TABLE `tbl_partnership` ADD COLUMN `description_en` TEXT NULL AFTER `description`;
-- ALTER TABLE `tbl_partnership` ADD COLUMN `subtasks_en` TEXT NULL AFTER `subtasks`;
-- ALTER TABLE `tbl_partnership` ADD COLUMN `goals_en` TEXT NULL AFTER `goals`;
-- ALTER TABLE `tbl_partnership` ADD COLUMN `description_images` TEXT NULL AFTER `file_path`;

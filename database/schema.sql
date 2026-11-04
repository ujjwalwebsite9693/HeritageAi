-- Manuscript Intelligence Archive (MIA)
-- Database Schema for MySQL 8.x

CREATE DATABASE IF NOT EXISTS `manuscript_archive` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `manuscript_archive`;

-- 1. Users Table (Visitor, Registered User, Researcher/Editor, Admin)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('visitor', 'user', 'researcher', 'admin') NOT NULL DEFAULT 'user',
  `api_key` VARCHAR(64) NULL UNIQUE,
  `institution` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Languages
CREATE TABLE IF NOT EXISTS `languages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL,
  `script` VARCHAR(80) NOT NULL,
  `iso_code` VARCHAR(10) NOT NULL,
  `direction` ENUM('ltr', 'rtl') NOT NULL DEFAULT 'ltr'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Places (Ancient vs Modern Name, Coordinates, Region)
CREATE TABLE IF NOT EXISTS `places` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ancient_name` VARCHAR(150) NOT NULL,
  `modern_name` VARCHAR(150) NOT NULL,
  `lat` DECIMAL(10, 7) NOT NULL,
  `lng` DECIMAL(10, 7) NOT NULL,
  `region` VARCHAR(150) NULL,
  `country` VARCHAR(100) NOT NULL DEFAULT 'India',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_places_coords` (`lat`, `lng`),
  INDEX `idx_places_names` (`ancient_name`, `modern_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Manuscripts
CREATE TABLE IF NOT EXISTS `manuscripts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(200) NULL DEFAULT 'Unknown / Anonymous',
  `language_id` INT UNSIGNED NOT NULL,
  `script` VARCHAR(100) NOT NULL,
  `era_start` INT NOT NULL, -- sortable year (e.g. -250 for 250 BCE)
  `era_end` INT NULL,
  `origin_place_id` INT UNSIGNED NULL,
  `source_repository` VARCHAR(255) NOT NULL,
  `call_number` VARCHAR(100) NULL,
  `license` VARCHAR(100) NOT NULL DEFAULT 'Public Domain (Creative Commons Zero)',
  `access_level` ENUM('public', 'restricted', 'private') NOT NULL DEFAULT 'public',
  `status` ENUM('draft', 'processing', 'archived', 'published') NOT NULL DEFAULT 'published',
  `uploaded_by` INT UNSIGNED NULL,
  `thumbnail` VARCHAR(255) NULL,
  `summary` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FULLTEXT KEY `ft_manuscript_search` (`title`, `author`, `summary`),
  INDEX `idx_era` (`era_start`, `era_end`),
  INDEX `idx_access` (`access_level`, `status`),
  CONSTRAINT `fk_manuscripts_lang` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_manuscripts_place` FOREIGN KEY (`origin_place_id`) REFERENCES `places` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_manuscripts_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Manuscript Pages
CREATE TABLE IF NOT EXISTS `manuscript_pages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `manuscript_id` INT UNSIGNED NOT NULL,
  `page_no` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `ocr_text` LONGTEXT NULL,
  `ocr_confidence` DECIMAL(5, 2) NULL DEFAULT 0.00,
  `transcription` LONGTEXT NULL,
  `transliteration` LONGTEXT NULL,
  `status` ENUM('ocr_draft', 'corrected', 'verified') NOT NULL DEFAULT 'ocr_draft',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_page` (`manuscript_id`, `page_no`),
  FULLTEXT KEY `ft_pages_text` (`ocr_text`, `transcription`),
  CONSTRAINT `fk_pages_manuscript` FOREIGN KEY (`manuscript_id`) REFERENCES `manuscripts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Translations (Side-by-side, Draft/Verified, Versioning)
CREATE TABLE IF NOT EXISTS `translations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `page_id` INT UNSIGNED NOT NULL,
  `target_lang` VARCHAR(10) NOT NULL DEFAULT 'en', -- 'en', 'hi', etc.
  `text` LONGTEXT NOT NULL,
  `method` ENUM('ai', 'human') NOT NULL DEFAULT 'ai',
  `status` ENUM('draft', 'verified') NOT NULL DEFAULT 'draft',
  `reviewer_id` INT UNSIGNED NULL,
  `version` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FULLTEXT KEY `ft_translation_text` (`text`),
  CONSTRAINT `fk_translations_page` FOREIGN KEY (`page_id`) REFERENCES `manuscript_pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_translations_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Glossary (Word -> Hindi & English Meanings + Tooltip Lookup)
CREATE TABLE IF NOT EXISTS `glossary` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `term` VARCHAR(150) NOT NULL,
  `language_id` INT UNSIGNED NOT NULL,
  `meaning_hi` TEXT NOT NULL,
  `meaning_en` TEXT NOT NULL,
  `notes` TEXT NULL,
  INDEX `idx_term` (`term`),
  CONSTRAINT `fk_glossary_lang` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Persons & Historical Figures
CREATE TABLE IF NOT EXISTS `persons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(180) NOT NULL,
  `alt_names` TEXT NULL, -- comma or JSON aliases
  `birth_year` INT NULL,
  `death_year` INT NULL,
  `role` VARCHAR(150) NOT NULL, -- King, Courtier, Scholar, Philosopher, Monk, Merchant, etc.
  `bio` TEXT NULL,
  `portrait_url` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_person_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Person Relations (Genealogy & Historical Relationships)
CREATE TABLE IF NOT EXISTS `person_relations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `person_a` INT UNSIGNED NOT NULL,
  `person_b` INT UNSIGNED NOT NULL,
  `relation_type` VARCHAR(80) NOT NULL, -- 'parent-child', 'guru-shishya', 'ally', 'rival', 'patron', 'minister-king'
  `source_manuscript_id` INT UNSIGNED NULL,
  `notes` VARCHAR(255) NULL,
  CONSTRAINT `fk_rel_person_a` FOREIGN KEY (`person_a`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rel_person_b` FOREIGN KEY (`person_b`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rel_manuscript` FOREIGN KEY (`source_manuscript_id`) REFERENCES `manuscripts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Events (Historical Events extracted from manuscripts)
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `date_text` VARCHAR(100) NOT NULL, -- e.g. "c. 261 BCE", "14th Regnal Year", "1582 CE"
  `sort_year` INT NOT NULL, -- normalized BCE/CE integer
  `end_year` INT NULL,
  `calendar_type` ENUM('Gregorian', 'Vikram Samvat', 'Shaka', 'Hijri', 'Regnal') NOT NULL DEFAULT 'Gregorian',
  `place_id` INT UNSIGNED NULL,
  `category` ENUM('war', 'treaty', 'trade', 'religion', 'politics', 'culture', 'science') NOT NULL DEFAULT 'politics',
  `confidence` DECIMAL(4, 2) NOT NULL DEFAULT 0.90,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
  INDEX `idx_events_sort_year` (`sort_year`),
  INDEX `idx_events_category` (`category`),
  CONSTRAINT `fk_events_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Event Sources (Linking Events to specific Manuscripts & Pages)
CREATE TABLE IF NOT EXISTS `event_sources` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT UNSIGNED NOT NULL,
  `manuscript_id` INT UNSIGNED NOT NULL,
  `page_id` INT UNSIGNED NULL,
  `quote_ref` TEXT NULL,
  CONSTRAINT `fk_es_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_es_manuscript` FOREIGN KEY (`manuscript_id`) REFERENCES `manuscripts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_es_page` FOREIGN KEY (`page_id`) REFERENCES `manuscript_pages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Entities (Persons, Places, Orgs, Dates, Concepts extracted)
CREATE TABLE IF NOT EXISTS `entities` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('person', 'place', 'org', 'object', 'concept') NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `normalized_name` VARCHAR(180) NOT NULL,
  `ref_id` INT UNSIGNED NULL, -- link to persons.id or places.id
  `merged_into` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_entity_norm` (`normalized_name`),
  INDEX `idx_entity_type` (`type`),
  CONSTRAINT `fk_entities_merged` FOREIGN KEY (`merged_into`) REFERENCES `entities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Entity Mentions
CREATE TABLE IF NOT EXISTS `entity_mentions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `entity_id` INT UNSIGNED NOT NULL,
  `manuscript_id` INT UNSIGNED NOT NULL,
  `page_id` INT UNSIGNED NULL,
  `position` INT UNSIGNED NULL,
  `context_snippet` TEXT NULL,
  CONSTRAINT `fk_em_entity` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_em_manuscript` FOREIGN KEY (`manuscript_id`) REFERENCES `manuscripts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_em_page` FOREIGN KEY (`page_id`) REFERENCES `manuscript_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Links (Cross-Manuscript Connections / Hidden Historical Links)
CREATE TABLE IF NOT EXISTS `links` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `manuscript_a` INT UNSIGNED NOT NULL,
  `manuscript_b` INT UNSIGNED NOT NULL,
  `score` INT UNSIGNED NOT NULL, -- 0 to 100
  `reason_json` JSON NOT NULL, -- explanation, shared entities, similarity metrics
  `status` ENUM('suggested', 'confirmed', 'rejected') NOT NULL DEFAULT 'suggested',
  `reviewed_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_links_score` (`score`),
  CONSTRAINT `fk_links_doc_a` FOREIGN KEY (`manuscript_a`) REFERENCES `manuscripts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_links_doc_b` FOREIGN KEY (`manuscript_b`) REFERENCES `manuscripts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_links_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Annotations (Region & Text Selection with Threaded Comments)
CREATE TABLE IF NOT EXISTS `annotations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `page_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('question', 'correction', 'reference', 'hypothesis') NOT NULL DEFAULT 'reference',
  `coordinates` VARCHAR(255) NULL, -- 'x,y,w,h' for image bounds or 'start:end' for text
  `note` TEXT NOT NULL,
  `visibility` ENUM('public', 'private') NOT NULL DEFAULT 'public',
  `parent_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_ann_page` (`page_id`),
  CONSTRAINT `fk_ann_page` FOREIGN KEY (`page_id`) REFERENCES `manuscript_pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ann_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ann_parent` FOREIGN KEY (`parent_id`) REFERENCES `annotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Background Jobs Queue (OCR, Translations, Entity Resolution, Link Discovery)
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(80) NOT NULL, -- 'ocr_process', 'ai_translate', 'entity_extract', 'link_discovery'
  `payload` JSON NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `error_message` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_jobs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Audit Logs (Scholarly changes, merges, approvals)
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(80) NOT NULL,
  `target_id` INT UNSIGNED NOT NULL,
  `details` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


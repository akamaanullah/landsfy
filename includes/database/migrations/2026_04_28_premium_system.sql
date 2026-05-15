-- Migration: Premium Listing Quota System

-- 1. Add quota fields to agents
ALTER TABLE `agents` 
ADD COLUMN `platinum_quota` INT(11) DEFAULT 0 AFTER `experience_years`,
ADD COLUMN `platinum_used` INT(11) DEFAULT 0 AFTER `platinum_quota`,
ADD COLUMN `diamond_quota` INT(11) DEFAULT 0 AFTER `platinum_used`,
ADD COLUMN `diamond_used` INT(11) DEFAULT 0 AFTER `diamond_quota`;

-- 2. Add premium status to properties
ALTER TABLE `properties`
ADD COLUMN `premium_type` ENUM('none', 'platinum', 'diamond') DEFAULT 'none' AFTER `is_featured`,
ADD COLUMN `premium_status` ENUM('none', 'pending', 'active', 'expired') DEFAULT 'none' AFTER `premium_type`,
ADD COLUMN `premium_expiry` DATETIME DEFAULT NULL AFTER `premium_status`;

-- 3. Create premium requests table for manual approvals
CREATE TABLE IF NOT EXISTS `premium_requests` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `property_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `request_type` ENUM('platinum_credit', 'diamond_credit', 'direct_upgrade') NOT NULL,
  `amount_paid` DECIMAL(15, 2) NOT NULL,
  `payment_screenshot` VARCHAR(512) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `admin_note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_premium_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_premium_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

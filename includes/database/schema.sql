-- Landsfy Final Dynamic Database Schema
-- Perfectly aligned with Admin Property-Config and Seller Add-Listing UIs

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Identity & Access Management
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar_url` VARCHAR(512) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `role_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`role_name`) VALUES ('admin'), ('agency_owner'), ('agent'), ('seller'), ('buyer');

-- --------------------------------------------------------
-- 2. Configuration Management (Dynamic & UI-Driven)
-- --------------------------------------------------------

-- Geography Configuration
CREATE TABLE `cities` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `is_popular` BOOLEAN DEFAULT FALSE,
  `sort_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `city_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `locations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `city_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_location_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Classification
CREATE TABLE `property_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `icon_class` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_subtypes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `icon_class` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subtype_slug_unique` (`slug`),
  CONSTRAINT `fk_subtype_category` FOREIGN KEY (`category_id`) REFERENCES `property_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Amenities & Features Architecture
CREATE TABLE `amenity_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `icon_class` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `amenity_fields` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `field_type` ENUM('switch', 'dropdown', 'number_group', 'text_input', 'number') DEFAULT 'switch',
  `options` JSON DEFAULT NULL, -- Comma-separated or JSON list of options
  `is_required` BOOLEAN DEFAULT FALSE,
  `icon_class` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `sort_order` INT(11) DEFAULT 0,
  `context` ENUM('all', 'home', 'plot', 'commercial') DEFAULT 'all',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_amenity_group` FOREIGN KEY (`group_id`) REFERENCES `amenity_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Agency & Agent Management
-- --------------------------------------------------------

CREATE TABLE `agencies` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` BIGINT(20) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `logo_url` VARCHAR(512) DEFAULT NULL,
  `banner_url` VARCHAR(512) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `social_links` JSON DEFAULT NULL,
  `is_verified` BOOLEAN DEFAULT FALSE,
  `is_premium` BOOLEAN DEFAULT FALSE,
  `status` ENUM('active', 'under_review', 'under_watch', 'suspended') DEFAULT 'under_review',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_agencies_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agents` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `agency_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `specialization` VARCHAR(255) DEFAULT NULL,
  `license_number` VARCHAR(100) DEFAULT NULL,
  `experience_years` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_agents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agents_agency` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Property Management
-- --------------------------------------------------------

CREATE TABLE `properties` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `author_id` BIGINT(20) UNSIGNED NOT NULL,
  `agency_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `category_id` INT(11) NOT NULL,
  `subtype_id` INT(11) NOT NULL,
  `city_id` INT(11) NOT NULL,
  `location_id` INT(11) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` LONGTEXT DEFAULT NULL,
  `price` DECIMAL(15, 2) NOT NULL,
  `purpose` ENUM('sell', 'rent') DEFAULT 'sell',
  `status` ENUM('active', 'sold', 'under_review', 'inactive', 'rejected') DEFAULT 'under_review',
  `rejection_reason` TEXT DEFAULT NULL,
  `area_size` DECIMAL(10, 2) NOT NULL,
  `area_unit` ENUM('kanal', 'marla', 'sqft', 'sqyrd') NOT NULL,
  `is_installment_available` BOOLEAN DEFAULT FALSE,
  `is_ready_for_possession` BOOLEAN DEFAULT FALSE,
  `video_url` VARCHAR(512) DEFAULT NULL,
  `is_featured` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `properties_slug_unique` (`slug`),
  KEY `properties_price_index` (`price`),
  CONSTRAINT `fk_properties_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_properties_agency` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_properties_category` FOREIGN KEY (`category_id`) REFERENCES `property_categories` (`id`),
  CONSTRAINT `fk_properties_subtype` FOREIGN KEY (`subtype_id`) REFERENCES `property_subtypes` (`id`),
  CONSTRAINT `fk_properties_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  CONSTRAINT `fk_properties_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_amenity_values` (
  `property_id` BIGINT(20) UNSIGNED NOT NULL,
  `amenity_field_id` INT(11) NOT NULL,
  `value` TEXT DEFAULT NULL,
  PRIMARY KEY (`property_id`, `amenity_field_id`),
  CONSTRAINT `fk_val_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_val_amenity` FOREIGN KEY (`amenity_field_id`) REFERENCES `amenity_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_images` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` BIGINT(20) UNSIGNED NOT NULL,
  `image_url` VARCHAR(512) NOT NULL,
  `is_main` BOOLEAN DEFAULT FALSE,
  `sort_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_images_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_contacts` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` BIGINT(20) UNSIGNED NOT NULL,
  `phone_number` VARCHAR(20) NOT NULL,
  `label` VARCHAR(50) DEFAULT 'Mobile',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_contacts_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Engagement & Leads
-- --------------------------------------------------------

CREATE TABLE `leads` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` BIGINT(20) UNSIGNED NOT NULL,
  `buyer_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `sender_name` VARCHAR(255) DEFAULT NULL,
  `sender_email` VARCHAR(255) DEFAULT NULL,
  `sender_phone` VARCHAR(20) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `status` ENUM('new', 'contacted', 'viewing_scheduled', 'sold', 'closed', 'rejected') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_leads_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leads_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_stats` (
  `property_id` BIGINT(20) UNSIGNED NOT NULL,
  `views_total` INT(11) DEFAULT 0,
  `leads_total` INT(11) DEFAULT 0,
  `last_viewed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`property_id`),
  CONSTRAINT `fk_stats_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Initial Seed Data (UI-Synced)
-- --------------------------------------------------------

INSERT INTO `cities` (`name`, `slug`, `is_popular`) VALUES
('Karachi', 'karachi', TRUE),
('Lahore', 'lahore', TRUE),
('Islamabad', 'islamabad', TRUE),
('Rawalpindi', 'rawalpindi', FALSE),
('Faisalabad', 'faisalabad', FALSE);

INSERT INTO `property_categories` (`id`, `name`, `slug`, `icon_class`, `sort_order`) VALUES
(1, 'Home', 'home', 'ph-house', 1),
(2, 'Plots', 'plots', 'ph-map-trifold', 2),
(3, 'Commercial', 'commercial', 'ph-buildings', 3);

INSERT INTO `property_subtypes` (`category_id`, `name`, `slug`, `icon_class`, `sort_order`) VALUES
-- Home Subtypes
(1, 'House', 'house', 'ph-house', 1),
(1, 'Flat', 'flat', 'ph-buildings', 2),
(1, 'Upper Portion', 'upper-portion', 'ph-stairs', 3),
(1, 'Lower Portion', 'lower-portion', 'ph-stairs', 4),
(1, 'Farm House', 'farm-house', 'ph-tree', 5),
(1, 'Room', 'room', 'ph-door', 6),
(1, 'Penthouse', 'penthouse', 'ph-stack', 7),
-- Plot Subtypes
(2, 'Residential Plot', 'residential-plot', 'ph-map-pin', 1),
(2, 'Commercial Plot', 'commercial-plot', 'ph-buildings', 2),
(2, 'Agricultural Land', 'agricultural-land', 'ph-leaf', 3),
(2, 'Industrial Land', 'industrial-land', 'ph-factory', 4),
-- Commercial Subtypes
(3, 'Office', 'office', 'ph-briefcase', 1),
(3, 'Shop', 'shop', 'ph-storefront', 2),
(3, 'Warehouse', 'warehouse', 'ph-warehouse', 3),
(3, 'Building', 'building', 'ph-buildings', 4);

INSERT INTO `amenity_groups` (`id`, `name`, `icon_class`, `sort_order`) VALUES
(1, 'Main Features', 'ph-star', 1),
(2, 'Rooms', 'ph-door', 2),
(3, 'Community Features', 'ph-users', 3),
(4, 'Healthcare Recreational', 'ph-first-aid', 4),
(5, 'Business and Communication', 'ph-broadcast', 5),
(6, 'Plot Features', 'ph-squares-four', 6);

INSERT INTO `amenity_fields` (`group_id`, `label`, `field_type`, `options`, `is_required`, `icon_class`, `context`) VALUES
(1, 'Built in year', 'number', NULL, FALSE, 'ph-calendar', 'home'),
(1, 'Parking Spaces', 'number', NULL, FALSE, 'ph-car', 'all'),
(2, 'Bedrooms', 'number_group', '["1", "2", "3", "4", "5", "6", "7", "8", "9", "10+"]', TRUE, 'ph-bed', 'home'),
(2, 'Bathrooms', 'number_group', '["1", "2", "3", "4", "5", "6", "7+"]', TRUE, 'ph-bathtub', 'home'),
(3, 'Mosque', 'switch', NULL, FALSE, 'ph-mosque', 'all'),
(6, 'Corner Plot', 'switch', NULL, FALSE, 'ph-selection-plus', 'plot'),
(6, 'Boundary Wall', 'switch', NULL, FALSE, 'ph-wall', 'plot');

-- --------------------------------------------------------
-- 7. Settings & Audit Logging
-- --------------------------------------------------------

CREATE TABLE `user_settings` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_setting_unique` (`user_id`, `setting_key`),
  CONSTRAINT `fk_user_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_reviews` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `agent_id` BIGINT(20) UNSIGNED NOT NULL,
  `reviewer_id` BIGINT(20) UNSIGNED NOT NULL,
  `rating` DECIMAL(2, 1) NOT NULL,
  `review_text` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_reviews_agent` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_interactions` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` BIGINT(20) UNSIGNED NOT NULL,
  `user_id` BIGINT(20) UNSIGNED DEFAULT NULL, -- Nullable if guest
  `interaction_type` ENUM('view', 'whatsapp_click', 'call_reveal', 'share') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_interactions_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_interactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `action_type` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `target_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agency_documents` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `agency_id` BIGINT(20) UNSIGNED NOT NULL,
  `document_type` VARCHAR(100) NOT NULL,
  `document_url` VARCHAR(512) NOT NULL,
  `status` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_docs_agency` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

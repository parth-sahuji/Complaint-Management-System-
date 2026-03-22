-- ============================================================
--  Online Complaint Management System
--  Database  : complaint_system
--  Engine    : InnoDB  |  Charset : utf8mb4
--  Compatible: MySQL 5.7+ / MariaDB 10.3+ / phpMyAdmin / XAMPP
--  Generated : 2026-03-21
-- ============================================================


-- ------------------------------------------------------------
-- 0. DATABASE
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `complaint_system`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `complaint_system`;


-- ============================================================
-- TABLE DEFINITIONS
-- ============================================================


-- ------------------------------------------------------------
-- (A) roles
-- ------------------------------------------------------------
CREATE TABLE `roles` (
    `id`        TINYINT     UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_name` VARCHAR(20) NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_roles_role_name` (`role_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Stores the three system roles: Admin, Staff, User';


-- ------------------------------------------------------------
-- (B) users
-- ------------------------------------------------------------
CREATE TABLE `users` (
    `id`         INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL,
    `password`   VARCHAR(255) NOT NULL   COMMENT 'Store bcrypt / password_hash() output',
    `phone`      VARCHAR(20)  DEFAULT NULL,
    `role_id`    TINYINT      UNSIGNED NOT NULL DEFAULT 3
                              COMMENT '3 = User (default on self-registration)',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role_id`    (`role_id`),

    CONSTRAINT `fk_users_role`
        FOREIGN KEY (`role_id`)
        REFERENCES `roles` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'All system users (Admin, Staff, and public Users)';


-- ------------------------------------------------------------
-- (C) categories
-- ------------------------------------------------------------
CREATE TABLE `categories` (
    `id`            TINYINT     UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_name` VARCHAR(60) NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_name` (`category_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Predefined complaint categories — managed by Admin only';


-- ------------------------------------------------------------
-- (D) complaints
-- ------------------------------------------------------------
CREATE TABLE `complaints` (
    `id`          INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT          UNSIGNED NOT NULL COMMENT 'Complainant (role = User)',
    `category_id` TINYINT      UNSIGNED NOT NULL,
    `assigned_to` INT          UNSIGNED NOT NULL COMMENT 'Staff / Admin auto-assigned by category',
    `title`       VARCHAR(200) NOT NULL,
    `description` TEXT         NOT NULL,
    `location`    TEXT         NOT NULL COMMENT 'Full text address supplied by the user',
    `status`      ENUM('Submitted','In Progress','Completed')
                              NOT NULL DEFAULT 'Submitted',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_complaints_user_id`     (`user_id`),
    KEY `idx_complaints_category_id` (`category_id`),
    KEY `idx_complaints_assigned_to` (`assigned_to`),
    KEY `idx_complaints_status`      (`status`),

    CONSTRAINT `fk_complaints_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT `fk_complaints_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT `fk_complaints_assigned`
        FOREIGN KEY (`assigned_to`)
        REFERENCES `users` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Core complaints table — no deletes, no edits after submission';


-- ------------------------------------------------------------
-- (E) complaint_images
--     Rule: max 2 images per complaint (enforced in PHP layer)
-- ------------------------------------------------------------
CREATE TABLE `complaint_images` (
    `id`           INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    `complaint_id` INT          UNSIGNED NOT NULL,
    `image_path`   VARCHAR(500) NOT NULL COMMENT 'Relative or absolute server path to the uploaded file',

    PRIMARY KEY (`id`),
    KEY `idx_complaint_images_complaint_id` (`complaint_id`),

    CONSTRAINT `fk_images_complaint`
        FOREIGN KEY (`complaint_id`)
        REFERENCES `complaints` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Stores 1–2 image paths per complaint';


-- ============================================================
-- SEED DATA
-- ============================================================


-- ------------------------------------------------------------
-- Roles  (id values are stable — referenced as constants in PHP)
-- ------------------------------------------------------------
INSERT INTO `roles` (`id`, `role_name`) VALUES
    (1, 'Admin'),
    (2, 'Staff'),
    (3, 'User');


-- ------------------------------------------------------------
-- Categories  (predefined — 11 entries)
-- ------------------------------------------------------------
INSERT INTO `categories` (`category_name`) VALUES
    ('Infrastructure'),
    ('Road & Transport'),
    ('Water Supply'),
    ('Electricity'),
    ('Cleanliness'),
    ('Waste Management'),
    ('Washroom / Sanitation'),
    ('Staff Behavior'),
    ('Construction Issues'),
    ('Illegal Activities / Bribery'),
    ('Other');


-- ============================================================
-- END OF SCHEMA
-- ============================================================

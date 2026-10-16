-- Run this script once in the Tapuyan_NHS_IMS database.
CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'General',
    body TEXT NOT NULL,
    external_url VARCHAR(2048) NULL,
    file_name VARCHAR(255) NULL,
    file_data LONGBLOB NULL,
    mime_type VARCHAR(150) NULL,
    image_name VARCHAR(255) NULL,
    image_data LONGBLOB NULL,
    image_mime_type VARCHAR(150) NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (announcement_id),
    KEY idx_announcements_published (is_published, created_at),
    CONSTRAINT fk_announcements_admin FOREIGN KEY (created_by) REFERENCES admin(EmployeeID) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multiple images per announcement (up to 10, enforced in application code).
CREATE TABLE IF NOT EXISTS announcement_images (
    image_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    announcement_id INT UNSIGNED NOT NULL,
    image_name VARCHAR(255) NULL,
    image_data LONGBLOB NULL,
    image_mime_type VARCHAR(150) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (image_id),
    KEY idx_announcement_images_announcement (announcement_id, sort_order, image_id),
    CONSTRAINT fk_announcement_images_announcement FOREIGN KEY (announcement_id)
        REFERENCES announcements (announcement_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One-time migration: copy legacy single images into the new gallery table.
-- Safe to run multiple times; skips announcements that already have gallery rows.
INSERT INTO announcement_images (announcement_id, image_name, image_data, image_mime_type, sort_order)
SELECT a.announcement_id, a.image_name, a.image_data, a.image_mime_type, 0
FROM announcements a
WHERE a.image_data IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM announcement_images gi WHERE gi.announcement_id = a.announcement_id
  );

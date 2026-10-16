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

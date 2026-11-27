CREATE TABLE IF NOT EXISTS order_email_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    recipient_email VARCHAR(190) NULL,
    status ENUM ('pending', 'sending', 'sent', 'failed', 'skipped') NOT NULL DEFAULT 'pending',
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    last_error VARCHAR(255) NULL,
    last_attempt_at DATETIME NULL,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_email_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    UNIQUE KEY uq_order_email_order (order_id),
    INDEX idx_order_email_status (status, last_attempt_at)
) ENGINE = InnoDB;

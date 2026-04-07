-- Key/value app settings (SEO, WhatsApp, SMTP, etc.)
-- Run once on existing databases: import into vk_billing (backup first).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key_name VARCHAR(128) NOT NULL,
  value TEXT,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_settings_key (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (key_name, value) VALUES
('site_name', 'VK Network'),
('seo_site_title', ''),
('seo_meta_description', 'Professional computer, printer, CCTV, maintenance, and field repair services in Kilinochchi and across Sri Lanka — VK Network.'),
('seo_meta_keywords', 'computer repair, laptop service, printer repair, CCTV installation, Sri Lanka, Kilinochchi, VK Network'),
('seo_og_image', ''),
('seo_auto_enabled', '1'),
('seo_locations', 'jaffna,vavuniya,kilinochchi'),
('seo_service_slugs', 'computer-repair,laptop-repair,printer-repair,it-service'),
('whatsapp_number', ''),
('whatsapp_default_message', 'Hello VK Network, I would like to inquire about your services.'),
('analytics_domain', ''),
('analytics_script_src', 'https://plausible.io/js/script.js'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('email_from', '');

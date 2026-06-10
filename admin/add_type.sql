ALTER TABLE certificates
ADD COLUMN `main_type` ENUM('IAF', 'Non-IAF') DEFAULT 'Non-IAF' AFTER `iso_standard`;

CREATE DATABASE IF NOT EXISTS `logistics`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'logist'@'%' IDENTIFIED BY 'logist_pass';
GRANT ALL PRIVILEGES ON `logistics`.* TO 'logist'@'%';
FLUSH PRIVILEGES;

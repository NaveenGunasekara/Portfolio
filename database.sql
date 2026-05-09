-- Portfolio CMS schema + sample data (MySQL 5.7+ / MariaDB)
-- Import via phpMyAdmin or:
--   mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS portfolio_cms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE portfolio_cms;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS music_items;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS site_about;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(64) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_about (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  headline VARCHAR(255) NOT NULL,
  subtitle VARCHAR(255) NOT NULL DEFAULT '',
  bio TEXT NOT NULL,
  skills VARCHAR(512) NOT NULL DEFAULT '',
  image_path VARCHAR(512) NOT NULL DEFAULT 'assets/img/profile-placeholder.svg',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  summary VARCHAR(400) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  image_path VARCHAR(512) NOT NULL DEFAULT 'assets/img/project-placeholder.svg',
  demo_url VARCHAR(512) NOT NULL DEFAULT '',
  github_url VARCHAR(512) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_projects_sort (sort_order),
  KEY idx_projects_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE music_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  artist VARCHAR(200) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  image_path VARCHAR(512) NOT NULL DEFAULT 'assets/img/music-placeholder.svg',
  external_url VARCHAR(512) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_music_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  user_agent VARCHAR(512) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_created (created_at),
  KEY idx_contact_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Single-row About content
INSERT INTO site_about (id, headline, subtitle, bio, skills, image_path)
VALUES (
  1,
  'Naveen — Full-stack builder',
  'Dark-mode portfolios, clean PHP architecture, and expressive UI.',
  'I design and ship production-ready web experiences with careful attention to performance, accessibility, and maintainability. This site is powered by a lightweight PHP CMS you can extend.',
  'PHP · MySQL · HTML/CSS · JavaScript · System design',
  'assets/img/profile-placeholder.svg'
);

-- Sample projects
INSERT INTO projects (title, summary, description, image_path, demo_url, github_url, sort_order)
VALUES
(
  'Atlas Dashboard',
  'Analytics UI kit with modular widgets.',
  'A responsive dashboard layout featuring charts, KPI cards, and theme tokens. Built to demonstrate component thinking without a heavy framework.',
  'assets/img/project-placeholder.svg',
  'https://example.com',
  'https://github.com/example/atlas-dashboard',
  10
),
(
  'Echo API Gateway',
  'Edge-friendly routing and observability hooks.',
  'A conceptual gateway service describing rate limits, structured logs, and graceful degradation patterns suitable for shared hosting and VPS deployments alike.',
  'assets/img/project-placeholder.svg',
  'https://example.com',
  'https://github.com/example/echo-gateway',
  20
),
(
  'Lumen Notes',
  'Minimal notes app with optimistic UI flows.',
  'Focus on instant feedback, offline-first hints, and readable typography. Designed as a portfolio piece for interaction polish.',
  'assets/img/project-placeholder.svg',
  '',
  'https://github.com/example/lumen-notes',
  30
);

-- Sample music gallery entries
INSERT INTO music_items (title, artist, description, image_path, external_url, sort_order)
VALUES
(
  'Midnight Signal',
  'NVN',
  'Analog-inspired synth textures layered over breakbeats. Placeholder entry for your discography or playlists.',
  'assets/img/music-placeholder.svg',
  'https://example.com/music/midnight-signal',
  10
),
(
  'Glass Harbour',
  'NVN',
  'Ambient progression with subtle vocal chops — swap links to Spotify, SoundCloud, or Bandcamp.',
  'assets/img/music-placeholder.svg',
  'https://example.com/music/glass-harbour',
  20
);

-- Admins table intentionally empty on import; first request seeds admin/admin via bootstrap when empty.
-- After login, change the password from the database or add a password-change screen later.

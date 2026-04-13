CREATE TABLE IF NOT EXISTS tbl_partnership (
  id SERIAL PRIMARY KEY,
  org_name VARCHAR(255) NOT NULL DEFAULT '',
  org_name_en VARCHAR(255) DEFAULT '',
  org_name_kz VARCHAR(255) DEFAULT '',
  org_type VARCHAR(50) NOT NULL DEFAULT '',
  country VARCHAR(100) NOT NULL DEFAULT '',
  city VARCHAR(100) NOT NULL DEFAULT '',
  website VARCHAR(255) DEFAULT '',
  contact_name VARCHAR(255) NOT NULL DEFAULT '',
  contact_position VARCHAR(255) DEFAULT '',
  contact_email VARCHAR(255) NOT NULL DEFAULT '',
  contact_phone VARCHAR(255) DEFAULT '',
  contact_method VARCHAR(100) DEFAULT '',
  cooperation_directions TEXT,
  description TEXT,
  description_en TEXT,
  description_kz TEXT,
  activity_areas TEXT,
  interaction_format TEXT,
  subtasks TEXT,
  subtasks_en TEXT,
  subtasks_kz TEXT,
  goals TEXT,
  goals_en TEXT,
  goals_kz TEXT,
  events TEXT,
  materials TEXT,
  file_path VARCHAR(500) DEFAULT NULL,
  description_images TEXT,
  data_consent SMALLINT NOT NULL DEFAULT 0,
  published SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_partnership_published ON tbl_partnership (published);
CREATE INDEX IF NOT EXISTS idx_partnership_created_at ON tbl_partnership (created_at);

CREATE TABLE IF NOT EXISTS tbl_user (
  id SERIAL PRIMARY KEY,
  username VARCHAR(128) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(128) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL
);

INSERT INTO tbl_user (username, password, email, created_at, updated_at)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ku.edu.kz', NOW(), NOW())
ON CONFLICT (username) DO NOTHING;

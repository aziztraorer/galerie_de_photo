-- Vérifier si la colonne role existe
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'role';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(50) DEFAULT "user" NOT NULL;')
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mettre à jour les utilisateurs sans rôle
UPDATE users SET role = 'user' WHERE role IS NULL OR role = '';

-- Donner le rôle admin au premier utilisateur (ou à celui que vous voulez)
UPDATE users SET role = 'admin' WHERE id = 1;

-- Afficher les utilisateurs avec leurs rôles
SELECT id, name, email, role, created_at FROM users ORDER BY id;
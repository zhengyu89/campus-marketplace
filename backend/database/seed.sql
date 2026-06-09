USE campus_marketplace;

INSERT INTO users (name, email, password_hash, role) VALUES
('System Admin', 'admin@gmail.com', '$2y$12$uZZYfrUiJWXRKLpRaqF2weo3h2cRcoPR8Uql0vgUtPNBUUbtkgr/.', 'admin')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role = VALUES(role);

INSERT INTO users (name, email, password_hash, role) VALUES
('Registered User 1', 'registereduser1@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 2', 'registereduser2@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 3', 'registereduser3@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 4', 'registereduser4@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 5', 'registereduser5@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 6', 'registereduser6@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 7', 'registereduser7@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 8', 'registereduser8@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 9', 'registereduser9@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user'),
('Registered User 10', 'registereduser10@gmail.com', '$2y$12$50BgBZ6RJzw.CbiL.vp2LO64SRaTld3lJdDxU9jofaEXjnFf7xYDO', 'user')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role = VALUES(role);

INSERT IGNORE INTO categories (category_name) VALUES
('Books'),
('Electronics'),
('Stationery'),
('Furniture'),
('Clothing'),
('Others');

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

INSERT INTO listings (
    seller_id,
    category_id,
    title,
    description,
    price,
    image_url,
    condition_status,
    listing_status
)
SELECT
    users.user_id,
    categories.category_id,
    'TI-84 Plus CE Graphing Calculator',
    'Reliable graphing calculator for engineering, calculus, and statistics classes. Includes the protective cover.',
    45.00,
    'https://lh3.googleusercontent.com/aida-public/AB6AXuAsMQPBRkaCWMPOuZAYs2a9f94cvKLG5ASZoXcT6xPVsH3_DL4RX6TlGBYBcMeUP8ikWxARihiiD0r23fZ3FxSSeQ4zX7Kdr4Ug1zw5G0PuF9kCw1Q5xHyBiRtrwY-UgObh6vMoBU6kPSr5Wrfpdy8zzER4_6lmTi8d40C2AXQVTiwiz1nk2418zp0zoa-9M2eCAIMxpvQ8R9hGETk8piSA4FwsNpEc7nutiMfcpohrwvJiX0MLxtQBlRbf9N4cRZhtk5zCI8eeMgye',
    'Like New',
    'Available'
FROM users
JOIN categories ON categories.category_name = 'Electronics'
WHERE users.email = 'registereduser1@gmail.com'
  AND NOT EXISTS (
      SELECT 1 FROM listings
      WHERE listings.title = 'TI-84 Plus CE Graphing Calculator'
        AND listings.seller_id = users.user_id
  );

INSERT INTO listings (
    seller_id,
    category_id,
    title,
    description,
    price,
    image_url,
    condition_status,
    listing_status
)
SELECT
    users.user_id,
    categories.category_id,
    'Principles of Economics (10th Ed.)',
    'Clean textbook with light highlighting in several chapters. Suitable for first-year economics students.',
    60.00,
    'https://lh3.googleusercontent.com/aida-public/AB6AXuCUZyl32tHVhOeTae88OSDpAowmqMP180hW-g6Xz5ijcGdAXpiWL0coDHXq1Izq90Qf9TI4AkQjO-DMzOrNc-HoXczItJzGRwf1XMzeTHg4hts91FE09T4-hzT2e0SLtGJGhBJ_f77W2BFnaoFti7N_dH_lCu0t0bzZEklEFacBnp5IBha_TDF1kuKnS1EX_PkiGjX2pSs20esDI7kEHU0tA7-zbVn5eyZYxkRIkQ5I7b6MiRiuQlxQoPl07bTJCYOwzj7ssQWLLJp1',
    'Used',
    'Available'
FROM users
JOIN categories ON categories.category_name = 'Books'
WHERE users.email = 'registereduser2@gmail.com'
  AND NOT EXISTS (
      SELECT 1 FROM listings
      WHERE listings.title = 'Principles of Economics (10th Ed.)'
        AND listings.seller_id = users.user_id
  );

INSERT INTO listings (
    seller_id,
    category_id,
    title,
    description,
    price,
    image_url,
    condition_status,
    listing_status
)
SELECT
    users.user_id,
    categories.category_id,
    'Adjustable LED Desk Lamp',
    'Space-saving LED lamp with adjustable brightness for late-night revision and compact study desks.',
    20.00,
    'https://lh3.googleusercontent.com/aida-public/AB6AXuBZById9yBaBGyxq4QDD6NVoI50MkWfmhyoGqDz5MTumO3RcAunVxdG1YI0-PjGSbkJKeIi7tWHYlJaZIgDrEYzhPSLdAtL_K9z-3WVRSESTINoREr6n98IL0qg4WnaU-DDD0tTjtwhIBwuOcnMclTMRkTe93tyzW_u_bgpMtDITlFYg_mOcx4pYtysRucehrDSPXdytXV783Zd26YNrWMcLNrHu2SpnZ9Qik3dhxf51Q3lhqnWVBmOaHa0-7RNG5lmUm6cxultlq4q',
    'Like New',
    'Available'
FROM users
JOIN categories ON categories.category_name = 'Furniture'
WHERE users.email = 'registereduser3@gmail.com'
  AND NOT EXISTS (
      SELECT 1 FROM listings
      WHERE listings.title = 'Adjustable LED Desk Lamp'
        AND listings.seller_id = users.user_id
  );

INSERT INTO listings (
    seller_id,
    category_id,
    title,
    description,
    price,
    image_url,
    condition_status,
    listing_status
)
SELECT
    users.user_id,
    categories.category_id,
    'Campus Commuter Bicycle',
    'Lightweight bicycle for getting between colleges, laboratories, and lecture halls. Recently serviced.',
    120.00,
    'https://lh3.googleusercontent.com/aida-public/AB6AXuBNQkgL4lltR4FEjsDJZHNWyJ87IGzg7mZ0KrIrx1yGtuMFKvFsAxJUNTwdPAuyF37zWmFvSjItkgRHBBEE8s1aysnIkJfRM5V0gcCwGci0wfFBGGcTrs7bwPOIjcrHqeVo801Dw7RkurQey3cbJ50uxKow7eOzhoURDkFQpsTNsoK1wEWpFYEjbzqt4Hk2UMt9kRnAbGVOZ1vq8ALvX3K5jjoYMn6R51_yiE2ByLxGdbZAAmiH2l4IQ85WDWJGTbuRjJ1oHjjOytqE',
    'Used',
    'Reserved'
FROM users
JOIN categories ON categories.category_name = 'Others'
WHERE users.email = 'registereduser4@gmail.com'
  AND NOT EXISTS (
      SELECT 1 FROM listings
      WHERE listings.title = 'Campus Commuter Bicycle'
        AND listings.seller_id = users.user_id
  );

INSERT INTO listings (
    seller_id,
    category_id,
    title,
    description,
    price,
    image_url,
    condition_status,
    listing_status
)
SELECT
    users.user_id,
    categories.category_id,
    'Wireless Keyboard and Mouse Set',
    'Comfortable wireless keyboard and mouse combination for assignments, reports, and everyday laptop use.',
    35.00,
    'https://images.unsplash.com/photo-1541140532154-b024d705b90a?auto=format&fit=crop&w=900&q=80',
    'Used',
    'Available'
FROM users
JOIN categories ON categories.category_name = 'Electronics'
WHERE users.email = 'registereduser5@gmail.com'
  AND NOT EXISTS (
      SELECT 1 FROM listings
      WHERE listings.title = 'Wireless Keyboard and Mouse Set'
        AND listings.seller_id = users.user_id
  );

INSERT INTO listings (
    seller_id,
    category_id,
    title,
    description,
    price,
    image_url,
    condition_status,
    listing_status
)
SELECT
    users.user_id,
    categories.category_id,
    'Compact Study Table',
    'A sturdy compact table that fits well in a hostel room. Minor cosmetic marks but fully functional.',
    55.00,
    'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=900&q=80',
    'Used',
    'Sold'
FROM users
JOIN categories ON categories.category_name = 'Furniture'
WHERE users.email = 'registereduser6@gmail.com'
  AND NOT EXISTS (
      SELECT 1 FROM listings
      WHERE listings.title = 'Compact Study Table'
        AND listings.seller_id = users.user_id
  );

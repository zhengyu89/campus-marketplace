CREATE DATABASE IF NOT EXISTS campus_marketplace;
USE campus_marketplace;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS listings (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(2048) NULL,
    condition_status ENUM('New', 'Like New', 'Used') NOT NULL DEFAULT 'Used',
    listing_status ENUM('Available', 'Reserved', 'Sold') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_listings_seller
        FOREIGN KEY (seller_id) REFERENCES users(user_id),

    CONSTRAINT fk_listings_category
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE IF NOT EXISTS offers (
    offer_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    offer_price DECIMAL(10, 2) NOT NULL,
    message VARCHAR(255),
    offer_status ENUM('Pending', 'Accepted', 'Rejected', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_offers_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id),

    CONSTRAINT fk_offers_buyer
        FOREIGN KEY (buyer_id) REFERENCES users(user_id),

    CONSTRAINT uq_offers_listing_buyer
        UNIQUE (listing_id, buyer_id)
);

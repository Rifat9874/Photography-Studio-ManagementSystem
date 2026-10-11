-- Photography Studio Management System
-- Group 05 - CSC 3215 Web Technologies
-- Import this file from phpMyAdmin (Import tab)
USE if0_43148662_photostudio;

-- Remove the old tables first, so this file can be imported again
-- without getting the error "#1050 Table 'users' already exists".
-- The child tables must be dropped before the parent tables.
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS availability;
DROP TABLE IF EXISTS portfolio;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS packages;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------
-- TABLE 1: users  (all four roles are kept in one table)
-- ---------------------------------------------------------
CREATE TABLE users (
	id INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	username VARCHAR(50) NOT NULL UNIQUE,
	email VARCHAR(100) NOT NULL UNIQUE,
	password VARCHAR(100) NOT NULL,
	phone VARCHAR(20),
	role VARCHAR(20) NOT NULL,
	specialization VARCHAR(100),
	status VARCHAR(20) DEFAULT 'active',
	profile_pic VARCHAR(255) DEFAULT '',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- TABLE 2: categories
-- ---------------------------------------------------------
CREATE TABLE categories (
	id INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	description VARCHAR(255)
);

-- ---------------------------------------------------------
-- TABLE 3: packages  (each package belongs to one category)
-- ---------------------------------------------------------
CREATE TABLE packages (
	id INT PRIMARY KEY AUTO_INCREMENT,
	category_id INT NOT NULL,
	name VARCHAR(100) NOT NULL,
	price DECIMAL(10,2) NOT NULL,
	duration VARCHAR(50),
	inclusions TEXT,
	FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- TABLE 4: bookings
-- status: pending / approved / assigned / completed / rejected / cancelled
-- ---------------------------------------------------------
CREATE TABLE bookings (
	id INT PRIMARY KEY AUTO_INCREMENT,
	customer_id INT NOT NULL,
	package_id INT NOT NULL,
	photographer_id INT DEFAULT NULL,
	booking_date DATE NOT NULL,
	booking_time VARCHAR(20) NOT NULL,
	location VARCHAR(255) NOT NULL,
	note TEXT,
	status VARCHAR(20) DEFAULT 'pending',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
	FOREIGN KEY (photographer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------
-- TABLE 5: reviews  (one review per completed booking)
-- ---------------------------------------------------------
CREATE TABLE reviews (
	id INT PRIMARY KEY AUTO_INCREMENT,
	booking_id INT NOT NULL,
	customer_id INT NOT NULL,
	rating INT NOT NULL,
	comment TEXT,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
	FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- TABLE 6: portfolio
-- ---------------------------------------------------------
CREATE TABLE portfolio (
	id INT PRIMARY KEY AUTO_INCREMENT,
	photographer_id INT NOT NULL,
	category_id INT NOT NULL,
	title VARCHAR(150) NOT NULL,
	description TEXT,
	image VARCHAR(255),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (photographer_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- TABLE 7: availability
-- ---------------------------------------------------------
CREATE TABLE availability (
	id INT PRIMARY KEY AUTO_INCREMENT,
	photographer_id INT NOT NULL,
	available_date DATE NOT NULL,
	time_slot VARCHAR(20) NOT NULL,
	FOREIGN KEY (photographer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- TABLE 8: complaints
-- ---------------------------------------------------------
CREATE TABLE complaints (
	id INT PRIMARY KEY AUTO_INCREMENT,
	customer_id INT NOT NULL,
	booking_id INT DEFAULT NULL,
	subject VARCHAR(150) NOT NULL,
	message TEXT NOT NULL,
	status VARCHAR(20) DEFAULT 'open',
	admin_reply TEXT,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);

-- =========================================================
-- DEMO DATA
-- =========================================================

-- admin / manager
INSERT INTO users (name, username, email, password, phone, role, specialization) VALUES
('Rifat Chowdhury', 'admin', 'admin@studio.com', 'admin123', '01700000001', 'admin', ''),
('Jubayer Khan', 'manager', 'manager@studio.com', 'manager123', '01700000002', 'manager', '');

-- photographers
INSERT INTO users (name, username, email, password, phone, role, specialization) VALUES
('Saidur Rahman', 'photo1', 'saidur@studio.com', 'photo123', '01700000003', 'photographer', 'Wedding'),
('Israt Jahan', 'photo2', 'israt@studio.com', 'photo123', '01700000004', 'photographer', 'Portrait'),
('Tanvir Ahmed', 'photo3', 'tanvir@studio.com', 'photo123', '01700000005', 'photographer', 'Product');

-- customers
INSERT INTO users (name, username, email, password, phone, role, specialization) VALUES
('Nusrat Jahan', 'customer', 'nusrat@gmail.com', 'customer123', '01800000001', 'customer', ''),
('Arif Hossain', 'customer2', 'arif@gmail.com', 'customer123', '01800000002', 'customer', '');

INSERT INTO categories (name, description) VALUES
('Wedding', 'Full wedding day and holud coverage'),
('Portrait', 'Studio and outdoor portrait sessions'),
('Event', 'Corporate programs, birthdays and conferences'),
('Product', 'E-commerce and catalogue product shoots');

INSERT INTO packages (category_id, name, price, duration, inclusions) VALUES
(1, 'Wedding Basic', 25000.00, '6 hours', '1 photographer, 150 edited photos, online gallery'),
(1, 'Wedding Premium', 55000.00, '12 hours', '2 photographers, 400 edited photos, photo album, drone shot'),
(2, 'Portrait Studio', 5000.00, '1 hour', '1 photographer, 20 edited photos, 2 outfit changes'),
(2, 'Portrait Outdoor', 8000.00, '2 hours', '1 photographer, 40 edited photos, location shoot'),
(3, 'Event Half Day', 12000.00, '4 hours', '1 photographer, 100 edited photos, same day preview'),
(3, 'Event Full Day', 20000.00, '8 hours', '2 photographers, 250 edited photos, highlight video'),
(4, 'Product Starter', 4000.00, '3 hours', 'Up to 15 products, white background, basic retouch'),
(4, 'Product Pro', 9000.00, '6 hours', 'Up to 40 products, lifestyle setup, advanced retouch');

INSERT INTO availability (photographer_id, available_date, time_slot) VALUES
(3, '2026-10-05', 'Morning'),
(3, '2026-10-05', 'Afternoon'),
(3, '2026-10-12', 'Morning'),
(4, '2026-10-05', 'Evening'),
(4, '2026-10-08', 'Morning'),
(5, '2026-10-05', 'Morning'),
(5, '2026-10-15', 'Afternoon');

INSERT INTO bookings (customer_id, package_id, photographer_id, booking_date, booking_time, location, note, status) VALUES
(6, 1, NULL, '2026-10-20', 'Morning', 'Banani Community Center, Dhaka', 'Please come 30 minutes early', 'pending'),
(6, 3, 4, '2026-09-10', 'Afternoon', 'Studio, Kemal Ataturk Avenue', 'Formal portrait for passport', 'completed'),
(7, 5, 3, '2026-10-05', 'Morning', 'AIUB Auditorium, Kuratoli', 'Corporate seminar coverage', 'assigned'),
(7, 7, NULL, '2026-10-25', 'Afternoon', 'Mirpur 10 warehouse', 'Around 12 products', 'approved');

INSERT INTO reviews (booking_id, customer_id, rating, comment) VALUES
(2, 6, 5, 'Very professional session, photos were delivered on time.');

INSERT INTO portfolio (photographer_id, category_id, title, description, image) VALUES
(3, 1, 'Holud Ceremony - Gulshan', 'Outdoor holud decoration shoot', ''),
(3, 3, 'Tech Conference 2026', 'Stage and audience coverage', ''),
(4, 2, 'Studio Portrait Series', 'Soft light studio portraits', ''),
(5, 4, 'Ceramic Mug Catalogue', 'White background product set', '');

INSERT INTO complaints (customer_id, booking_id, subject, message, status) VALUES
(6, 2, 'Late photo delivery', 'The edited photos arrived three days after the promised date.', 'open'),
(7, 3, 'Change of location', 'I want to know if the shoot location can be changed after approval.', 'in progress');

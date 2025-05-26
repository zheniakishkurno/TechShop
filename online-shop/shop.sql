-- Удаляем таблицы если существуют
DROP TABLE IF EXISTS reviews, order_items, orders, customers, products, categories, users CASCADE;

-- Создаём ENUM типы
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role') THEN
        CREATE TYPE user_role AS ENUM ('user', 'admin');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'order_status') THEN
        CREATE TYPE order_status AS ENUM ('processing', 'shipped', 'delivered', 'cancelled');
    END IF;
END$$;

-- Создание таблиц

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role user_role DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT REFERENCES categories(id),
    price NUMERIC(10,2) NOT NULL,
    description TEXT,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    discount INT DEFAULT 0,
    views INT DEFAULT 0,
    reviews_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL REFERENCES customers(id),
    total NUMERIC(10,2) NOT NULL,
    status order_status DEFAULT 'processing',
    payment_method VARCHAR(50) NOT NULL,
    shipping_address TEXT NOT NULL,
    delivery_date TIMESTAMP NULL,
    notes TEXT NULL,
    delivery_address VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES orders(id),
    product_id INT NOT NULL REFERENCES products(id),
    quantity INT NOT NULL,
    price NUMERIC(10,2) NOT NULL
);

CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES products(id),
    user_id INT NOT NULL REFERENCES users(id),
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Вставка тестовых данных

INSERT INTO categories (name, image_url) VALUES 
('Смартфоны и гаджеты', 'images/max.jpg'),
('Ноутбуки и компьютеры', 'images/qw.jpg'),
('Телевизоры и аудио', 'images/audio.jpg'),
('Фото и видеокамеры', 'images/iu.jpg');

INSERT INTO products (name, category_id, price, description, stock, image_url, discount, reviews_count) VALUES 
('Смартфон Apple iPhone 14 Pro', 1, 999.90, 'Флагманский смартфон с процессором A16 Bionic и камерой 48 МП', 15, 'images/AppleiPhone14Pro.jpg', 0, 42),
('Смартфон Samsung Galaxy S23 Ultra', 1, 899.90, 'Смартфон с экраном Dynamic AMOLED 2X и S Pen', 12, 'images/SamsungGalaxyS23.jpg', 5, 38),
('Смартфон Xiaomi 13 Pro', 1, 74990.00, 'Флагман с камерой Leica и Snapdragon 8 Gen 2', 20, 'images/xiaomi-13-pro.jpg', 10, 33),
('Смарт-часы Apple Watch Series 9', 1, 399.90, 'Умные часы с датчиком температуры и функцией "двойной тап"', 25, 'images/Apple-Watch-Series-9-41-mm-Aluminum-Starlight.jpg', 0, 40),
('Фитнес-браслет Huawei Band 8', 1, 499.00, 'Фитнес-трекер с AMOLED-дисплеем и пульсометром', 30, 'images/HuaweiBand8.jpg', 0, 14),

('Ноутбук Apple MacBook Pro 14\" M2 Pro', 2, 1499.90, 'Мощный ноутбук с чипом Apple M2 Pro и экраном Liquid Retina XDR', 8, 'images/AppleMacBookPro14M2Pro.jpg', 0, 25),
('Ноутбук ASUS ROG Zephyrus G14', 2, 109.99, 'Игровой ноутбук с Ryzen 9 и NVIDIA RTX 3060', 10, 'images/ASUSROGZephyrusG14.jpg', 10, 31),
('Ноутбук Lenovo Legion 5 Pro', 2, 1199.90, 'Игровой ноутбук с Ryzen 7 и RTX 4070', 6, 'images/LenovoLegion5Pro.jpg', 5, 22),
('Мини-ПК Intel NUC 13 Pro', 2, 649.90, 'Компактный ПК с Intel Core i7 13-го поколения', 10, 'images/IntelNUC13Pro.jpg', 0, 9),
('Монитор LG UltraFine 27\"', 2, 459.90, 'Профессиональный монитор 5K с точной цветопередачей', 5, 'images/LGUltraFine27.jpg', 5, 13),

('Телевизор LG OLED C2', 3, 899.90, 'OLED телевизор 55\" с AI ThinQ', 7, 'images/LGOLEDC2.jpg', 15, 19),
('Наушники Sony WH-1000XM5', 3, 299.90, 'Беспроводные наушники с шумоподавлением', 20, 'images/SonyWH-1000XM5.jpg', 0, 47),
('Телевизор Samsung Neo QLED QN90C', 3, 1249.90, '4K телевизор с поддержкой HDR10+', 4, 'images/SamsungNeoQLEDQN90C.jpg', 7, 15),
('Саундбар JBL Bar 500', 3, 399.90, 'Dolby Atmos саундбар с сабвуфером', 14, 'images/JBLBar500.jpg', 0, 18),
('Портативная колонка Marshall Emberton II', 3, 199.90, 'Bluetooth колонка с фирменным звучанием', 25, 'images/MarshallEmbertonII.jpg', 0, 24),

('Фотоаппарат Canon EOS R6 Mark II', 4, 1599.90, 'Беззеркальная камера с 24.2 МП и 6K видео', 5, 'images/CanonEOSR6MarkII.jpg', 0, 12),
('Экшн-камера GoPro HERO11 Black', 4, 399.90, 'Экшн-камера с сенсором 1/1.9\" и HyperSmooth 5.0', 18, 'images/GoProHERO11Black.jpg', 5, 28),
('Беззеркальная камера Sony Alpha 7 IV', 4, 1999.90, 'Камера с сенсором 33 МП и 4K 60p видео', 3, 'images/SonyAlpha7IV.jpg', 5, 11),
('Компактная камера Fujifilm X100V', 4, 1249.90, 'Премиальная камера с фикс-объективом и ретро-дизайном', 7, 'images/FujifilmX100V.jpg', 0, 20),
('Объектив Sigma 24-70mm f/2.8 DG DN Art', 4, 849.90, 'Полнокадровый универсальный объектив', 4, 'images/Sigma24-70mmf2.8DGDNArt.jpg', 0, 6);

INSERT INTO users (first_name, last_name, phone, email, password, role) VALUES
('Иван', 'Иванов', '+79161234567', 'ivan@gmail.com', '$2b$12$QfMdnn7IiWPlg9Rnl6Xtre/vvAVOBzqf23zghFJYP2LzHSHSYixGq', 'user'),
('женя', 'женя', '+375447566666', 'zhenia@gmail.com', '$2b$12$QfMdnn7IiWPlg9Rnl6Xtre/vvAVOBzqf23zghFJYP2LzHSHSYixGq', 'user'),
('админ', 'админ', '+375291234567', 'admin@gmail.com', '$2b$12$QfMdnn7IiWPlg9Rnl6Xtre/vvAVOBzqf23zghFJYP2LzHSHSYixGq', 'admin');

INSERT INTO customers (name, email, phone, address) VALUES
('Иван Иванов', 'ivan@gmail.com', '+79161234567', 'Москва, ул. Пушкина, д. 1'),
('Петр Петров', 'petr@gmail.com', '+79261234567', 'Санкт-Петербург, ул. Ленина, д. 10');

INSERT INTO orders (customer_id, total, status, payment_method, shipping_address, delivery_date, notes, delivery_address) VALUES
(1, 2500.00, 'processing', 'credit card', 'Москва, ул. Пушкина, д. 1', NULL, 'Без звонков', 'Москва'),
(2, 1200.00, 'shipped', 'paypal', 'Санкт-Петербург, ул. Ленина, д. 10', '2025-05-30 15:00:00', '', 'Санкт-Петербург');

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 2, 999.90),
(1, 4, 1, 399.90),
(2, 2, 1, 899.90);

INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Отличный смартфон!'),
(2, 2, 4, 'Хорошо, но дороговато'),
(3, 3, 3, 'Средний телефон');

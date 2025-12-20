-- =====================================================
-- FOODHUNTER SAMPLE DATA
-- University Canteen Database
-- =====================================================

USE university_canteen;

-- Clear existing data (in reverse order of foreign key dependencies)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pickups;
TRUNCATE TABLE user_redeemed_rewards;
TRUNCATE TABLE loyalty_points;
TRUNCATE TABLE rewards;
TRUNCATE TABLE payments;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE cart_items;
TRUNCATE TABLE menu_items;
TRUNCATE TABLE categories;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. USERS (Vendors, Students, Staff, Admin)
-- =====================================================
-- Password for all users: Password123
-- Hash: $2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki

INSERT INTO users (user_id, name, email, password, role, phone, created_at, updated_at) VALUES
-- Vendors
(1, 'Nasi Lemak Stall', 'nasilemak@vendor.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456001', NOW(), NOW()),
(2, 'Western Food Corner', 'western@vendor.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456002', NOW(), NOW()),
(3, 'Mamak Roti Canai', 'mamak@vendor.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456003', NOW(), NOW()),
(4, 'Chinese Noodle House', 'noodles@vendor.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456004', NOW(), NOW()),
(5, 'Healthy Salad Bar', 'salad@vendor.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456005', NOW(), NOW()),
(6, 'Japanese Bento', 'bento@vendor.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456006', NOW(), NOW()),
(7, 'Drinks & Desserts', 'drinks@vendor.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456007', NOW(), NOW()),

-- Students
(8, 'Ahmad bin Abdullah', 'ahmad.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876001', NOW(), NOW()),
(9, 'Siti Nurhaliza', 'siti.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876002', NOW(), NOW()),
(10, 'Lee Wei Ming', 'lee.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876003', NOW(), NOW()),
(11, 'Priya Devi', 'priya.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876004', NOW(), NOW()),
(12, 'John Tan', 'john.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876005', NOW(), NOW()),
(13, 'Nurul Aina', 'nurul.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876006', NOW(), NOW()),
(14, 'Chen Mei Ling', 'chen.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876007', NOW(), NOW()),
(15, 'Kumar Raj', 'kumar.student@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876008', NOW(), NOW()),

-- Staff
(16, 'Dr. Sarah Wong', 'sarah.staff@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'staff', '+60125551001', NOW(), NOW()),
(17, 'Prof. Hassan Ibrahim', 'hassan.staff@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'staff', '+60125551002', NOW(), NOW()),
(18, 'Mrs. Lim Siew Hua', 'lim.staff@university.edu', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'staff', '+60125551003', NOW(), NOW()),

-- Admin
(19, 'Admin User', 'admin@foodhunter.com', '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'admin', '+60123456789', NOW(), NOW());

-- =====================================================
-- 2. CATEGORIES
-- =====================================================
INSERT INTO categories (category_id, category_name) VALUES
(1, 'Rice Dishes'),
(2, 'Noodles'),
(3, 'Western Food'),
(4, 'Indian Cuisine'),
(5, 'Japanese Food'),
(6, 'Salads & Healthy'),
(7, 'Beverages'),
(8, 'Desserts'),
(9, 'Breakfast'),
(10, 'Snacks');

-- =====================================================
-- 3. MENU ITEMS (60+ items across vendors)
-- =====================================================

-- Vendor 1: Nasi Lemak Stall
INSERT INTO menu_items (item_id, category_id, vendor_id, name, description, price, image_path, is_available, created_at, updated_at) VALUES
(1, 1, 1, 'Nasi Lemak Special', 'Fragrant coconut rice with fried chicken, sambal, peanuts, and anchovies', 7.50, '/images/menu/nasi-lemak.jpg', 1, NOW(), NOW()),
(2, 1, 1, 'Nasi Lemak Rendang', 'Coconut rice with tender beef rendang and condiments', 9.00, '/images/menu/nasi-lemak-rendang.jpg', 1, NOW(), NOW()),
(3, 1, 1, 'Nasi Lemak Ayam Berempah', 'Spicy marinated fried chicken with coconut rice', 8.50, '/images/menu/nasi-lemak-berempah.jpg', 1, NOW(), NOW()),
(4, 1, 1, 'Nasi Goreng Kampung', 'Traditional village-style fried rice with anchovies', 6.50, '/images/menu/nasi-goreng.jpg', 1, NOW(), NOW()),
(5, 1, 1, 'Nasi Tomato Ayam', 'Tomato rice with fried chicken', 7.00, '/images/menu/nasi-tomato.jpg', 1, NOW(), NOW()),

-- Vendor 2: Western Food Corner
(6, 3, 2, 'Chicken Chop', 'Grilled chicken chop with black pepper sauce, fries, and coleslaw', 12.00, '/images/menu/chicken-chop.jpg', 1, NOW(), NOW()),
(7, 3, 2, 'Fish & Chips', 'Crispy battered fish fillet with chunky chips and tartar sauce', 13.50, '/images/menu/fish-chips.jpg', 1, NOW(), NOW()),
(8, 3, 2, 'Spaghetti Carbonara', 'Creamy carbonara with turkey bacon and mushrooms', 11.00, '/images/menu/carbonara.jpg', 1, NOW(), NOW()),
(9, 3, 2, 'Beef Burger', 'Juicy beef patty with cheese, lettuce, tomato, and fries', 10.50, '/images/menu/burger.jpg', 1, NOW(), NOW()),
(10, 3, 2, 'Grilled Lamb Chop', 'Tender grilled lamb chop with mint sauce', 18.00, '/images/menu/lamb-chop.jpg', 1, NOW(), NOW()),
(11, 3, 2, 'Chicken Lasagna', 'Layered pasta with chicken and cheese', 12.50, '/images/menu/lasagna.jpg', 1, NOW(), NOW()),

-- Vendor 3: Mamak Roti Canai
(12, 9, 3, 'Roti Canai Kosong', 'Plain crispy roti canai with curry dhal', 2.50, '/images/menu/roti-kosong.jpg', 1, NOW(), NOW()),
(13, 9, 3, 'Roti Telur', 'Roti canai with egg filling', 3.50, '/images/menu/roti-telur.jpg', 1, NOW(), NOW()),
(14, 9, 3, 'Roti Planta', 'Roti with butter and sugar', 3.00, '/images/menu/roti-planta.jpg', 1, NOW(), NOW()),
(15, 9, 3, 'Roti Boom', 'Fluffy layered roti with condensed milk', 4.00, '/images/menu/roti-boom.jpg', 1, NOW(), NOW()),
(16, 4, 3, 'Nasi Briyani Ayam', 'Aromatic briyani rice with chicken curry', 8.50, '/images/menu/briyani.jpg', 1, NOW(), NOW()),
(17, 4, 3, 'Mee Goreng Mamak', 'Spicy fried yellow noodles with prawns', 7.00, '/images/menu/mee-goreng.jpg', 1, NOW(), NOW()),
(18, 4, 3, 'Maggi Goreng', 'Fried instant noodles with vegetables and egg', 6.50, '/images/menu/maggi-goreng.jpg', 1, NOW(), NOW()),
(19, 7, 3, 'Teh Tarik', 'Pulled milk tea - Malaysian favorite', 2.50, '/images/menu/teh-tarik.jpg', 1, NOW(), NOW()),
(20, 7, 3, 'Milo Ais', 'Iced chocolate malt drink', 3.00, '/images/menu/milo-ais.jpg', 1, NOW(), NOW()),

-- Vendor 4: Chinese Noodle House
(21, 2, 4, 'Wantan Mee', 'Egg noodles with wantan dumplings and char siew', 7.50, '/images/menu/wantan-mee.jpg', 1, NOW(), NOW()),
(22, 2, 4, 'Curry Laksa', 'Spicy coconut curry noodle soup', 8.50, '/images/menu/curry-laksa.jpg', 1, NOW(), NOW()),
(23, 2, 4, 'Char Kuey Teow', 'Stir-fried flat noodles with prawns and cockles', 8.00, '/images/menu/char-kuey-teow.jpg', 1, NOW(), NOW()),
(24, 2, 4, 'Hokkien Mee', 'Braised noodles in dark soy sauce', 7.50, '/images/menu/hokkien-mee.jpg', 1, NOW(), NOW()),
(25, 2, 4, 'Pan Mee Soup', 'Handmade noodle soup with minced pork', 7.00, '/images/menu/pan-mee.jpg', 1, NOW(), NOW()),
(26, 2, 4, 'Dry Chili Pan Mee', 'Noodles with spicy chili paste and anchovies', 7.50, '/images/menu/chili-pan-mee.jpg', 1, NOW(), NOW()),
(27, 1, 4, 'Chicken Rice', 'Steamed chicken with fragrant rice', 7.00, '/images/menu/chicken-rice.jpg', 1, NOW(), NOW()),
(28, 1, 4, 'Roast Duck Rice', 'Sliced roast duck with rice', 9.50, '/images/menu/duck-rice.jpg', 1, NOW(), NOW()),

-- Vendor 5: Healthy Salad Bar
(29, 6, 5, 'Caesar Salad', 'Romaine lettuce, parmesan, croutons, and Caesar dressing', 9.50, '/images/menu/caesar-salad.jpg', 1, NOW(), NOW()),
(30, 6, 5, 'Greek Salad', 'Fresh vegetables with feta cheese and olives', 10.00, '/images/menu/greek-salad.jpg', 1, NOW(), NOW()),
(31, 6, 5, 'Grilled Chicken Salad', 'Mixed greens with grilled chicken breast', 11.50, '/images/menu/chicken-salad.jpg', 1, NOW(), NOW()),
(32, 6, 5, 'Tuna Nicoise Salad', 'Tuna, eggs, green beans, and potatoes', 12.00, '/images/menu/tuna-salad.jpg', 1, NOW(), NOW()),
(33, 6, 5, 'Quinoa Buddha Bowl', 'Quinoa with roasted vegetables and tahini', 11.00, '/images/menu/buddha-bowl.jpg', 1, NOW(), NOW()),
(34, 6, 5, 'Fruit Salad Cup', 'Fresh seasonal fruits with honey yogurt', 6.50, '/images/menu/fruit-salad.jpg', 1, NOW(), NOW()),
(35, 7, 5, 'Green Smoothie', 'Spinach, banana, mango, and chia seeds', 7.50, '/images/menu/green-smoothie.jpg', 1, NOW(), NOW()),
(36, 7, 5, 'Berry Blast Smoothie', 'Mixed berries with yogurt and honey', 8.00, '/images/menu/berry-smoothie.jpg', 1, NOW(), NOW()),

-- Vendor 6: Japanese Bento
(37, 5, 6, 'Chicken Teriyaki Bento', 'Grilled teriyaki chicken with rice and sides', 12.50, '/images/menu/teriyaki-bento.jpg', 1, NOW(), NOW()),
(38, 5, 6, 'Salmon Bento', 'Grilled salmon with Japanese rice', 15.00, '/images/menu/salmon-bento.jpg', 1, NOW(), NOW()),
(39, 5, 6, 'Tonkatsu Bento', 'Breaded pork cutlet with curry sauce', 13.50, '/images/menu/tonkatsu-bento.jpg', 1, NOW(), NOW()),
(40, 5, 6, 'Chicken Katsu Curry', 'Crispy chicken with Japanese curry', 12.00, '/images/menu/katsu-curry.jpg', 1, NOW(), NOW()),
(41, 5, 6, 'Ebi Tempura Set', 'Prawn tempura with rice and miso soup', 14.00, '/images/menu/tempura-set.jpg', 1, NOW(), NOW()),
(42, 5, 6, 'Ramen Bowl', 'Japanese noodle soup with pork and egg', 11.50, '/images/menu/ramen.jpg', 1, NOW(), NOW()),
(43, 5, 6, 'Sushi Platter (8pcs)', 'Assorted fresh sushi selection', 16.50, '/images/menu/sushi-platter.jpg', 1, NOW(), NOW()),
(44, 5, 6, 'Unagi Don', 'Grilled eel on rice with teriyaki sauce', 17.00, '/images/menu/unagi-don.jpg', 1, NOW(), NOW()),

-- Vendor 7: Drinks & Desserts
(45, 7, 7, 'Fresh Orange Juice', 'Freshly squeezed orange juice', 5.50, '/images/menu/orange-juice.jpg', 1, NOW(), NOW()),
(46, 7, 7, 'Iced Latte', 'Espresso with cold milk and ice', 6.50, '/images/menu/iced-latte.jpg', 1, NOW(), NOW()),
(47, 7, 7, 'Cappuccino', 'Classic cappuccino with milk foam', 6.00, '/images/menu/cappuccino.jpg', 1, NOW(), NOW()),
(48, 7, 7, 'Bubble Milk Tea', 'Milk tea with tapioca pearls', 7.00, '/images/menu/bubble-tea.jpg', 1, NOW(), NOW()),
(49, 7, 7, 'Watermelon Juice', 'Fresh watermelon juice', 5.00, '/images/menu/watermelon-juice.jpg', 1, NOW(), NOW()),
(50, 8, 7, 'Chocolate Lava Cake', 'Warm chocolate cake with molten center', 8.50, '/images/menu/lava-cake.jpg', 1, NOW(), NOW()),
(51, 8, 7, 'Tiramisu', 'Classic Italian coffee-flavored dessert', 9.00, '/images/menu/tiramisu.jpg', 1, NOW(), NOW()),
(52, 8, 7, 'Cendol', 'Traditional Malaysian shaved ice dessert', 5.50, '/images/menu/cendol.jpg', 1, NOW(), NOW()),
(53, 8, 7, 'Ice Cream Sundae', 'Vanilla ice cream with toppings', 6.50, '/images/menu/sundae.jpg', 1, NOW(), NOW()),
(54, 8, 7, 'Cheese Cake Slice', 'New York style cheesecake', 8.00, '/images/menu/cheesecake.jpg', 1, NOW(), NOW()),

-- Add more snacks
(55, 10, 1, 'Curry Puff', 'Crispy pastry with spicy potato filling', 2.50, '/images/menu/curry-puff.jpg', 1, NOW(), NOW()),
(56, 10, 3, 'Vadai', 'Indian savory donuts', 1.50, '/images/menu/vadai.jpg', 1, NOW(), NOW()),
(57, 10, 4, 'Spring Rolls (3pcs)', 'Crispy vegetable spring rolls', 5.00, '/images/menu/spring-rolls.jpg', 1, NOW(), NOW()),
(58, 10, 6, 'Edamame', 'Steamed soybeans with sea salt', 4.50, '/images/menu/edamame.jpg', 1, NOW(), NOW()),
(59, 10, 2, 'Chicken Wings (5pcs)', 'Crispy fried chicken wings', 8.50, '/images/menu/chicken-wings.jpg', 1, NOW(), NOW()),
(60, 10, 7, 'Donut (2pcs)', 'Glazed donuts', 4.00, '/images/menu/donuts.jpg', 1, NOW(), NOW());

-- =====================================================
-- 4. LOYALTY POINTS (Initialize for all users)
-- =====================================================
INSERT INTO loyalty_points (lp_id, user_id, points, updated_at) VALUES
(1, 1, 0, NOW()),
(2, 2, 0, NOW()),
(3, 3, 0, NOW()),
(4, 4, 0, NOW()),
(5, 5, 0, NOW()),
(6, 6, 0, NOW()),
(7, 7, 0, NOW()),
(8, 8, 250, NOW()),
(9, 9, 180, NOW()),
(10, 10, 420, NOW()),
(11, 11, 95, NOW()),
(12, 12, 310, NOW()),
(13, 13, 155, NOW()),
(14, 14, 275, NOW()),
(15, 15, 60, NOW()),
(16, 16, 500, NOW()),
(17, 17, 380, NOW()),
(18, 18, 290, NOW()),
(19, 19, 1000, NOW());

-- =====================================================
-- 5. REWARDS
-- =====================================================
INSERT INTO rewards (reward_id, title, description, points_required, created_at) VALUES
(1, 'Free Drink', 'Get any beverage for free', 50, NOW()),
(2, 'RM5 Discount Voucher', 'RM5 off your next purchase', 100, NOW()),
(3, 'Free Dessert', 'Choose any dessert on the house', 150, NOW()),
(4, 'RM10 Discount Voucher', 'RM10 off your next purchase', 200, NOW()),
(5, 'Free Appetizer', 'Get any appetizer for free', 150, NOW()),
(6, 'RM20 Discount Voucher', 'RM20 off your next purchase', 400, NOW()),
(7, 'Free Lunch Set', 'One free lunch set meal', 500, NOW()),
(8, 'VIP Member Status', 'Exclusive VIP benefits for 1 month', 1000, NOW());

-- =====================================================
-- 6. ORDERS (Sample completed and active orders)
-- =====================================================
INSERT INTO orders (order_id, user_id, vendor_id, total_price, status, created_at, updated_at) VALUES
-- Completed orders
(1, 8, 1, 16.00, 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 9, 2, 25.50, 'completed', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(3, 10, 3, 14.50, 'completed', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 11, 4, 15.00, 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 12, 5, 23.50, 'completed', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 16, 6, 28.50, 'completed', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 17, 1, 22.50, 'completed', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- Active orders (today)
(8, 13, 2, 24.00, 'preparing', NOW(), NOW()),
(9, 14, 4, 16.50, 'ready', NOW(), NOW()),
(10, 15, 6, 42.00, 'accepted', NOW(), NOW()),
(11, 18, 3, 18.50, 'pending', NOW(), NOW()),
(12, 8, 7, 17.00, 'ready', NOW(), NOW());

-- =====================================================
-- 7. ORDER ITEMS
-- =====================================================
INSERT INTO order_items (order_item_id, order_id, item_id, quantity, price, special_request) VALUES
-- Order 1
(1, 1, 1, 1, 7.50, NULL),
(2, 1, 4, 1, 6.50, 'Extra spicy'),
(3, 1, 55, 1, 2.50, NULL),

-- Order 2
(4, 2, 6, 1, 12.00, 'Well done'),
(5, 2, 9, 1, 10.50, 'No onions'),
(6, 2, 45, 1, 5.50, NULL),

-- Order 3
(7, 3, 12, 2, 5.00, NULL),
(8, 3, 13, 1, 3.50, NULL),
(9, 3, 19, 2, 5.00, 'Less sugar'),

-- Order 4
(10, 4, 21, 1, 7.50, NULL),
(11, 4, 22, 1, 8.50, 'Extra spicy'),

-- Order 5
(12, 5, 29, 1, 9.50, 'No croutons'),
(13, 5, 31, 1, 11.50, NULL),
(14, 5, 35, 1, 7.50, NULL),

-- Order 6
(15, 6, 37, 1, 12.50, NULL),
(16, 6, 42, 1, 11.50, NULL),
(17, 6, 58, 1, 4.50, NULL),

-- Order 7
(18, 7, 1, 2, 15.00, NULL),
(19, 7, 2, 1, 9.00, NULL),

-- Order 8 (preparing)
(20, 8, 6, 1, 12.00, NULL),
(21, 8, 8, 1, 11.00, NULL),
(22, 8, 46, 1, 6.50, NULL),

-- Order 9 (ready)
(23, 9, 21, 1, 7.50, NULL),
(24, 9, 23, 1, 8.00, NULL),
(25, 9, 45, 1, 5.00, NULL),

-- Order 10 (accepted)
(26, 10, 37, 1, 12.50, NULL),
(27, 10, 39, 1, 13.50, NULL),
(28, 10, 43, 1, 16.50, NULL),

-- Order 11 (pending)
(29, 11, 16, 1, 8.50, NULL),
(30, 11, 17, 1, 7.00, NULL),
(31, 11, 20, 1, 3.00, NULL),

-- Order 12 (ready)
(32, 12, 50, 1, 8.50, NULL),
(33, 12, 51, 1, 9.00, NULL);

-- =====================================================
-- 8. PAYMENTS
-- =====================================================
INSERT INTO payments (payment_id, order_id, amount, method, status, transaction_ref, paid_at) VALUES
(1, 1, 16.00, 'online', 'paid', 'TXN20250113001', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 2, 25.50, 'ewallet', 'paid', 'TXN20250114002', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(3, 3, 14.50, 'cash', 'paid', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 4, 15.00, 'online', 'paid', 'TXN20250116004', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 5, 23.50, 'ewallet', 'paid', 'TXN20250117005', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 6, 28.50, 'online', 'paid', 'TXN20250117006', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 7, 22.50, 'ewallet', 'paid', 'TXN20250117007', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 8, 24.00, 'online', 'paid', 'TXN20250118008', NOW()),
(9, 9, 16.50, 'online', 'paid', 'TXN20250118009', NOW()),
(10, 10, 42.00, 'ewallet', 'paid', 'TXN20250118010', NOW()),
(11, 11, 18.50, 'online', 'pending', NULL, NULL),
(12, 12, 17.00, 'cash', 'paid', NULL, NOW());

-- =====================================================
-- 9. PICKUP (Queue System)
-- =====================================================
INSERT INTO pickups (pickup_id, order_id, queue_number, qr_code, status, updated_at) VALUES
(1, 1, 101, 'QR-ORD001-2025', 'picked_up', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 2, 102, 'QR-ORD002-2025', 'picked_up', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(3, 3, 103, 'QR-ORD003-2025', 'picked_up', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 4, 104, 'QR-ORD004-2025', 'picked_up', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 5, 105, 'QR-ORD005-2025', 'picked_up', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 6, 106, 'QR-ORD006-2025', 'picked_up', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 7, 107, 'QR-ORD007-2025', 'picked_up', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 8, 108, 'QR-ORD008-2025', 'waiting', NOW()),
(9, 9, 109, 'QR-ORD009-2025', 'ready', NOW()),
(10, 10, 110, 'QR-ORD010-2025', 'waiting', NOW()),
(11, 11, 111, 'QR-ORD011-2025', 'waiting', NOW()),
(12, 12, 112, 'QR-ORD012-2025', 'ready', NOW());

-- =====================================================
-- 10. CART ITEMS (Active shopping carts)
-- =====================================================
INSERT INTO cart_items (cart_id, user_id, item_id, quantity, special_request, added_at) VALUES
-- User 8 cart
(1, 8, 27, 1, NULL, NOW()),
(2, 8, 28, 1, NULL, NOW()),

-- User 9 cart
(3, 9, 37, 2, 'Extra wasabi', NOW()),
(4, 9, 42, 1, 'Extra noodles', NOW()),

-- User 10 cart
(5, 10, 6, 1, 'Medium rare', NOW()),
(6, 10, 47, 1, NULL, NOW()),

-- User 11 cart
(7, 11, 29, 1, 'Dressing on the side', NOW()),
(8, 11, 36, 1, NULL, NOW()),

-- User 13 cart
(9, 13, 50, 1, NULL, NOW()),
(10, 13, 51, 1, NULL, NOW()),
(11, 13, 48, 2, 'Less sugar', NOW());

-- =====================================================
-- 11. USER REDEEMED REWARDS
-- =====================================================
INSERT INTO user_redeemed_rewards (redeemed_id, user_id, reward_id, redeemed_at) VALUES
(1, 8, 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 9, 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(3, 10, 2, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(4, 10, 3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(5, 12, 2, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(6, 14, 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 16, 4, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(8, 17, 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(9, 17, 3, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================
-- Run these to verify data was inserted correctly:

-- SELECT COUNT(*) as total_users FROM users;
-- SELECT COUNT(*) as total_menu_items FROM menu_items;
-- SELECT COUNT(*) as total_orders FROM orders;
-- SELECT COUNT(*) as total_categories FROM categories;
-- SELECT COUNT(*) as total_rewards FROM rewards;

-- SELECT role, COUNT(*) as count FROM users GROUP BY role;
-- SELECT status, COUNT(*) as count FROM orders GROUP BY status;
-- SELECT vendor_id, COUNT(*) as items FROM menu_items GROUP BY vendor_id;

-- =====================================================
-- END OF SAMPLE DATA
-- =====================================================

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();
        $categories = Category::all();

        if ($vendors->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Ensure Vendors and Categories are seeded first.');
            return;
        }

        // 100 Unique Items (10 per Category)
        $data = [
            'rice-noodles' => [
                ['name' => 'Seafood Laksa', 'price' => 15.50, 'img' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c'],
                ['name' => 'Vegetable Fried Rice', 'price' => 10.00, 'img' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b'],
                ['name' => 'Shrimp Pad Thai', 'price' => 14.20, 'img' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de'],
                ['name' => 'Beef Ramen Bowl', 'price' => 16.00, 'img' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624'],
                ['name' => 'Chicken Chow Mein', 'price' => 12.50, 'img' => 'https://images.unsplash.com/photo-1585032295896-262427b99521'],
                ['name' => 'Egg Fried Rice', 'price' => 9.50, 'img' => 'https://images.unsplash.com/photo-1512056502450-4c7b446ef2f0'],
                ['name' => 'Korean Bibimbap', 'price' => 18.00, 'img' => 'https://images.unsplash.com/photo-1590301157890-4810ed352733'],
                ['name' => 'Spicy Udon Noodles', 'price' => 15.00, 'img' => 'https://images.unsplash.com/photo-1558985250-27a406d64cb3'],
                ['name' => 'Singapore Rice Vermicelli', 'price' => 13.00, 'img' => 'https://images.unsplash.com/photo-1511910849563-0466479099bc'],
                ['name' => 'Nasi Goreng Special', 'price' => 11.50, 'img' => 'https://images.unsplash.com/photo-1610123043194-6b9900c69bc7'],
            ],
            'burgers-sandwiches' => [
                ['name' => 'Classic Cheeseburger', 'price' => 12.00, 'img' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd'],
                ['name' => 'Bacon BBQ Burger', 'price' => 14.50, 'img' => 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5'],
                ['name' => 'Chicken Club Sandwich', 'price' => 11.00, 'img' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8'],
                ['name' => 'Veggie Garden Burger', 'price' => 13.00, 'img' => 'https://images.unsplash.com/photo-1550547660-d9450f859349'],
                ['name' => 'Pulled Pork Slider', 'price' => 10.50, 'img' => 'https://images.unsplash.com/photo-1547584385-8cd437ad5463'],
                ['name' => 'Turkey Avocado Wrap', 'price' => 9.00, 'img' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f'],
                ['name' => 'Steak Sandwich', 'price' => 17.00, 'img' => 'https://images.unsplash.com/photo-1553979459-d2229ba7433b'],
                ['name' => 'Gourmet Truffle Burger', 'price' => 19.00, 'img' => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add'],
                ['name' => 'Crispy Fish Fillet', 'price' => 12.50, 'img' => 'https://images.unsplash.com/photo-1460306855393-0410f61241c7'],
                ['name' => 'Double Patty Monster', 'price' => 18.00, 'img' => 'https://images.unsplash.com/photo-1550317138-10000687ad32'],
            ],
            'beverages' => [
                ['name' => 'Iced Matcha Latte', 'price' => 6.50, 'img' => 'https://images.unsplash.com/photo-1536935338213-d2c2533ae08b'],
                ['name' => 'Fresh Lemonade', 'price' => 4.50, 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd'],
                ['name' => 'Cold Brew Coffee', 'price' => 5.50, 'img' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735'],
                ['name' => 'Berry Smoothie', 'price' => 7.50, 'img' => 'https://images.unsplash.com/photo-1534353436294-0dbd4bdac845'],
                ['name' => 'Orange Juice', 'price' => 4.00, 'img' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b'],
                ['name' => 'Classic Mojito', 'price' => 8.00, 'img' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87'],
                ['name' => 'Hot Chocolate', 'price' => 5.00, 'img' => 'https://images.unsplash.com/photo-1544787210-2213d242750e'],
                ['name' => 'Green Tea', 'price' => 3.50, 'img' => 'https://images.unsplash.com/photo-1564890369478-c89ca6d9cde9'],
                ['name' => 'Craft Beer', 'price' => 9.00, 'img' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13'],
                ['name' => 'Sparkling Water', 'price' => 2.50, 'img' => 'https://images.unsplash.com/photo-1559839914-17aae19cea9e'],
            ],
            'snacks' => [
                ['name' => 'Loaded Nachos', 'price' => 11.00, 'img' => 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d'],
                ['name' => 'French Fries', 'price' => 5.00, 'img' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877'],
                ['name' => 'Chicken Wings', 'price' => 12.00, 'img' => 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f'],
                ['name' => 'Spring Rolls', 'price' => 7.00, 'img' => 'https://images.unsplash.com/photo-1544025162-d76694265947'],
                ['name' => 'Onion Rings', 'price' => 6.50, 'img' => 'https://images.unsplash.com/photo-1639024471283-03518883511d'],
                ['name' => 'Mozzarella Sticks', 'price' => 8.50, 'img' => 'https://images.unsplash.com/photo-1531749956667-59f1f630adca'],
                ['name' => 'Popcorn Shrimp', 'price' => 13.00, 'img' => 'https://images.unsplash.com/photo-1603855669677-33as55669677'],
                ['name' => 'Tacos al Pastor', 'price' => 10.00, 'img' => 'https://images.unsplash.com/photo-1552332386-f8dd00dc2f85'],
                ['name' => 'Potato Wedges', 'price' => 6.00, 'img' => 'https://images.unsplash.com/photo-1606755456206-b25206cde27e'],
                ['name' => 'Falafel Plate', 'price' => 9.50, 'img' => 'https://images.unsplash.com/photo-1547000681-b993da58e8e7'],
            ],
            'desserts' => [
                ['name' => 'Chocolate Cake', 'price' => 8.00, 'img' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587'],
                ['name' => 'Tiramisu', 'price' => 9.00, 'img' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9'],
                ['name' => 'New York Cheesecake', 'price' => 8.50, 'img' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad'],
                ['name' => 'Fruit Tart', 'price' => 7.00, 'img' => 'https://images.unsplash.com/photo-1519915028121-7d3463d20b13'],
                ['name' => 'Ice Cream Sundae', 'price' => 6.00, 'img' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb'],
                ['name' => 'Macarons (6pcs)', 'price' => 12.00, 'img' => 'https://images.unsplash.com/photo-1569864358642-9d1684040f43'],
                ['name' => 'Apple Pie', 'price' => 7.50, 'img' => 'https://images.unsplash.com/photo-1568571780765-9276ac8b75a2'],
                ['name' => 'Creme Brulee', 'price' => 9.50, 'img' => 'https://images.unsplash.com/photo-1470338745628-1fdbd49bc43e'],
                ['name' => 'Brownie with Ice Cream', 'price' => 8.50, 'img' => 'https://images.unsplash.com/photo-1564355808539-22fda35bed7e'],
                ['name' => 'Pancakes with Syrup', 'price' => 10.00, 'img' => 'https://images.unsplash.com/photo-1528207776546-365bb710ee93'],
            ],
            'local-favorites' => [
                ['name' => 'Satay Chicken', 'price' => 12.00, 'img' => 'https://images.unsplash.com/photo-1529692236671-f1f6e994a52c'],
                ['name' => 'Hainanese Chicken Rice', 'price' => 11.00, 'img' => 'https://images.unsplash.com/photo-1596797038530-2c39bb917b17'],
                ['name' => 'Beef Rendang', 'price' => 16.00, 'img' => 'https://images.unsplash.com/photo-1541544741938-0af808871cc0'],
                ['name' => 'Roti Canai', 'price' => 5.00, 'img' => 'https://images.unsplash.com/photo-1626777553631-48762740707a'],
                ['name' => 'Dim Sum Set', 'price' => 20.00, 'img' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d'],
                ['name' => 'Salmon Teriyaki', 'price' => 22.00, 'img' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288'],
                ['name' => 'Lamb Kebabs', 'price' => 18.00, 'img' => 'https://images.unsplash.com/photo-1514327605112-b887c0e61c0a'],
                ['name' => 'Butter Chicken', 'price' => 15.00, 'img' => 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db'],
                ['name' => 'Steamed Dumplings', 'price' => 9.00, 'img' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb'],
                ['name' => 'Mixed Grill Platter', 'price' => 35.00, 'img' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1'],
            ],
            'sushi-sashimi' => [
                ['name' => 'Salmon Nigiri', 'price' => 8.00, 'img' => 'https://images.unsplash.com/photo-1583623025817-d180a2221d0a'],
                ['name' => 'California Roll', 'price' => 10.00, 'img' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351'],
                ['name' => 'Tuna Sashimi', 'price' => 12.00, 'img' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb'],
                ['name' => 'Dragon Roll', 'price' => 15.50, 'img' => 'https://images.unsplash.com/photo-1617196034183-421b4917c92d'],
                ['name' => 'Miso Soup', 'price' => 4.50, 'img' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd'],
                ['name' => 'Tempura Shrimp', 'price' => 14.00, 'img' => 'https://images.unsplash.com/photo-1559410545-0bdcd187e0a6'],
                ['name' => 'Unagi Don', 'price' => 18.00, 'img' => 'https://images.unsplash.com/photo-1590235225167-15c8a0d9b720'],
                ['name' => 'Octopus Takoyaki', 'price' => 9.00, 'img' => 'https://images.unsplash.com/photo-1615361065124-211e0d80ca21'],
                ['name' => 'Veggie Futomaki', 'price' => 11.00, 'img' => 'https://images.unsplash.com/photo-1553621042-f6e147245754'],
                ['name' => 'Spicy Tuna Roll', 'price' => 13.00, 'img' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c'],
            ],
            'healthy-salads' => [
                ['name' => 'Quinoa Protein Bowl', 'price' => 14.00, 'img' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd'],
                ['name' => 'Greek Salad', 'price' => 11.50, 'img' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe'],
                ['name' => 'Avocado Toast', 'price' => 9.00, 'img' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c'],
                ['name' => 'Kale & Apple Salad', 'price' => 10.00, 'img' => 'https://images.unsplash.com/photo-1511690656952-34342bb7c2f2'],
                ['name' => 'Roasted Beet Salad', 'price' => 12.00, 'img' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061'],
                ['name' => 'Chickpea Buddha Bowl', 'price' => 13.50, 'img' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352'],
                ['name' => 'Spinach & Walnut', 'price' => 11.00, 'img' => 'https://images.unsplash.com/photo-1543353071-873f17a7a088'],
                ['name' => 'Caesar Salad', 'price' => 12.50, 'img' => 'https://images.unsplash.com/photo-1550317138-10000687ad32'],
                ['name' => 'Summer Berry Salad', 'price' => 10.50, 'img' => 'https://images.unsplash.com/photo-1505576399279-565b52d4ac71'],
                ['name' => 'Roasted Veggie Bowl', 'price' => 14.50, 'img' => 'https://images.unsplash.com/photo-1505253758473-96b7015fcd40'],
            ],
            'indian-curries' => [
                ['name' => 'Chicken Tikka Masala', 'price' => 16.00, 'img' => 'https://images.unsplash.com/photo-1585937421612-7110052026ae'],
                ['name' => 'Palak Paneer', 'price' => 14.50, 'img' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641'],
                ['name' => 'Lamb Rogan Josh', 'price' => 18.00, 'img' => 'https://images.unsplash.com/photo-1517244681291-03973d63c547'],
                ['name' => 'Dal Makhani', 'price' => 12.00, 'img' => 'https://images.unsplash.com/photo-1601050633647-81a35d377f86'],
                ['name' => 'Aloo Gobi', 'price' => 13.00, 'img' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c6'],
                ['name' => 'Chana Masala', 'price' => 11.50, 'img' => 'https://images.unsplash.com/photo-1505253149613-112d21d9f6a9'],
                ['name' => 'Beef Vindaloo', 'price' => 17.50, 'img' => 'https://images.unsplash.com/photo-1605333396915-47ed6b68a00e'],
                ['name' => 'Prawn Malai Curry', 'price' => 19.00, 'img' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7'],
                ['name' => 'Vegetable Biryani', 'price' => 14.00, 'img' => 'https://images.unsplash.com/photo-1626777552726-4a6b547b4e5d'],
                ['name' => 'Tandoori Chicken', 'price' => 16.50, 'img' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8'],
            ],
            'grilled-seafood' => [
                ['name' => 'Grilled Salmon Steak', 'price' => 22.00, 'img' => 'https://images.unsplash.com/photo-1432139555190-58524dae6a55'],
                ['name' => 'Garlic Butter Lobster', 'price' => 45.00, 'img' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2'],
                ['name' => 'Lemon Herb Shrimp', 'price' => 18.00, 'img' => 'https://images.unsplash.com/photo-1559742811-822873691df8'],
                ['name' => 'Seared Scallops', 'price' => 24.00, 'img' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288'],
                ['name' => 'Grilled Whole Sea Bass', 'price' => 28.00, 'img' => 'https://images.unsplash.com/photo-1553621042-f6e147245754'],
                ['name' => 'Calamari Fritti', 'price' => 15.00, 'img' => 'https://images.unsplash.com/photo-1544025162-d76694265947'],
                ['name' => 'King Crab Legs', 'price' => 55.00, 'img' => 'https://images.unsplash.com/photo-1514516369414-78177d7e1c50'],
                ['name' => 'Grilled Octopus', 'price' => 26.00, 'img' => 'https://images.unsplash.com/photo-1560717789-0ac7c58ac90a'],
                ['name' => 'Tuna Tataki', 'price' => 20.00, 'img' => 'https://images.unsplash.com/photo-1534080564607-c92751f8969f'],
                ['name' => 'Mussels in White Wine', 'price' => 19.00, 'img' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0'],
            ],
        ];

        $vendorCount = $vendors->count();

        foreach ($data as $categorySlug => $items) {
            $category = Category::where('slug', $categorySlug)->first();
            if (!$category) continue;

            // Loop through the 10 unique items for this category
            foreach ($items as $index => $itemData) {
                // Use Modulo to assign the item to a vendor. 
                // If you have 10 vendors, each gets 1 unique item per category.
                // If you have 5 vendors, each gets 2 unique items per category.
                $vendor = $vendors->get($index % $vendorCount);

                MenuItem::create([
                    'vendor_id'      => $vendor->id,
                    'category_id'    => $category->id,
                    'name'           => $itemData['name'],
                    'slug'           => Str::slug($itemData['name'] . '-' . $vendor->id . '-' . uniqid()),
                    'description'    => "Authentic " . $itemData['name'] . " prepared by " . $vendor->name . ".",
                    'price'          => $itemData['price'],
                    'original_price' => rand(0, 1) ? $itemData['price'] + 4.00 : $itemData['price'],
                    'image'          => $itemData['img'] . "?auto=format&w=800",
                    'is_available'   => true,
                    'is_featured'    => ($index === 0), // First item of each cat is featured
                    'prep_time'      => rand(15, 45),
                    'calories'       => rand(200, 1000),
                    'total_sold'     => rand(0, 200),
                ]);
            }
        }
    }
}
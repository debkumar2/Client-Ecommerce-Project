<?php
/**
 * Centralized Product Dataset & Helper Functions
 * Biswas Enterprise E-Commerce
 */

require_once __DIR__ . '/database.php';

if (!function_exists('getAllProducts')) {
    function getAllProducts(): array {
        try {
            $pdo = Database::getConnection();
                $stmt = $pdo->query("SELECT p.*, 
                    COALESCE(c.name, 'General') as category_name_resolved, 
                    COALESCE(c.slug, 'general') as category_slug_resolved,
                    (SELECT image_url FROM `product_images` WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as gallery_image
                    FROM `products` p 
                    LEFT JOIN `categories` c ON p.category_id = c.id 
                    WHERE (p.status IS NULL OR p.status IN ('approved', 'published', 'active', '')) 
                    ORDER BY p.id DESC");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    $dbProducts = [];
                    foreach ($rows as $r) {
                        $price = !empty($r['selling_price']) ? (float)$r['selling_price'] : ((float)($r['price'] ?? 0));
                        $regPrice = !empty($r['regular_price']) ? (float)$r['regular_price'] : round($price * 1.15);
                        $stock = isset($r['stock_quantity']) ? (int)$r['stock_quantity'] : (int)($r['stock'] ?? 50);

                        $img = !empty($r['gallery_image']) ? $r['gallery_image'] : (!empty($r['image']) ? $r['image'] : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80');

                        $dbProducts[] = [
                            'id' => (int)$r['id'],
                            'name' => $r['name'],
                            'category' => $r['category_name_resolved'],
                            'category_slug' => $r['category_slug_resolved'],
                            'brand' => !empty($r['brand']) ? $r['brand'] : 'Biswas Enterprise',
                            'price' => $price,
                            'regular_price' => $regPrice,
                            'rating' => 5,
                            'reviews_count' => 24,
                            'stock_quantity' => $stock,
                            'stock_status' => ($stock > 0) ? 'in-stock' : 'out-of-stock',
                            'badge' => !empty($r['badge']) ? $r['badge'] : (!empty($r['is_bestseller']) ? 'BEST SELLER' : ''),
                            'badge_type' => 'sale',
                            'description' => !empty($r['description']) ? $r['description'] : (!empty($r['short_description']) ? $r['short_description'] : ''),
                            'image' => $img
                        ];
                    }
                    return $dbProducts;
                }
        } catch (\Throwable $e) {}

        return [
            [
                'id' => 101,
                'name' => 'Dried Arjuna Bark',
                'category' => 'Arjuna Bark',
                'category_slug' => 'arjuna-bark',
                'brand' => 'Biswas Organics',
                'price' => 710,
                'regular_price' => 775,
                'rating' => 5,
                'reviews_count' => 32,
                'stock_quantity' => 50,
                'stock_status' => 'in-stock',
                'badge' => 'BEST SELLER',
                'badge_type' => 'sale',
                'description' => 'Pure 99% high-purity Dried Arjuna Bark sourced directly from West Bengal for traditional heart remedies and cardio support.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 102,
                'name' => 'High Quality Arjuna Bark',
                'category' => 'Arjuna Bark',
                'category_slug' => 'arjuna-bark',
                'brand' => 'Biswas Organics',
                'price' => 750,
                'regular_price' => 820,
                'rating' => 5,
                'reviews_count' => 24,
                'stock_quantity' => 35,
                'stock_status' => 'in-stock',
                'badge' => 'POPULAR',
                'badge_type' => 'sale',
                'description' => 'Selected thick cut medicinal-grade Arjuna bark rich in tannins and flavonoids for natural cardiovascular health.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1563865436874-9aef32095fad?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 103,
                'name' => 'Premium Quality Arjuna Bark',
                'category' => 'Arjuna Bark',
                'category_slug' => 'arjuna-bark',
                'brand' => 'Biswas Organics',
                'price' => 790,
                'regular_price' => 890,
                'rating' => 5,
                'reviews_count' => 18,
                'stock_quantity' => 20,
                'stock_status' => 'in-stock',
                'badge' => 'PREMIUM',
                'badge_type' => 'sale',
                'description' => 'Export-quality sun-dried Terminalia Arjuna tree bark strips carefully cleaned and sorted for pharmaceutical and herbal use.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 104,
                'name' => 'Harad Powder',
                'category' => 'Herbs Powder',
                'category_slug' => 'herbs-powder',
                'brand' => 'Biswas Organics',
                'price' => 350,
                'regular_price' => 420,
                'rating' => 5,
                'reviews_count' => 41,
                'stock_quantity' => 40,
                'stock_status' => 'in-stock',
                'badge' => 'BEST SELLER',
                'badge_type' => 'sale',
                'description' => 'Pure blended Harad (Haritaki) powder from Kolkata. Promotes digestive wellness, detoxification, and natural rejuvenation.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 105,
                'name' => 'Neem Powder',
                'category' => 'Herbs Powder',
                'category_slug' => 'herbs-powder',
                'brand' => 'Heritage Botanicals',
                'price' => 299,
                'regular_price' => 380,
                'rating' => 5,
                'reviews_count' => 29,
                'stock_quantity' => 25,
                'stock_status' => 'in-stock',
                'badge' => 'POPULAR',
                'badge_type' => 'sale',
                'description' => 'Fine micro-powdered organic Neem leaves. Antibacterial, antifungal & antioxidant for skincare, haircare, and Ayurvedic remedies.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 106,
                'name' => 'Ashwagandha Root Powder',
                'category' => 'Herbs Powder',
                'category_slug' => 'herbs-powder',
                'brand' => 'Biswas Organics',
                'price' => 599,
                'regular_price' => 749,
                'rating' => 5,
                'reviews_count' => 28,
                'stock_quantity' => 15,
                'stock_status' => 'in-stock',
                'badge' => 'BEST SELLER',
                'badge_type' => 'sale',
                'description' => 'Pure premium Ashwagandha (Withania Somnifera) root powder, traditional revitalizing herb for energy, stamina, and stress management.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 107,
                'name' => 'Organic Triphala Powder',
                'category' => 'Herbs Powder',
                'category_slug' => 'herbs-powder',
                'brand' => 'Biswas Organics',
                'price' => 349,
                'regular_price' => 429,
                'rating' => 5,
                'reviews_count' => 38,
                'stock_quantity' => 25,
                'stock_status' => 'in-stock',
                'badge' => 'POPULAR',
                'badge_type' => 'sale',
                'description' => 'Balanced classic formulation of Amla, Haritaki, and Bibhitaki for gentle gut cleansing and daily digestive harmony.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 108,
                'name' => 'Natural Reetha Soap Nuts',
                'category' => 'Dried Herbs',
                'category_slug' => 'dried-herbs',
                'brand' => 'Pure Herbs Co.',
                'price' => 280,
                'regular_price' => 340,
                'rating' => 4,
                'reviews_count' => 22,
                'stock_quantity' => 30,
                'stock_status' => 'in-stock',
                'badge' => 'ECO CHOICE',
                'badge_type' => 'sale',
                'description' => '90% pure medicine grade Reetha (Soapnut) shells. 100% natural chemical-free organic cleanser for hair washing and delicate fabrics.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1512290900673-7002ddb97b09?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 109,
                'name' => 'Dried Tulsi Leaves',
                'category' => 'Dried Herbs',
                'category_slug' => 'dried-herbs',
                'brand' => 'Pure Herbs Co.',
                'price' => 299,
                'regular_price' => 349,
                'rating' => 4,
                'reviews_count' => 19,
                'stock_quantity' => 12,
                'stock_status' => 'in-stock',
                'badge' => 'FRESH HARVEST',
                'badge_type' => 'sale',
                'description' => 'Handpicked shade-dried sacred Rama & Krishna Tulsi leaves for natural immunity teas and respiratory wellness.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 110,
                'name' => 'Dried Neem Leaves',
                'category' => 'Dried Herbs',
                'category_slug' => 'dried-herbs',
                'brand' => 'Heritage Botanicals',
                'price' => 249,
                'regular_price' => 299,
                'rating' => 5,
                'reviews_count' => 31,
                'stock_quantity' => 18,
                'stock_status' => 'in-stock',
                'badge' => 'POPULAR',
                'badge_type' => 'sale',
                'description' => '99% pure sun-dried green Neem leaves. Essential for therapeutic herbal baths, skin detox, and botanical infusions.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 111,
                'name' => 'Solar LED Street Light',
                'category' => 'Renewable Energy Products',
                'category_slug' => 'renewable-energy',
                'brand' => 'Biswas Eco Tech',
                'price' => 3499,
                'regular_price' => 4200,
                'rating' => 5,
                'reviews_count' => 42,
                'stock_quantity' => 15,
                'stock_status' => 'in-stock',
                'badge' => 'BEST SELLER',
                'badge_type' => 'sale',
                'description' => 'Integrated aluminum & polycrystalline silicon solar LED street light. High lumen output, dusk-to-dawn sensor, and weather resistance.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1545259741-2ea3ebf61fa3?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 112,
                'name' => 'Solar Power Battery',
                'category' => 'Renewable Energy Products',
                'category_slug' => 'renewable-energy',
                'brand' => 'Biswas Eco Tech',
                'price' => 5899,
                'regular_price' => 6999,
                'rating' => 5,
                'reviews_count' => 27,
                'stock_quantity' => 8,
                'stock_status' => 'in-stock',
                'badge' => 'HIGH CYCLE',
                'badge_type' => 'sale',
                'description' => 'Heavy-duty Lithium-ion & deep-cycle lead-acid solar storage battery (12V/24V/48V) for reliable off-grid power storage.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 113,
                'name' => 'Solar PV Panel',
                'category' => 'Renewable Energy Products',
                'category_slug' => 'renewable-energy',
                'brand' => 'Biswas Eco Tech',
                'price' => 4200,
                'regular_price' => 4999,
                'rating' => 5,
                'reviews_count' => 35,
                'stock_quantity' => 20,
                'stock_status' => 'in-stock',
                'badge' => 'ECO POWER',
                'badge_type' => 'sale',
                'description' => 'High efficiency silicon & tempered glass solar photovoltaic panel for residential, commercial & industrial power generation.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1509391365360-2e959784a276?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ],
            [
                'id' => 114,
                'name' => 'Solar Emergency LED Lantern',
                'category' => 'Renewable Energy Products',
                'category_slug' => 'renewable-energy',
                'brand' => 'Biswas Eco Tech',
                'price' => 1299,
                'regular_price' => 1599,
                'rating' => 5,
                'reviews_count' => 19,
                'stock_quantity' => 18,
                'stock_status' => 'in-stock',
                'badge' => 'PORTABLE',
                'badge_type' => 'sale',
                'description' => 'Multi-functional solar rechargeable LED lantern with mobile charging USB output port for camping and power outages.',
                'image' => cloudinary_url('https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=600&q=80', ['width' => 600, 'height' => 600])
            ]
        ];
    }
}

if (!function_exists('getProductById')) {
    function getProductById(int $id): ?array {
        $products = getAllProducts();
        foreach ($products as $p) {
            if ((int)$p['id'] === $id) {
                return $p;
            }
        }
        return null;
    }
}

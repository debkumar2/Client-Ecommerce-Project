/**
 * API Client Configuration
 * 
 * Handles requests to PHP backend API endpoints.
 * Fallback to development mock data when endpoints are not available.
 */

const API_BASE_URL = '/api';

export const fetchAPI = async (endpoint, options = {}) => {
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP Error ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.warn(`API call failed for ${endpoint}. Using development mock data context.`, error.message);
        return null;
    }
};

// Category Endpoint Integration Point
export const getCategories = async () => {
    const data = await fetchAPI('/categories');
    if (data && data.success) return data.data;

    // DEVELOPMENT DATA - TODO: Replace with backend API response
    return [
        { id: 1, name: 'Herbal Products', slug: 'herbal-products', description: 'Pure herbal formulations for daily wellness', image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80' },
        { id: 2, name: 'Dried Herbs', slug: 'dried-herbs', description: 'Carefully dried, raw natural herbs', image: 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80' },
        { id: 3, name: 'Herbal Powders', slug: 'herbal-powders', description: 'Finely ground single-herb powders', image: 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80' },
        { id: 4, name: 'Wellness Products', slug: 'wellness-products', description: 'Natural care for health & body', image: 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80' }
    ];
};

// Featured Products Endpoint Integration Point
export const getFeaturedProducts = async () => {
    const data = await fetchAPI('/products?featured=1');
    if (data && data.success) return data.data;

    // DEVELOPMENT DATA - TODO: Replace with backend API response
    return [
        {
            id: 101,
            name: 'Ashwagandha Root Powder',
            category: 'Herbal Powders',
            price: 599,
            regular_price: 749,
            rating: 4.9,
            reviews_count: 28,
            stock_quantity: 15,
            badge: 'BEST SELLER',
            image: 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80'
        },
        {
            id: 102,
            name: 'Organic Neem Leaf Extract',
            category: 'Herbal Products',
            price: 399,
            regular_price: 499,
            rating: 4.8,
            reviews_count: 14,
            stock_quantity: 8,
            badge: 'POPULAR',
            image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80'
        },
        {
            id: 103,
            name: 'Dried Tulsi Leaves',
            category: 'Dried Herbs',
            price: 299,
            regular_price: 349,
            rating: 4.7,
            reviews_count: 19,
            stock_quantity: 0, // OUT OF STOCK EXAMPLE
            badge: 'OUT OF STOCK',
            image: 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80'
        },
        {
            id: 104,
            name: 'Amla Herbal Vitality Oil',
            category: 'Wellness Products',
            price: 699,
            regular_price: 899,
            rating: 5.0,
            reviews_count: 32,
            stock_quantity: 22,
            badge: 'NEW',
            image: 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80'
        }
    ];
};

// Newsletter Endpoint Integration Point
export const subscribeNewsletter = async (email) => {
    const data = await fetchAPI('/newsletter', {
        method: 'POST',
        body: JSON.stringify({ email })
    });
    
    if (data) return data;
    
    // DEVELOPMENT MOCK RESPONSE
    return new Promise(resolve => {
        setTimeout(() => {
            resolve({ success: true, message: 'Thank you for subscribing to Biswas Enterprise newsletter!' });
        }, 600);
    });
};

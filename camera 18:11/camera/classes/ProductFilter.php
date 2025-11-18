<?php
/**
 * ProductFilter Class
 * Handles product filtering and sorting logic
 */
class ProductFilter {
    private $products = [];
    private $category = '';
    private $brands = [];
    private $search = '';
    private $sort = 'newest';
    private $priceMin = 0;
    private $priceMax = PHP_INT_MAX;
    private $validSortOptions = ['newest', 'name', 'price_low', 'price_high'];
    
    /**
     * Constructor
     * @param array $products Array of products to filter
     */
    public function __construct($products = []) {
        $this->products = $products;
    }
    
    /**
     * Set filter parameters
     * @param array $params Filter parameters array
     */
    public function setFilters($params) {
        if (isset($params['category']) && !empty($params['category'])) {
            $this->category = htmlspecialchars($params['category']);
        }
        
        if (isset($params['brands']) && !empty($params['brands'])) {
            $brands = $params['brands'];
            // Handle both array and string input
            if (is_array($brands)) {
                $this->brands = array_map('htmlspecialchars', $brands);
            } else {
                $this->brands = [htmlspecialchars($brands)];
            }
        }
        
        if (isset($params['search']) && !empty($params['search'])) {
            $this->search = htmlspecialchars($params['search']);
        }
        
        if (isset($params['sort']) && in_array($params['sort'], $this->validSortOptions)) {
            $this->sort = $params['sort'];
        }
        
        if (isset($params['price_min']) && is_numeric($params['price_min'])) {
            $this->priceMin = (int)$params['price_min'];
        }
        
        if (isset($params['price_max']) && is_numeric($params['price_max'])) {
            $this->priceMax = (int)$params['price_max'];
        }
    }
    
    /**
     * Set products array
     * @param array $products Products array
     */
    public function setProducts($products) {
        $this->products = $products;
    }
    
    /**
     * Filter products by search term
     * @param array $products Products to filter
     * @return array Filtered products
     */
    public function filterBySearch($products = null) {
        $items = $products ?? $this->products;
        
        if (empty($this->search)) {
            return $items;
        }
        
        return array_filter($items, function($product) {
            $search = strtolower($this->search);
            $name = strtolower($product['name'] ?? '');
            $description = strtolower($product['description'] ?? '');
            return strpos($name, $search) !== false || strpos($description, $search) !== false;
        });
    }
    
    /**
     * Filter products by category
     * @param array $products Products to filter
     * @return array Filtered products
     */
    public function filterByCategory($products) {
        if (empty($this->category)) {
            return $products;
        }
        
        return array_filter($products, function($product) {
            return ($product['category'] ?? '') === $this->category;
        });
    }
    
    /**
     * Filter products by brand (supports multiple brands)
     * @param array $products Products to filter
     * @return array Filtered products
     */
    public function filterByBrand($products) {
        if (empty($this->brands)) {
            return $products;
        }
        
        return array_filter($products, function($product) {
            // Check if product has brand_id
            $brandId = $product['brand_id'] ?? null;
            $brandName = $product['brand_name'] ?? '';
            
            // Check if product's brand is in the selected brands list
            foreach ($this->brands as $selectedBrand) {
                if ($brandName === $selectedBrand || (int)$brandId === (int)$selectedBrand) {
                    return true;
                }
            }
            return false;
        });
    }
    
    /**
     * Filter products by price range
     * @param array $products Products to filter
     * @return array Filtered products
     */
    public function filterByPrice($products) {
        return array_filter($products, function($product) {
            $price = (float)($product['price'] ?? 0);
            return $price >= $this->priceMin && $price <= $this->priceMax;
        });
    }
    
    /**
     * Apply all filters
     * @return array Filtered products
     */
    public function apply() {
        $result = $this->products;
        
        // Apply search filter
        $result = $this->filterBySearch($result);
        
        // Apply category filter
        $result = $this->filterByCategory($result);
        
        // Apply brand filter
        $result = $this->filterByBrand($result);
        
        // Apply price filter
        $result = $this->filterByPrice($result);
        
        // Apply sorting
        $result = $this->sort($result);
        
        return $result;
    }
    
    /**
     * Sort products
     * @param array $products Products to sort
     * @return array Sorted products
     */
    public function sort($products) {
        $sorted = $products;
        
        switch ($this->sort) {
            case 'price_low':
                usort($sorted, function($a, $b) {
                    return $a['price'] - $b['price'];
                });
                break;
            case 'price_high':
                usort($sorted, function($a, $b) {
                    return $b['price'] - $a['price'];
                });
                break;
            case 'name':
                usort($sorted, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                break;
            case 'newest':
            default:
                // Already sorted by created_at DESC in query
                break;
        }
        
        return $sorted;
    }
    
    /**
     * Get unique categories from products
     * @param array $products Products array
     * @return array Unique categories
     */
    public static function getUniqueCategories($products) {
        $categories = [];
        foreach ($products as $product) {
            if (!empty($product['category']) && !in_array($product['category'], $categories)) {
                $categories[] = $product['category'];
            }
        }
        sort($categories);
        return $categories;
    }
    
    /**
     * Get getter methods
     */
    public function getCategory() {
        return $this->category;
    }
    
    public function getBrands() {
        return $this->brands;
    }
    
    public function getSearch() {
        return $this->search;
    }
    
    public function getSort() {
        return $this->sort;
    }
    
    public function getPriceMin() {
        return $this->priceMin;
    }
    
    public function getPriceMax() {
        return $this->priceMax;
    }
    
    public function getValidSortOptions() {
        return $this->validSortOptions;
    }
}
?>

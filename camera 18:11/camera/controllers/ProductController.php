<?php
/**
 * ProductController Class
 * Handles product page logic and data preparation
 */
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/ProductFilter.php';
require_once __DIR__ . '/../classes/Brand.php';
require_once __DIR__ . '/../classes/Request.php';
require_once __DIR__ . '/../classes/Page.php';
require_once __DIR__ . '/../config/database.php';

class ProductController {
    private $productModel;
    private $brandModel;
    private $request;
    private $page;
    private $filter;
    private $products = [];
    private $categories = [];
    private $brands = [];
    private $pageTitle = 'Sản Phẩm - XEDIC Camera';
    private $pageDescription = 'Danh sách sản phẩm camera chuyên nghiệp';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->productModel = new Product();
        $this->brandModel = new Brand();
        $this->request = new Request();
        $this->page = new Page();
        $this->filter = new ProductFilter();
        
        // Set page info
        $this->page->setTitle($this->pageTitle);
        $this->page->setDescription($this->pageDescription);
    }
    
    /**
     * Initialize controller and prepare data
     * @return array Data to pass to view
     */
    public function init() {
        // Get all products first
        $allProducts = $this->productModel->getAllProducts();
        
        // Enrich products with brand information
        $allProducts = $this->enrichProductsWithBrands($allProducts);
        
        // Get all categories from database (not just from products)
        $this->categories = $this->getAllCategoriesFromDB();
        
        // Get all brands from database
        $this->brands = $this->getAllBrandsFromDB();
        
        // Set filter parameters from request
        $filterParams = [
            'category' => $this->request->get('category'),
            'brands' => $this->request->get('brands'), // Now gets array
            'search' => $this->request->get('search'),
            'sort' => $this->request->get('sort', 'newest'),
            'price_min' => $this->request->get('price_min', 0),
            'price_max' => $this->request->get('price_max', 100000000)
        ];
        
        $this->filter->setFilters($filterParams);
        
        // Apply filters
        $this->filter->setProducts($allProducts);
        $this->products = $this->filter->apply();
        
        return $this->getViewData();
    }
    
    /**
     * Enrich products with brand information
     * @param array $products Products array
     * @return array Enriched products
     */
    private function enrichProductsWithBrands($products) {
        foreach ($products as &$product) {
            if (!empty($product['brand_id'])) {
                $brand = $this->brandModel->getBrandById($product['brand_id']);
                if ($brand) {
                    $product['brand_name'] = $brand['name'];
                    $product['brand_slug'] = $brand['slug'];
                }
            }
        }
        return $products;
    }
    
    /**
     * Get heading text based on filters
     * @return string Heading text
     */
    public function getHeading() {
        $search = $this->filter->getSearch() ?? '';
        // Avoid assigning from filter->getCategory() if it is declared void in some implementations;
        // fall back to the request value which was used to populate filters.
        $category = $this->request->get('category');
        
        if (!empty($search)) {
            return "Kết quả tìm kiếm: " . htmlspecialchars($search);
        } elseif (!empty($category)) {
            return htmlspecialchars($category);
        } else {
            return "Sản Phẩm Camera & Phụ kiện";
        }
    }
    
    /**
     * Get data for view
     * @return array View data
     */
    public function getViewData() {
        return [
            'page' => $this->page,
            'products' => $this->products,
            'categories' => $this->categories,
            'brands' => $this->brands,
            'category' => $this->filter->getCategory(),
            'brands_selected' => $this->filter->getBrands(), // For checked status in view
            'search' => $this->filter->getSearch(),
            'sort' => $this->filter->getSort(),
            'price_min' => $this->filter->getPriceMin(),
            'price_max' => $this->filter->getPriceMax(),
            'validSortOptions' => $this->filter->getValidSortOptions(),
            'heading' => $this->getHeading(),
            'totalProducts' => count($this->products),
            'request' => $this->request
        ];
    }
    
    /**
     * Get page object
     * @return Page Page instance
     */
    public function getPage() {
        return $this->page;
    }
    
    /**
     * Get products
     * @return array Products array
     */
    public function getProducts() {
        return $this->products;
    }
    
    /**
     * Get categories from database
     * @return array Categories array
     */
    private function getAllCategoriesFromDB() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $stmt = $pdo->query("SELECT name FROM categories WHERE is_active = 1 ORDER BY display_order ASC");
            $categories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = $row['name'];
            }
            return $categories;
        } catch (Exception $e) {
            // Fallback to extracting from products if database query fails
            return ProductFilter::getUniqueCategories($this->productModel->getAllProducts());
        }
    }
    
    /**
     * Get brands from database
     * @return array Brands array
     */
    private function getAllBrandsFromDB() {
        try {
            return $this->brandModel->getAllBrands();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get brands
     * @return array Brands array
     */
    public function getBrands() {
        return $this->brands;
    }
}

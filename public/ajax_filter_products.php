<?php
/**
 * AJAX endpoint for filtering products without page reload
 * Returns JSON response with filtered products and pagination
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/includes/config.php';
require_once __DIR__ . '/../config/utils/Pagination.php';

// Get search and filter parameters
$search = trim($_GET['search'] ?? '');
$min_price = isset($_GET['min_price']) ? max(0, (float) $_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? max(0, (float) $_GET['max_price']) : 999999999;
$stock_filter = $_GET['stock'] ?? ''; // 'in_stock', 'low_stock', 'all'
$current_page = max(1, (int) ($_GET['page'] ?? 1));

// Build WHERE clause for filtering
$where_conditions = [];
$params = [];
$param_types = '';

// Search by product name or description
if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(products.name LIKE ? OR products.description LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

// Price filter
if ($min_price > 0 || $max_price < 999999999) {
    $where_conditions[] = "products.price BETWEEN ? AND ?";
    $params[] = $min_price;
    $params[] = $max_price;
    $param_types .= 'dd';
}

// Stock filter
if ($stock_filter === 'in_stock') {
    $where_conditions[] = "products.stock > 0";
} elseif ($stock_filter === 'low_stock') {
    $where_conditions[] = "products.stock > 0 AND products.stock <= 10";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total products matching filters
$count_query_sql = "SELECT COUNT(*) as total FROM products JOIN users ON products.user_id = users.id $where_clause";
$count_query = $conn->prepare($count_query_sql);

if ($count_query && !empty($params)) {
    $count_query->bind_param($param_types, ...$params);
}

$total_products = 0;
if ($count_query) {
    $count_query->execute();
    $count_result = $count_query->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_products = $count_row['total'];
    $count_query->close();
}

// Pagination settings
$items_per_page = 12;
$pagination = new Pagination($total_products, $items_per_page, $current_page);

// Get products with pagination
$products_query_sql = "SELECT products.*, users.store_name, users.phone as seller_phone 
FROM products JOIN users ON products.user_id = users.id $where_clause 
ORDER BY products.created_at DESC 
LIMIT ? OFFSET ?";

$products_query = $conn->prepare($products_query_sql);
$products_data = [];

if ($products_query) {
    // Add limit and offset parameters
    $offset = $pagination->getOffset();
    $limit = $pagination->getLimit();
    $all_params = $params;
    $all_params[] = $limit;
    $all_params[] = $offset;
    $all_param_types = $param_types . 'ii';

    if (!empty($all_params)) {
        $products_query->bind_param($all_param_types, ...$all_params);
    }

    $products_query->execute();
    $products = $products_query->get_result();

    while ($product = $products->fetch_assoc()) {
        // Determine image path with fallback chain
        $image_src = '../assets/images/placeholder-product.svg';

        if (!empty($product['image'])) {
            $thumb_path = '../assets/uploads/thumbs/' . $product['image'];
            $original_path = '../assets/uploads/' . $product['image'];

            if (file_exists(__DIR__ . '/' . $thumb_path)) {
                $image_src = $thumb_path;
            } elseif (file_exists(__DIR__ . '/' . $original_path)) {
                $image_src = $original_path;
            }
        }

        // Format phone number for WhatsApp
        $whatsapp_phone = !empty($product['seller_phone']) ? $product['seller_phone'] : '628123456789';
        if (substr($whatsapp_phone, 0, 1) === '0') {
            $whatsapp_phone = '62' . substr($whatsapp_phone, 1);
        }

        $products_data[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'image' => $image_src,
            'store_name' => $product['store_name'],
            'seller_phone' => $whatsapp_phone
        ];
    }

    $products_query->close();
}

// Build pagination HTML
$extra_params = [];
if (!empty($search))
    $extra_params['search'] = $search;
if ($min_price > 0)
    $extra_params['min_price'] = $min_price;
if ($max_price < 999999999)
    $extra_params['max_price'] = $max_price;
if (!empty($stock_filter))
    $extra_params['stock'] = $stock_filter;

$pagination_html = $pagination->getTotalPages() > 1 ? $pagination->render('index.php', 'page', $extra_params) : '';

// Return JSON response
echo json_encode([
    'success' => true,
    'total' => $total_products,
    'current_page' => $current_page,
    'total_pages' => $pagination->getTotalPages(),
    'start_item' => $pagination->getStartItem(),
    'end_item' => $pagination->getEndItem(),
    'products' => $products_data,
    'pagination_html' => $pagination_html,
    'has_active_filters' => !empty($search) || $min_price > 0 || $max_price < 999999999 || !empty($stock_filter)
]);

$conn->close();

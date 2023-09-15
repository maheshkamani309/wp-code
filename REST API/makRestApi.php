<?php


/**
 * To add custom WP REST API endpoint
 * 
 * @author Mahesh Kamani
 */

class makRestApi
{

    /**
     * To add actions which is required to add custom rest api endpoint
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerEndPoint']);
    }


    /**
     * To register endpoint to the WP REST API
     */
    public function registerEndPoint()
    {

        // Get wishlist of user.
        register_rest_route('product-wishlist/v1', '/get', array(
            'methods'   => 'GET', // Method of Request POST, PUT, GET, DELETE, OPTIONS
            'callback'  => [$this, 'getWishList'], // Methos to call 
            'args'      => array(
                'user_id'   => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        // Add product to wishlist
        register_rest_route('product-wishlist/v1', '/add', array(
            'methods' => 'POST',
            'callback' => [$this, 'addProductToWishList'],
            'args' => array(
                'user_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'product_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
    }


    /**
     * To get list of the wishlist products
     * 
     * @param WP_REST_Request $request This function accepts a rest request to process data.
     * 
     * @return WP_REST_Response 
     */
    public function getWishList($request)
    {
        $user_id = $request->get_param('user_id'); // Get parameter from the request

        $wishlist_products = get_user_meta($user_id, 'wishlist_products', true);
        if (!$wishlist_products) {
            $wishlist_products = array();
        }
        $products = $this->productsDetail($wishlist_products);

        $response = array('products' => $products);
        wp_send_json_success($response);
    }


    /**
     * To add product to wishlist
     * 
     * @param WP_REST_Request $request This function accepts a rest request to process data.
     * 
     * @return WP_REST_Response|WP_Error
     */
    public function addProductToWishList($request)
    {
        $user_id = $request->get_param('user_id');
        $product_id = $request->get_param('product_id');


        $product = wc_get_product($product_id);
        if (!$product) {
            return new WP_Error('rest_product_invalid', esc_html__('The product does not exist.', 'my-text-domain'), array('status' => 404));
        }

        $wishlist_products = get_user_meta($user_id, 'wishlist_products', true);
        if (!$wishlist_products) {
            $wishlist_products = array();
        }

        // Check if the product is not already in the favorites
        if (!in_array($product_id, $wishlist_products)) {
            $wishlist_products[] = $product_id;
            update_user_meta($user_id, 'wishlist_products', $wishlist_products);
        }

        $products = $this->productsDetail($wishlist_products);
        $response = array('message' => 'Product added to wishlist', 'products' => $products);
        wp_send_json_success($response);
    }


    /**
     * To prepare array of the products 
     * 
     * @param array $product_ids Ids of the products 
     * 
     * @return array Array of the products information
     */
    public function productsDetail(array $product_ids): array
    {
        $products = array();
        if (!empty($product_ids)) {
            $args = array(
                'post_type' => 'product',
                'post__in' => $product_ids,
                'posts_per_page' => -1,
            );

            $query = new WP_Query($args);
            if (count($query->posts) > 0) {
                foreach ($query->posts as $post) {
                    $_product = wc_get_product($post->ID);
                    $products[] = array(
                        'title'         => $post->post_title,
                        'price'         => $_product->get_price(),
                    );
                }
            }
        }
        return $products;
    }
}
new makRestApi();

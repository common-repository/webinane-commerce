<?php
/**
 * Orders.
 *
 * @package WordPress
 */

namespace WebinaneCommerce\Classes;

use WeDevs\ORM\Eloquent\Facades\DB;
use WebinaneCommerce\Models\Customer;
use WebinaneCommerce\Models\Order as OrderModel;
use WebinaneCommerce\Models\OrderItems;

class Orders {

	static $export_page;
	/**
	 * [$item_table description]
	 *
	 * @var string
	 */
	protected static $item_table = '';

	/**
	 * [$item_meta_table description]
	 *
	 * @var string
	 */
	protected static $item_meta_table = '';

	/**
	 * [init description]
	 *
	 * @return [type] [description]
	 */
	public static function init() {
		global $wpdb;

		self::$item_table = $wpdb->prefix . 'wpcommerce_order_items';
		self::$item_meta_table = $wpdb->prefix . 'wpcommerce_order_itemmeta';

		add_action( 'init', array( __CLASS__, 'register_post_type' ) );

		add_action( 'wpcm_after_order_success_content', array( __CLASS__, 'order_detail' ) );

		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ), 1000 );

		add_action( 'wp_ajax__admin_orders_fields', array( __CLASS__, 'save_admin_orders_field' ) );
		add_action( 'wp_ajax__wpcommerce_admin_orders_data', array( __CLASS__, 'order_admin_data' ) );
		add_action( 'wp_ajax__wpcommerce_admin_order_update_general', array( __CLASS__, 'update_order_general' ) );

		add_action( 'wp_ajax__wpcommerce_admin_order_update_item', array( __CLASS__, 'update_order_items' ) );
		add_action( 'wp_ajax__wpcommerce_admin_order_give_refund', array( __CLASS__, 'give_refund' ) );

		add_action( 'wp_ajax__wpcommerce_admin_order_get_products', array( __CLASS__, 'get_products' ) );
		add_action( 'wp_ajax__wpcommerce_admin_order_add_new_item', array( __CLASS__, 'admin_add_new_item' ) );
		add_action( 'wp_ajax__wpcommerce_admin_order_add_note', array( __CLASS__, 'admin_add_note' ) );
		add_action( 'wp_ajax__wpcommerce_admin_order_get_notes', array( __CLASS__, 'admin_get_notes' ) );
		add_action( 'wp_ajax__wpcommerce_admin_order_remove_note', array( __CLASS__, 'admin_remove_note' ) );
		add_action( 'wp_ajax__wpcommerce_admin_order_delete', array( __CLASS__, 'admin_delete_order' ) );
		add_action( 'wp_ajax__wpcommerce_admin_orders_customer_data', array( __CLASS__, 'customer_data' ) );

		add_filter( 'manage_orders_posts_columns', array( __CLASS__, 'cpt_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'custom_columns' ) );

		add_action('admin_print_footer_scripts', array( __CLASS__, 'export_button' ) );
		add_action('wpcommerce_admin_page', array( __CLASS__, 'register_pages' ), 11 );
	}

	/**
	 * Enqueue script and styles.
	 */
	public static function enqueue() {
		global $post_type;

		if ( $post_type !== 'orders' ) {
			return;
		}

		$version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : WPCM_VERSION;

		wp_enqueue_style( array( 'element-ui' ) );

		wp_enqueue_style( 'wpcm-bootstrap', WNCM_URL . 'assets/css/bootstrap.min.css', '', $version );
		// wp_enqueue_style('bootstrap-datetimepicker', WNCM_URL . 'assets/css/bootstrap-datetimepicker.min.css', '', $version);
		wp_enqueue_style( 'fontawesome', WNCM_URL . 'assets/css/fontawesome.min.css', '', $version );

		wp_enqueue_style( 'wpcm_main', WNCM_URL . 'assets/css/main.css', '', $version );
		wp_enqueue_style( 'wpcm_style', WNCM_URL . 'assets/css/style.css', '', $version );
		wp_enqueue_style( 'wpcm_responsive', WNCM_URL . 'assets/css/responsive.css', '', $version );

		if ( function_exists( 'wp_set_script_translations' ) ) {
			/**
			 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
			 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
			 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
			 */
			wp_set_script_translations( 'admin_orders', 'webinane-commerce' );
		}

	}
	/**
	 * Add metaboxes for order view.
	 */
	public static function add_metabox() {

		$title = apply_filters( 'wpcommerce_admin_orders_metabox_title', esc_html__( 'Order Detail', 'webinane-commerce' ) );
		add_meta_box( 'wpcm_order_metabox_basic', $title, array( __CLASS__, 'basic_metabox' ), 'orders', 'advanced', 'high' );
		// add_meta_box( 'wpcm_order_metabox_items', esc_html__( 'Order Items', 'webinane-commerce' ), array(__CLASS__, 'items_metabox'), 'orders', 'advanced' );

		remove_meta_box( 'submitdiv', 'orders', 'side' );
	}

	/**
	 * HTML output for basic metabox of orders.
	 *
	 * @param  object $post [description]
	 * @return void         [description]
	 */
	public static function basic_metabox( $post ) {

		$trans = include WNCM_PATH . 'config/i18n.php';
		$version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : WPCM_VERSION;
		$settings = wpcm_get_settings();

		$symbol = webinane_currency_symbol();
		$position = $settings->get( 'currency_position', 'left' );
		$sep = $settings->get( 'thousand_saparator', '' ); // Thousand Separator
		$d_sep = $settings->get( 'decimal_separator', '.' ); // Decimal separator
		$d_point = $settings->get( 'number_decimals', 0 ); // Decimal numbers
		$data = array(
			'nonce'         => wp_create_nonce( WPCM_GLOBAL_KEY ),
			'ajax_action'   => WPCM_AJAX_ACTION,
			'symbol'        => $symbol,
			'position'      => $position,
			'sep'           => $sep,
			'd_sep'         => $d_sep,
			'd_point'       => $d_point,
		);

		wp_localize_script( 'vuejs', 'wpcm_data', $data );
		wp_enqueue_script( array( 'wp-blocks', 'wp-i18n', 'wp-element' ) );
		wp_enqueue_script( 'vuejs', WNCM_URL . 'assets/js/common/vue.js', array( 'wp-blocks', 'wp-i18n', 'wp-element' ), $version, true );
		wp_enqueue_script( 'bootstrap', WNCM_URL . 'assets/js/common/bootstrap.min.js', array( 'jquery' ), $version, true );
		wp_enqueue_script( 'moment', WNCM_URL . 'assets/js/common/moment.min.js', array( 'jquery' ), $version, true );
		wp_enqueue_script( 'bootstrap-datetimepicker', WNCM_URL . 'assets/js/common/bootstrap-datetimepicker.min.js', array( 'bootstrap' ), $version, true );
		// wp_enqueue_script( 'script', WNCM_URL . 'assets/js/common/script.min.js' );
		wp_enqueue_script( 'element-ui', WNCM_URL . 'assets/js/common/element-ui.min.js', array( 'vuejs' ), $version, true );
		wp_enqueue_script( 'element-ui-en', '//unpkg.com/element-ui/lib/umd/locale/en.js', array( 'jquery', 'vuejs', 'element-ui' ), $version, true );
		wp_enqueue_script( 'wpcommerce_components', WNCM_URL . 'assets/js/components.js', array( 'wp-element' ), $version, true );
		wp_enqueue_script( 'admin_orders', WNCM_URL . 'assets/js/admin/orders.js', array( 'wp-element', 'element-ui-en' ), $version, true );

		// $order = self::order_data($post);
		// $customer = self::get_customer_detail($post);

		webinane_template( 'admin/order-metabox-basic.php' );
	}

	/**
	 * Output for order items data.
	 *
	 * @param  object $post [description]
	 * @return void         [description]
	 */
	/*
	static function items_metabox($post) {
		$order = self::order_data($post);
		$customer = self::get_customer_detail($post);

		include WNCM_PATH . 'templates/admin/order-metabox-items.php';
	}*/

	/**
	 * Register orders post type.
	 *
	 * @return [type] [description]
	 */
	public static function register_post_type() {

		/**
		 * Registers a new post type
		 *
		 * @uses $wp_post_types Inserts new post type object into the list
		 *
		 * @param string  Post type key, must not exceed 20 characters
		 * @param array|string  See optional args description above.
		 * @return object|WP_Error the registered post type object, or an error object
		 */

		$labels = array(
			'name'               => esc_html__( 'Orders', 'webinane-commerce' ),
			'singular_name'      => esc_html__( 'Order', 'webinane-commerce' ),
			'add_new'            => _x( 'Add New Order', 'webinane-commerce', 'webinane-commerce' ),
			'add_new_item'       => esc_html__( 'Add New Order', 'webinane-commerce' ),
			'edit_item'          => esc_html__( 'Edit Order', 'webinane-commerce' ),
			'new_item'           => esc_html__( 'New Order', 'webinane-commerce' ),
			'view_item'          => esc_html__( 'View Order', 'webinane-commerce' ),
			'search_items'       => esc_html__( 'Search Orders', 'webinane-commerce' ),
			'not_found'          => esc_html__( 'No Orders found', 'webinane-commerce' ),
			'not_found_in_trash' => esc_html__( 'No Orders found in Trash', 'webinane-commerce' ),
			'parent_item_colon'  => esc_html__( 'Parent Order:', 'webinane-commerce' ),
			'menu_name'          => esc_html__( 'Orders', 'webinane-commerce' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => 'description',
			'taxonomies'          => array(),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'true',
			'show_in_admin_bar'   => false,
			'menu_position'       => null,
			'menu_icon'           => 'dashicons-chart-area',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'revisions' ),
		);

		$args = apply_filters( 'wpcm_post_type_orders_args', $args );

		register_post_type( 'orders', $args );

	}

	/**
	 * Add export button in order post type
	 *
	 * @return void
	 */
	public static function export_button(){
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		if( $screen->id == 'edit-orders' && $screen->post_type == 'orders' ){
			?>
				<script>
				var export_url = '<?php echo esc_url(admin_url('/admin.php?page=wp-commerce-export-orders&export_orders=true'));  ?>';
				var button_label = '<?php echo esc_html__('Export Orders', 'webinane-commerce'); ?>';
				jQuery('.wrap .wp-header-end').before('<a href="'+export_url+'" class="page-title-action">'+button_label+'</a>');
				</script>
			<?php
		}
	}

	/**
	 * Register pages in admin menu for settings.
	 *
	 * @return [type] [description]
	 */
	public static function register_pages() {
		if(!isset($_GET['export_orders']) ){
			return;
		}
		self::$export_page = add_submenu_page(
	        'wp-commerce-settings',
	        esc_html__( 'Export Orders', 'webinane-commerce' ),
	        esc_html__('Export Orders', 'webinane-commerce' ),
	        'manage_options',
	        'wp-commerce-export-orders',
	        array(__CLASS__, 'export_page'),
	        1000
		);

		add_action( 'admin_print_styles-'.self::$export_page, array(__CLASS__, 'export_order_custom_css') );
		add_action( 'admin_print_styles-'.self::$export_page, array(__CLASS__, 'export_order_custom_script') );
	}

	/**
	 * Export orders page style.
	 *
	 * @return [type] [description]
	 */
	public static function export_order_custom_css() {
		wp_enqueue_style( 'jquery-ui', WNCM_URL . 'assets/css/jquery-ui.min.css' );
		wp_enqueue_style( 'wpcommerce_export_order' );
	}

	/**
	 * Export orders page script.
	 *
	 * @return [type] [description]
	 */
	public static function export_order_custom_script() {
		wp_enqueue_script( array('jquery-ui-datepicker','wpcommerce_export_order')  );
	}

	/**
	 * Register export page in admin menu.
	 *
	 * @return [type] [description]
	 */
	public static function export_page() {
		?>
		<div class="wrap wpcommerce">
			<h1><?php esc_html_e( 'Export Orders', 'webinane-commerce' ); ?></h1>
			<div class="wpcommerce-exporter-wrapper">
				<form class="wpcommerce-exporter">
					<header>
						<h2><?php esc_html_e( 'Export orders to a CSV file', 'webinane-commerce' ); ?></h2>
						<p><?php esc_html_e( 'This tool allows you to generate and download a CSV file containing a list of all orders.', 'webinane-commerce' ); ?></p>
					</header>
					<section>
						<table class="form-table wpcommerce-exporter-options">
							<tbody>
								<tr>
									<th scope="row">
										<label for="wpcommerce-exporter-columns"><?php esc_html_e('Which columns should be exported?', 'webinane-commerce') ?></label>
									</th>
									<td>
										<select id="wpcommerce-exporter-columns" class="wpcommerce-exporter-columns wpcm-enhanced-select" style="width:100%;" multiple name="columns">
											<option value="ID"><?php esc_html_e( 'ID', 'webinane-commerce' ); ?></option>
											<option value="Name"><?php esc_html_e( 'Name', 'webinane-commerce' ); ?></option>
											<option value="Cost"><?php esc_html_e( 'Cost', 'webinane-commerce' ); ?></option>
											<option value="QTY"><?php esc_html_e( 'Qty', 'webinane-commerce' ); ?></option>
											<option value="Total"><?php esc_html_e( 'Total', 'webinane-commerce' ); ?></option>
											<option value="Currency"><?php esc_html_e( 'Currency', 'webinane-commerce' ); ?></option>
											<option value="Status"><?php esc_html_e( 'Status', 'webinane-commerce' ); ?></option>
											<option value="Date Created"><?php esc_html_e( 'Date Created', 'webinane-commerce' ); ?></option>
											<option value="Customer Name"><?php esc_html_e( 'Customer Name', 'webinane-commerce' ); ?></option>
											<option value="Email"><?php esc_html_e( 'Email', 'webinane-commerce' ); ?></option>
											<option value="Gateway"><?php esc_html_e( 'Gateway', 'webinane-commerce' ); ?></option>
											<option value="Recurring"><?php esc_html_e( 'Recurring', 'webinane-commerce' ); ?></option>
											<option value="Recurring Cycle"><?php esc_html_e( 'Recurring Cycle', 'webinane-commerce' ); ?></option>
											<option value="Recurring Frequency"><?php esc_html_e( 'Recurring Frequency', 'webinane-commerce' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="wpcommerce-exporter-types"><?php esc_html_e('Which order status should be exported?', 'webinane-commerce') ?></label>
									</th>
									<td>
										<select id="wpcommerce-exporter-types" class="wpcommerce-exporter-types wpcm-enhanced-select" style="width:100%;" multiple name="status">
											<?php
											$status = array(
												'pending_payment'           => esc_html__( 'Pending Payment', 'webinane-commerce' ),
												'processing'                => esc_html__( 'Processing', 'webinane-commerce' ),
												'hold'                      => esc_html__( 'On Hold', 'webinane-commerce' ),
												'completed'                 => esc_html__( 'Completed', 'webinane-commerce' ),
												'cancelled'                 => esc_html__( 'Cancelled', 'webinane-commerce' ),
												'refunded'                  => esc_html__( 'Refunded', 'webinane-commerce' ),
												'failed'                    => esc_html__( 'Failed', 'webinane-commerce' ),
											);
											foreach($status as $k => $v):
											?>
												<option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v) ?></option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="wpcommerce-exporter-date"><?php esc_html_e('Start Date', 'webinane-commerce') ?></label>
									</th>
									<td>
										<input type="text" id="wpcommerce-exporter-date1" class="wpcommerce-exporter-date wpcm-enhanced-date" name="st_date">
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="wpcommerce-exporter-date"><?php esc_html_e('End Date', 'webinane-commerce') ?></label>
									</th>
									<td>
										<input type="text" id="wpcommerce-exporter-date2" class="wpcommerce-exporter-date wpcm-enhanced-date" name="nd_date">
									</td>
								</tr>
							</tbody>
						</table>
					</section>
					<div class="wpcm-actions">
						<button type="submit" class="wpcommerce-exporter-button button button-primary">
							<span class="spinner is-active"></span>
							<?php esc_html_e('Generate CSV', 'webinane-commerce') ?>
						</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Create new order in custom post type "orders"
	 *
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function create( $payment_data ) {

		$userinf = webinane_set( $payment_data, 'user_info' );
		$order = new OrderModel();

		$first_name = array_get( $userinf, 'first_name' );
		$last_name = array_get( $userinf, 'last_name' );

		$data = array(
			'post_title'        => webinane_set( $userinf, 'first_name' ) . ' ' . webinane_set( $userinf, 'last_name' ),
			'post_status'       => 'pending_payment',
			'post_type'         => 'orders',
		);
		$order->post_title = $first_name . ' ' . $last_name;
		$order->post_status = 'pending_payment';
		$order->post_type = 'orders';

		if ( is_user_logged_in() ) {
			$order->post_author = get_current_user_id();
		}

		$order = apply_filters( 'wpcm_new_order_data', $order, $payment_data );

		$order->save();
		$id = $order->ID;
		if ( $order ) {

			self::set_order_meta( $order, $payment_data );
			self::set_order_item( $order, $payment_data );
		}

		do_action( 'wpcm_new_order_process', $data, $payment_data, $id );

		return $id;
	}

	/**
	 * Set order meta.
	 *
	 * @param OrderModel $order   [description]
	 * @param [type]     $data [description]
	 */
	protected static function set_order_meta( $order, $data ) {


		$customer = new Customers( $data['user_email'] );
		$customer_id = $customer->customer->id;
		$purchase_key = array_get( $data, 'purchase_key' );
		$total = (float) array_get( $data, 'subtotal' );
		$gateway = array_get( $data, 'gateway' );
		$recurring = array_get( $data, 'recurring' );

		if ( $customer_id ) {
			$order->update_meta( '_wpcm_order_customer_id', $customer_id );
		}
		if ( $purchase_key ) {
			$order->update_meta( '_wpcm_order_purchase_key', $purchase_key );
		}
		if ( $total ) {
			$total = apply_filters( 'webinane_commerce/order_total', $total, $data );
			$order->update_meta( '_wpcm_order_total', $total );
			$order->update_meta( '_order_total', $total );
		}
		if ( $gateway ) {
			$order->update_meta( '_wpcm_order_gateway', $gateway );
			$order->update_meta( '_wpcm_order_customer_ip', $_SERVER['REMOTE_ADDR'] );
		}

		$order->update_meta( '_wpcm_is_recurring', $recurring );
		if($recurring) {
			$cycle = array_get($data, 'post_data.billing_period');
			$frequency = array_get($data, 'post_data.extras.gifts_number');

			$order->update_meta( '_wpcm_recurring_cycle', $cycle );
			$order->update_meta( '_wpcm_recurring_frequency', $frequency );
		}
		$order->update_meta( '_wpcm_order_submitted_data', $data );

		do_action( 'wpcm_new_order_process_meta', $data, $order );
	}

	/**
	 * Set order item and its meta.
	 *
	 * @param [type] $id   [description]
	 * @param [type] $data [description]
	 */
	protected static function set_order_item( $order, $data ) {
		global $wpdb;

		$items = webinane_set( $data, 'items' );
		$currency = webinane_set($data, 'currency');

		foreach ( $items as $item ) {
			$item_id = webinane_set( $item, 'item_id' );
			$base_price = webinane_set( $item, 'price' );
			$price = apply_filters( 'webinane_commerce/order_total', $base_price, $data );

			$item_data = new OrderItems(
				array(
					'order_item_name'       => get_the_title( $item_id ),
					'order_item_type'       => apply_filters( 'wpcommerce_order_item_db_type', 'simple' ),
					'post_id'               => $item_id,
					'price' => (float) $price,
					'order_id'  => $order->ID,
					'qty' => webinane_set( $item, 'quantity' ),
					'currency'	=> $currency,
					'base_price'	=> $base_price
				)
			);
			$order->items()->save( $item_data );
		}

		do_action( 'wpcm_new_order_after_new_order_items', $data, $order->ID );
	}

	/**
	 * [order_detail description]
	 *
	 * @param  [type] $order [description]
	 * @return [type]        [description]
	 */
	public static function order_detail( $order ) {
		_deprecated_function( __FUNCTION__, '1.0.7.3', esc_html__( 'No replacement', 'webinane-commerce' ) );
		$order = self::order_data( $order );

		webinane_template( 'orders/order-detail.php' );
	}

	/**
	 * get complete order data with items and meta.
	 *
	 * @param  [type] $order [description]
	 * @return [type]        [description]
	 */
	public static function order_data( $order ) {
		global $wpdb;

		if ( $order instanceof \WP_Post ) {
			$order = wpcm_order_model( $order );
		}

		if ( $order->items ) {

			$order->items->each(
				function( $order_item ) {
					$order_item->setAttribute( 'link', get_permalink( $order_item->post_id ) );
					$order_item->setAttribute( 'thumb', wp_get_attachment_image_url( get_post_thumbnail_id( $order_item->post_id ), 'thumbnail' ) );
					return $order_item;
				}
			);
		}
		$order->order_items = $order->items;
		$gateway = array_get($order->meta_data, '_wpcm_order_gateway');
		$meta_data = [
			[
				'label' => esc_html__('Gateway', 'webinane-commerce'),
				'value'	=> ucwords($gateway)
			],
			[
				'label' => esc_html__('Total', 'webinane-commerce'),
				'value'	=> webinane_cm_price_with_symbol(array_get($order->meta_data, '_wpcm_order_total'))
			]
		];


		$meta_data = apply_filters( "webinane_commerce/admin/{$gateway}/meta", $meta_data, $order );

		$order->unsetRelation( 'meta' );
		$order->setAttribute( 'meta', $meta_data );

		return $order;
	}

	/**
	 * get order payment meta.
	 *
	 * @param  [type] $order [description]
	 * @return [type]        [description]
	 */
	public static function order_payment_info( $order ) {
		global $wpdb;

		if ( $order instanceof \WP_Post ) {
			$order = wpcm_order_model( $order );
		}

		if ( $order->items ) {

			$order->items->each(
				function( $order_item ) {
					$order_item->setAttribute( 'link', get_permalink( $order_item->post_id ) );
					$order_item->setAttribute( 'thumb', wp_get_attachment_image_url( get_post_thumbnail_id( $order_item->post_id ), 'thumbnail' ) );
					return $order_item;
				}
			);
		}

		$order->order_items = $order->items;
		$gateway = $order->meta_data->_wpcm_order_gateway;
		$meta_data = [
			'_wpcm_order_gateway' => ucwords($gateway),
			'_wpcm_order_total' => $order->meta_data->_wpcm_order_total
		];


		$meta_data = apply_filters( "webinane_commerce/admin/{$gateway}_payment_info/meta", $meta_data, $order );

		$order->unsetRelation( 'meta' );
		$order->setAttribute( 'meta', $meta_data );

		return $order;
	}

	/**
	 * [get_customer_detail description]
	 *
	 * @param  [type]  $order       [description]
	 * @param  integer $customer_id [description]
	 * @return [type]               [description]
	 */
	public static function get_customer_detail( $order, $customer_id = 0 ) {

		$order = wpcm_order_model( $order );
		return $order->customer;
	}

	/**
	 * [display_post_statuses description]
	 *
	 * @param  [type] $statuses [description]
	 * @return [type]           [description]
	 *
	 * @todo Need to remove .. recheck before removing
	 */
	public static function display_post_statuses( $statuses ) {
		global $post;

		$status = array(
			'pending_payment'           => esc_html__( 'Pending Payment', 'webinane-commerce' ),
			'processing'                => esc_html__( 'Processing', 'webinane-commerce' ),
			'hold'                      => esc_html__( 'On Hold', 'webinane-commerce' ),
			'completed'                 => esc_html__( 'Completed', 'webinane-commerce' ),
			'cancelled'                 => esc_html__( 'Cancelled', 'webinane-commerce' ),
			'refunded'                  => esc_html__( 'Refunded', 'webinane-commerce' ),
			'failed'                    => esc_html__( 'Failed', 'webinane-commerce' ),
		);

		if ( $post ) {

			if ( $post->post_type == 'orders' ) {
				if ( ! in_array( get_query_var( 'post_status' ), array_values( $status ) ) ) { // not for pages with all posts of this status
					if ( isset( $status[ $post->post_status ] ) ) {
						return array( $status[ $post->post_status ] );
					}
				}
			}
		}
		return $statuses;
	}

	/**
	 * [custom_post_status description]
	 *
	 * @return [type] [description]
	 * @todo Need to remove .. re-check if it is not being used.
	 */
	public static function custom_post_status() {
		$common = array(
			'public'                    => true,
			'post_type'                 => array( 'orders' ), // Define one or more post types the status can be applied to.
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'show_in_metabox_dropdown'  => true,
			'show_in_inline_dropdown'   => true,
			'dashicon'                  => 'dashicons-businessman',
		);

		register_post_status(
			'processing',
			array_merge(
				array(
					'label'                     => _x( 'Processing ', 'post status label', 'webinane-commerce' ),
					'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'webinane-commerce' ),

				),
				$common
			)
		);
		register_post_status(
			'pending_payment',
			array_merge(
				array(
					'label'                     => _x( 'Pending Payment ', 'post status label', 'webinane-commerce' ),
					'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'webinane-commerce' ),

				),
				$common
			)
		);

		register_post_status(
			'hold',
			array_merge(
				array(
					'label'                     => _x( 'On Hold ', 'post status label', 'webinane-commerce' ),
					'label_count'               => _n_noop( 'On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'webinane-commerce' ),

				),
				$common
			)
		);

		register_post_status(
			'completed',
			array_merge(
				array(
					'label'                     => _x( 'Completed ', 'post status label', 'webinane-commerce' ),
					'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'webinane-commerce' ),

				),
				$common
			)
		);

		register_post_status(
			'cancelled',
			array_merge(
				array(
					'label'                     => _x( 'Cancelled ', 'post status label', 'webinane-commerce' ),
					'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'webinane-commerce' ),

				),
				$common
			)
		);
		register_post_status(
			'refunded',
			array_merge(
				array(
					'label'                     => _x( 'Refunded ', 'post status label', 'webinane-commerce' ),
					'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'webinane-commerce' ),

				),
				$common
			)
		);
		register_post_status(
			'failed',
			array_merge(
				array(
					'label'                     => _x( 'Failed ', 'post status label', 'webinane-commerce' ),
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'webinane-commerce' ),

				),
				$common
			)
		);
	}

	public static function remove_quick_edit( $actions ) {
		global $post_type;
		if ( $post_type === 'orders' ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['trash'] );
		}
		return $actions;
	}

	/**
	 * Update customer data and mata data for billing and shipping
	 *
	 * @return [type] [description]
	 */
	public static function save_admin_orders_field() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array( 'message' => esc_html__( 'You are not authorized', 'webinane-commerce' ) ), 403 );
		}
		$_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		$customer_table = $wpdb->prefix . 'wpcommerce_customers';
		$customer_meta_table = $wpdb->prefix . 'wpcommerce_customer_meta';

		$data = webinane_set( $_post, 'customer' );
		$id = webinane_set( $data, 'id' );
		if ( ! $id ) {
			$id = esc_attr( webinane_set( $_POST, 'customer_id' ) );
		}
		$email = sanitize_email( webinane_set( $data, 'email' ) );
		if ( ! $id ) {
			$customer = new Customers( $email );
		} else {
			$customer = new Customers( $id );
		}

		$meta = webinane_set( $data, 'meta' );
		if ( $meta ) {
			unset( $data['meta'] );
		}

		$wpdb->update( $customer_table, $data, array( 'id' => $data['id'] ) );

		foreach ( array( 'billing', 'shipping' ) as $address_key ) {
			foreach ( $customer->meta as $key ) {
				if ( $value = webinane_set( $meta, $address_key . '_' . $key ) ) {
					$customer->update_meta( $customer->customer, $address_key . '_' . $key, $value );
				}
			}
		}

		wp_send_json(
			array(
				'message' => esc_html__( 'Successfully updated', 'webinane-commerce' ),
				'cust' => $data,
			)
		);
	}

	/**
	 * [order_admin_data description]
	 *
	 * @return [type] [description]
	 */
	public static function order_admin_data() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array( 'message' => esc_html__( 'You are not authorized', 'webinane-commerce' ) ), 403 );
		}
		$post_id = isset( $_POST['post_id'] ) ? esc_attr( $_POST['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json( array( 'message' => esc_html__( 'Invalid Post id', 'webinane-commerce' ) ), 403 );
		}
		if ( ! OrderModel::find( $post_id ) ) {
			wp_send_json( array( 'message' => esc_html__( 'No order found for the given ID', 'webinane-commerce' ) ), 403 );
		}

		$post = OrderModel::find( $post_id );
		$order = self::order_data( $post );
		$customer = self::get_customer_detail( $post );
		$all = Customers::all();
		$gateways = wpcm_get_active_gateways();

		wp_send_json(
			array(
				'order' => $order,
				'customer' => $customer,
				'customers' => $all,
				'gateways' => $gateways,
				'symbol' => webinane_currency_symbol(),
			)
		);
	}

	/**
	 * [update_order_general description]
	 *
	 * @return [type] [description]
	 */
	public static function update_order_general() {
		// exit('sdfsd');
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array( 'message' => esc_html__( 'You are not authorized', 'webinane-commerce' ) ), 403 );
		}
		$_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		$order = webinane_set( $_post, 'order' );
		$old_customer_id = get_post_meta($order['ID'], '_wpcm_order_customer_id', true);
		$customer_id = webinane_set( $_post, 'customer' );
		$order_action = esc_attr( webinane_set( $_post, 'order_action' ) );

		if ( ! $order ) {
			wp_send_json_error( esc_html__( 'Invalid order data provided', 'webinane-commerce' ) );
		}

		if ( ! $order['post_title'] || $order['post_title'] == 'Auto Draft' || $customer_id !== $old_customer_id ) {
			if ( $customer_id ) {
				$customer = new Customers( $customer_id );
				if ( $customer ) {
					$full_info = $customer->full_customer_info();

					$order['post_title'] = $full_info->email;
				}
			}
		}
		$result = wp_update_post( $order, true );

		if ( ! is_wp_error( $result ) ) {
			update_post_meta( $result, '_wpcm_order_customer_id', $customer_id );

			// Hookup to send email notifications for new order or just an order invoice.
			do_action( "wpcommerce_order_action_{$order_action}", wpcm_order($order), $customer_id );

			wp_send_json( array( 'message' => esc_html__( 'Order updated successfully', 'webinane-commerce' ) ) );
		}

		wp_send_json_error( array( 'message' => esc_html__( 'Something went wrong', 'webinane-commerce' ) ) );
	}

	/**
	 * [update_order_items description]
	 *
	 * @return [type] [description]
	 */
	public static function update_order_items() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not authorized to do that', 'webinane-commerce' ) );
		}
		$_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		$order = webinane_set( $_post, 'order' );
		$items = webinane_set( $order, 'order_items' );
		$total = 0;

		$db_items = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::$item_table . ' WHERE order_id = %d', $order['ID'] ) );
		foreach ( $db_items as $itm ) {
			$collection = collect( $items );

			$found = $collection->where( 'order_item_id', $itm->order_item_id );
			if ( ! $found->count() ) {
				$wpdb->delete( self::$item_table, array( 'order_item_id' => $itm->order_item_id ) );
			} else {
				$item = $found->first();
				$wpdb->update(
					self::$item_table,
					array(
						'qty' => $item['qty'],
					),
					array( 'order_item_id' => $item['order_item_id'] )
				);

				$total += $item['qty'] * $item['price'];
			}
		}
		update_post_meta( $order['ID'], '_wpcm_order_total', $total );

		wp_send_json_success( esc_html__( 'Item is updated successfully', 'webinane-commerce' ) );

	}

	/**
	 * Get the list of support post types.
	 *
	 * @return [type] [description]
	 */
	public static function get_products() {
		$post_type = apply_filters( 'wpcommerce_metabox_fields_supported_post_types', array( 'post' ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => -1,
			)
		);

		$products = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$products[ get_the_ID() ] = get_the_title();
			}
		}

		wp_reset_postdata();

		wp_send_json_success( $products );
	}

	/**
	 * [admin_add_new_item description]
	 *
	 * @return [type] [description]
	 */
	public static function admin_add_new_item() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not authorized to do that', 'webinane-commerce' ) );
		}

		$id = absint( webinane_set( $_POST, 'id' ) );

		$post = get_post( $id );

		if ( ! $post ) {
			wp_send_json_error( esc_html__( 'Product doesn\'t exist', 'webinane-commerce' ) );
		}

		$price = get_post_meta( $post->ID, '_price', true );

		if ( ! $price ) {
			$price = esc_attr( webinane_set( $_POST, 'amount', 0 ) );
		}
		if ( ! $price ) {
			wp_send_json_error( esc_html__( 'Product pricie is not available', 'webinane-commerce' ) );
		}

		$order = get_post( absint( webinane_set( $_POST, 'post_id' ) ) );

		if ( ! $order ) {
			wp_send_json_error( esc_html__( 'Product doesn\'t exist', 'webinane-commerce' ) );
		}

		$exists = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$item_table . ' WHERE order_id = %s AND post_id = %s', $order->ID, $post->ID ) );

		if ( ! $exists ) {
			$qty = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 1;

			$wpdb->insert(
				self::$item_table,
				array(
					'order_item_name'   => $post->post_title,
					'order_item_type'   => 'simple',
					'order_id'          => $order->ID,
					'post_id'           => $post->ID,
					'qty'               => $qty,
					'price'             => $price,
				)
			);

			$item_id = $wpdb->insert_id;

		} else {

			wp_send_json_error( esc_html__( 'Item is already in order, please update its quantity', 'webinane-commerce' ) );
		}

		$order_data = self::order_data( $order );
		wp_send_json_success(
			array(
				'message' => esc_html__( 'Item is updated successfully', 'webinane-commerce' ),
				'order' => $order_data,
			)
		);
	}

	/**
	 * [admin_add_note description]
	 *
	 * @return [type] [description]
	 */
	public static function admin_add_note() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not authorized to do that', 'webinane-commerce' ) );
		}
		$user = wp_get_current_user();

		$_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		$order = webinane_set( $_post, 'order' );
		$order_id = absint( webinane_set( $_post, 'post_id' ) );
		$note = sanitize_textarea_field( webinane_set( $_POST, 'note' ) );
		$status = esc_attr( webinane_set( $_POST, 'status' ) );

		$time = current_time( 'mysql' );

		$data = array(
			'comment_post_ID' => $order_id,
			'comment_author' => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_author_url' => '',
			'comment_content' => wp_kses_post( $note ),
			'comment_type' => '',
			'comment_parent' => 0,
			'user_id' => $user->ID,
			'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
			'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
			'comment_date' => $time,
			'comment_approved' => 1,
		);

		$comment_id = wp_new_comment( $data );
		if ( $comment_id ) {
			update_comment_meta( $comment_id, '_comment_status', $status );
		}
		$comments = get_comments( array( 'post_id' => $order_id ) );
		wp_send_json_success(
			array(
				'notes' => $comments,
				'message' => esc_html__(
					'Note added successfully',
					'webinane-commerce'
				),
			)
		);
	}

	/**
	 * [admin_get_notes description]
	 *
	 * @return [type] [description]
	 */
	public static function admin_get_notes() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not authorized to do that', 'webinane-commerce' ) );
		}
		$user = wp_get_current_user();

		$order_id = absint( webinane_set( $_POST, 'post_id' ) );

		$comments = get_comments( array( 'post_id' => $order_id ) );
		wp_send_json_success(
			array(
				'notes' => $comments,
				'message' => esc_html__(
					'Note added successfully',
					'webinane-commerce'
				),
			)
		);
	}

	/**
	 * [admin_remove_note description]
	 *
	 * @return [type] [description]
	 */
	public static function admin_remove_note() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not authorized to do that', 'webinane-commerce' ) );
		}
		$user = wp_get_current_user();

		$order_id = absint( webinane_set( $_POST, 'post_id' ) );
		$comment_id = absint( webinane_set( $_POST, 'id' ) );

		wp_delete_comment( $comment_id );

		$comments = get_comments( array( 'post_id' => $order_id ) );
		wp_send_json_success(
			array(
				'notes' => $comments,
				'message' => esc_html__(
					'Note deleted successfully',
					'webinane-commerce'
				),
			)
		);
	}

	/**
	 * [admin_delete_order description]
	 *
	 * @return void [description]
	 */
	public static function admin_delete_order() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not authorized to do that', 'webinane-commerce' ) );
		}

		$_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		$order_id = absint( webinane_set( $_post, 'post_id' ) );

		wp_delete_post( $order_id );

		wp_send_json_success( array( 'message' => esc_html__( 'Note deleted successfully', 'webinane-commerce' ) ) );
	}

	/**
	 * [cpt_columns description]
	 *
	 * @param  [type] $columns [description].
	 *
	 * @return [type]          [description]
	 */
	public static function cpt_columns( $columns ) {
		unset( $columns['date'] );
		$columns['title'] = esc_html__( 'Donor', 'webinane-commerce' );
		$columns['total'] = esc_html__( 'Total', 'webinane-commerce' );
		$columns['email'] = esc_html__( 'Email', 'webinane-commerce' );
		$columns['gateway'] = esc_html__( 'Gateway', 'webinane-commerce' );
		$columns['number_itmes'] = esc_html__( 'Number of Items', 'webinane-commerce' );
		$columns['date'] = esc_html__( 'Date', 'webinane-commerce' );
		return $columns;
	}

	/**
	 * [custom_columns description]
	 *
	 * @param  [type] $column [description].
	 *
	 * @return [type]         [description]
	 */
	public static function custom_columns( $column ) {
		global $post, $wpdb;

		if ( 'orders' != $post->post_type ) {
			return;
		}

		switch ( $column ) {
			case 'email':
				$customer_id = get_post_meta( $post->ID, '_wpcm_order_customer_id', true );
				$exists = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'wpcommerce_customers WHERE id = %s', $customer_id ) );
				if ( $exists ) {
					// translators: it replaces the email address.
					printf( '<a href="mailto:%s">%s</a>', esc_attr( $exists->email ), esc_attr( $exists->email ) );
				}
				break;
			case 'total':
				// $meta = get_post_meta($post->ID, '_wpcm_order_total', true);
				$order = self::order_data( $post );
				$total = 0;
				if ( $order ) {
					foreach ( $order->order_items as $item ) {
						$total += ( $item->qty ) * ( $item->price );
					}

					echo wp_kses_post( webinane_cm_price_with_symbol( $total ) );
				} else {
					echo '0';
				}
				// code...
				break;
			case 'number_itmes':
				$order = self::order_data( $post );

				if ( $order ) {
					echo count( $order->order_items );
				}
				break;
			case 'gateway':
				$gateway = get_post_meta( $post->ID, '_wpcm_order_gateway', true );
				$gateways = wpcm_get_active_gateways();
				if ( isset( $gateways[ $gateway ] ) ) {
					echo esc_attr( $gateways[ $gateway ]->name );
				}
				break;

		}
	}

	/**
	 * Ajax response for customer specific data.
	 *
	 * @return void [description]
	 */
	public static function customer_data() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not authorized to do that', 'webinane-commerce' ) );
		}

		$_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		$subaction = esc_attr( webinane_set( $_post, 'subaction' ) );
		$customer_id = absint( webinane_set( $_post, 'customer_id' ) );
		$post_id = esc_attr( array_get( $_post, 'post_id' ) );

		switch ( $subaction ) {
			case 'load_billing':
			case 'load_shipping':
				// code...
				$customer = self::get_customer_detail( get_post( $post_id ), $customer_id );
				wp_send_json( array( 'customer' => $customer ) );
				break;
			case 'copy_billing':
				$customer = new Customers( $customer_id );
				if ( $customer->userdata ) {
					$customer->copyBilling( $customer->userdata );
					$customer = self::get_customer_detail( get_post( $post_id ), $customer_id );
					wp_send_json( array( 'customer' => $customer ) );
				}
				break;
			default:
				// code...
				break;
		}

	}

	/**
	 * [this_month_total description]
	 *
	 * @return [type] [description]
	 */
	public static function orders_status_this_month() {
		global $post;

		$orders = new \WP_Query(
			array(
				'post_type' => 'orders',
				'post_status' => 'completed',
			)
		);
		$total = 0;
		if ( $orders->have_posts() ) {
			while ( $orders->have_posts() ) {
				$orders->the_post();

				$total += self::get_order_total( $post );
			}
		}
		wp_reset_postdata();

		$pending = new \WP_Query(
			array(
				'post_type' => 'orders',
				'post_status' => 'pending_payment',
			)
		);
		wp_reset_postdata();

		$hold = new \WP_Query(
			array(
				'post_type' => 'orders',
				'post_status' => 'hold',
			)
		);
		wp_reset_postdata();

		return array(
			'total' => '$' . $total,
			'pending' => $pending->found_posts,
			'hold' => $hold->found_posts,
		);
	}

	/**
	 * [get_order_total description]
	 *
	 * @param  [type] $order [description].
	 *
	 * @return [type]        [description]
	 */
	public static function get_order_total( $order ) {
		global $wpdb;

		$items = $wpdb->get_results( $wpdb->prepare( 'SELECT order_item_id, SUM(qty * price) AS total FROM ' . $wpdb->prefix . 'wpcommerce_order_items WHERE order_id = %d', $order->ID ) );
		$total = 0;
		if ( $items ) {
			foreach ( $items as $item ) {
				$total += $item->total;
			}
		}

		return $total;
	}

	/**
	 * [get_order_total description]
	 *
	 * @param  [type] $item [description].
	 *
	 * @return [type]        [description]
	 */
	public static function get_items_total( $item ) {
		global $wpdb;

		$items = OrderItems::whereHas('order', function($query) {
			$query->where('post_status', 'completed');
		})->where('post_id', $item->ID)->get();

		$total = $items->sum('total');

		return $total;
	}

	/**
	 * Process the refund.
	 *
	 * @return void [description]
	 */
	public static function give_refund() {
		$_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You have no permissions to do that', 'webinane-commerce' ) ) );
		}
		$order = webinane_set( $_post, 'order' );
		$gateway_id = get_post_meta( webinane_set( $order, 'ID' ), '_wpcm_order_gateway', true );

		$gateways = wpcm_get_active_gateways();
		$gateway = webinane_set( $gateways, $gateway_id );

		if ( is_object( $gateway ) ) {
			if ( $gateway->is_active() ) {
				do_action( "webinane_commerce_process_refund_{$gateway_id}", $order );
				wp_send_json_success( array( 'message' => esc_html__( 'Refund is processed successfully', 'webinane-commerce' ) ) );
			} else {
				wp_send_json_error( array( 'message' => esc_html__( 'Gateway is not enabled', 'webinane-commerce' ) ) );
			}
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid gateway', 'webinane-commerce' ) ) );
		}
	}
}


add_action( 'init', array( Orders::class, 'custom_post_status' ) );
add_filter( 'display_post_states', array( Orders::class, 'display_post_statuses' ) );

add_filter( 'post_row_actions', array( Orders::class, 'remove_quick_edit' ), 10, 1 );

add_filter( 'bulk_actions-edit-orders', '__return_empty_array', 100 );

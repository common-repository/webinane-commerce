<?php
namespace WebinaneCommerce\Helpers;

use WP_Error;

class Api
{
    private static $token;
    private static $token_exp;
    private static $refresh_token;
    private static $storage;
    private static $api_server='https://www.webinane.com';
    public static $instance;
    private static $secret = '';
    private const OPT_KEY = 'webinane_commerce_user_connect';
    private const TRANSIENT = 'webinane_commerce_user_connect';
    private static $req_body = [];

    /**
     * Magic function to handle static or dynamic methods.
     *
     * @param  string $method     The method
     * @param  array $parameters  Array of parameters.
     * @return callable           Returns nothing.
     */
    public function __call($method, $parameters = null) {
        if($method == 'exists') {
            return call_user_func_array(array($this, 'exists'), array(array($this->file)));
        }
    }

    public static function __callStatic($method, $parameters) {
        if($method == 'exists') {
            return call_user_func(__CLASS__.'::exists', $parameters);
        }
    }

    /**
     * Class Instance.
     * @return [type] [description]
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize the API.
     * @return [type] [description]
     */
    public static function init() {
        $info = get_option(self::OPT_KEY);
        self::set_token(array_get($info, 'access_token'));
        self::set_token_exp(array_get($info, 'expires_at'));
        self::set_refresh_token(array_get($info, 'refresh_token'));
        self::set_creds();
    }

    /**
     * [set_secret description]
     */
    private static function set_creds() {
        self::$req_body = [
            'client_id' => '2',
            'client_secret' => self::$secret,
        ];
    }

    /**
     * get storage.
     *
     * @return [type] [description]
     */
    public static function get_storage()
    {

        self::$storage = get_option(self::OPT_KEY);

        return self::$storage;

    }

    /**
     * remove storage.
     *
     * @return [type] [description]
     */
    public static function remove_storage()
    {
        if(!get_option(self::OPT_KEY)){
            return;
        }

        return delete_option(self::OPT_KEY);

    }

    /**
     * set access token.
     *
     * @return [type] [description]
     */
    public static function set_token($token)
    {
        if ($token) {
            self::$token = $token;
        }

        return self::instance();
    }

    /**
     * get access token.
     *
     * @return [type] [description]
     */
    public static function get_token()
    {
        if(!self::$token){
            return self::$token = array_get(self::get_storage(), 'access_token');
        }
        return self::$token;
    }

    /**
     * set token expiration.
     *
     * @return [type] [description]
     */
    public static function set_token_exp($exp)
    {
        if ($exp) {
            self::$token_exp = $exp;
        }

        return self::instance();
    }

     /**
     * get token expiration.
     *
     * @return [type] [description]
     */
    public static function get_token_exp()
    {
        return self::$token_exp;
    }

    /**
     * set refresh token.
     *
     * @return [type] [description]
     */
    public static function set_refresh_token($refresh)
    {
        if ($refresh) {
            self::$refresh_token = $refresh;
        }

        return self::instance();
    }

    /**
     * get refresh token.
     *
     * @return [type] [description]
     */
    public static function get_refresh_token()
    {
        return self::$refresh_token;
    }

    /*public function update() {
        return;
        $data = [
            'token'     => $this->get_token(),
            'expiration'    => $this->get_token_exp(),
            'refresh' =>    $this->get_refresh_token()
        ];

        $storage = $this->get_storage();
        $storage['authorization'] = $data;

        update_option('wpcm_user_data', $storage);
    }*/

     /**
     * get refresh token.
     *
     * @return [type] [description]
     */
    public static function get_redirect_url()
    {
        return self::$api_server . '/redirect?redirect_uri='.urlencode(admin_url('/admin.php?page=wp-commerce-settings'));
    }

    public static function get_auth_url() {
        return self::$api_server . '/api/auth/login';
    }

    /**
     * Get the status if connected or not.
     *
     * @return boolean [description]
     */
    public static function is_connected() {
        $options = get_option('webinane_commerce_user_connect');

        if($options && array_get($options, 'access_token')) {
            if ( ! self::is_expired($options) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether token is expired.
     *
     * @param  [type]  $info [description]
     * @return boolean       [description]
     */
    private static function is_expired($info) {

        if ( isset( $info['expires_in'] ) ) {
            return $info['expires_in'] > time() ? false : true;
        }

        return true;
    }

    /**
     * Get the status if disconnected or not.
     *
     * @return boolean [description]
     */
    public static function is_disconnected() {
        if(!Api::is_connected()){
            return;
        }
        $opt_disconnect = self::remove_storage();
        if(!$opt_disconnect){
            wp_send_json_error( ['message' => esc_html__('settings to disconnection is get wrong.', 'webinane-commerce')], 403 );
        }
        return delete_transient( self::TRANSIENT );
    }

    /**
     * Check if token is still valid.
     *
     * @return [type] [description]
     */
    private static function validate_token() {
        $res = self::request([
            'url'   => '/api/user',
            'method'    => 'get',
        ]);

        /**
         * @todo Need to work on the response.
         */
    }

    /**
     * Get a new token using refresh token.
     *
     * @return [type] [description]
     */
    private static function get_new_token() {
        /**
         * @todo Need to get a token if a token is expired or revoked.
         */
    }

    /**
     * Connect using a password.
     *
     * @param  [type] $form [description]
     * @return [type]       [description]
     */
    public static function request_body($form) {
        return [
            'username' => sanitize_text_field( array_get($form, 'email') ),
            'password' => sanitize_text_field( array_get($form, 'password') ),
        ];
    }

    /**
     * parse the response and save.
     *
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    public static function parse_response($response) {
        $status = wp_remote_retrieve_response_code( $response );

        if($status == 200) {

            $body = json_decode(wp_remote_retrieve_body( $response ), true);
            $body['expires_in'] = time() + $body['expires_in'];
            update_option(self::OPT_KEY, $body);
            set_transient( self::TRANSIENT, $body, DAY_IN_SECONDS * 100 );

            return ['message' => esc_html__('Connection is successful', 'webinane-commerce')];

        } else {
            $body = wp_remote_retrieve_body( $response );
            if(json_decode($body)) {
                // $body = json_decode($body);
                return new WP_Error('failed', esc_html__( 'Wrong username or password, please try again with correct credentials', 'webinane-commerce' ));
            } else {
                return new WP_Error('failed', $body);
            }

        }
    }

    private static function get_headers() {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . self::get_token(),
            'referer'   => home_url('/')
        ];
    }
    /**
     * Make a request.
     *
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    public static function request($config) {
        $url = self::$api_server . '/api' . $config['path'];

        if($config['type'] === 'post') {
            $res = wp_remote_post( $url, [
                'headers'   => self::get_headers(),
                'body'      => $config['body']
            ] );
        } else {
            $res = wp_remote_get( $url, [
                'headers'   => self::get_headers()
            ] );
        }

        return $res;
    }
}

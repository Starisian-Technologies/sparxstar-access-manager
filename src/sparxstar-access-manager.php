<?php
/**
Plugin Name: SPARXSTAR Access Manager
@Description: Complete access control enforcement (Query, REST, AJAX, and Template).
@Version: 1.4
@Author: Starisian Technologies (Max Barrett) <support@starisian.com>
*/
namespace Starisian\Sparxstar\access;

if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
 * PART 1: FIELD REGISTRATION (Unchanged)
 * -------------------------------------------------------------------------- */
add_action( 'acf/init', function() {
    if ( function_exists( 'acf_add_options_page' ) ) {
        acf_add_options_page([
            'page_title' => 'Site Access',
            'menu_slug'  => 'sparx-site-access',
            'menu_title' => 'Frontend Restrictions',
            'menu_icon'  => 'dashicons-shield',
            'redirect'   => false,
            'autoload'   => true,
            'capability' => 'manage_options',
        ]);
    }
});

add_action( 'acf/include_fields', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    // Site Access Options
    acf_add_local_field_group([
        'key' => 'group_sparx_site_access',
        'title' => 'Site Access',
        'location' => [[['param'=>'options_page','operator'=>'==','value'=>'sparx-site-access']]],
        'fields' => [
            [
                'key' => 'field_sparx_frontend_enabled',
                'label' => 'Frontend Post Type Restrictions Enabled',
                'name' => 'sparx_frontend_restrictions_enabled',
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 1,
            ],
            [
                'key' => 'field_sparx_default_redirect',
                'label' => 'Default Redirection Target URL',
                'name' => 'sparx_default_redirection_target_url',
                'type' => 'url',
                'required' => 1,
                'default_value' => home_url(),
                'conditional_logic' => [[['field'=>'field_sparx_frontend_enabled','operator'=>'==','value'=>'1']]]
            ],
            [
                'key' => 'field_sparx_admin_restricted',
                'label' => 'Restrict WP-Admin',
                'name' => 'sparx_restrict_wp_admin',
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 1,
            ],
            [
                'key' => 'field_sparx_admin_redirect',
                'label' => 'Redirect URL After Login',
                'name' => 'sparx_redirect_url_after_login',
                'type' => 'url',
                'default_value' => home_url('/star-dashboard/'),
                'conditional_logic' => [[['field'=>'field_sparx_admin_restricted','operator'=>'==','value'=>'1']]]
            ],
            [
                'key' => 'field_sparx_admin_roles',
                'label' => 'Permitted User Groups',
                'name' => 'sparx_permitted_user_groups',
                'type' => 'select',
                'multiple' => 1,
                'return_format' => 'value',
                'conditional_logic' => [[['field'=>'field_sparx_admin_restricted','operator'=>'==','value'=>'1']]]
            ],
            [
                'key' => 'field_sparx_admin_users',
                'label' => 'Permitted User Access',
                'name' => 'sparx_permitted_user_access',
                'type' => 'user',
                'multiple' => 1,
                'return_format' => 'array',
                'conditional_logic' => [[['field'=>'field_sparx_admin_restricted','operator'=>'==','value'=>'1']]]
            ],
        ],
    ]);

    // Post/Page Restrictions
    acf_add_local_field_group([
        'key' => 'group_sparx_frontend',
        'title' => 'Frontend Restrictions',
        'location' => [
            [['param'=>'post_type','operator'=>'==','value'=>'post']],
            [['param'=>'post_type','operator'=>'==','value'=>'page']],
        ],
        'position' => 'side',
        'fields' => [
            [
                'key' => 'field_sparx_restrict',
                'label' => 'Restrict Frontend Access',
                'name' => 'sparx_frontend_restriction',
                'type' => 'true_false',
                'default_value' => 0,
            ],
            [
                'key' => 'field_sparx_state',
                'label' => 'Allow If',
                'name' => 'sparx_frontend_allow_if',
                'type' => 'select',
                'choices' => ['logged_in'=>'Logged In','logged_out'=>'Logged Out','all'=>'All'],
                'default_value' => 'logged_in',
                'conditional_logic' => [[['field'=>'field_sparx_restrict','operator'=>'==','value'=>'1']]]
            ],
            [
                'key' => 'field_sparx_roles',
                'label' => 'Allowed Roles',
                'name' => 'sparx_frontend_allowed_roles',
                'type' => 'select',
                'multiple' => 1,
                'ui' => 1,
                'conditional_logic' => [
                    [['field'=>'field_sparx_restrict','operator'=>'==','value'=>'1'],['field'=>'field_sparx_state','operator'=>'==','value'=>'logged_in']],
                    [['field'=>'field_sparx_restrict','operator'=>'==','value'=>'1'],['field'=>'field_sparx_state','operator'=>'==','value'=>'logged_out']]
                ]
            ],
            [
                'key' => 'field_sparx_users',
                'label' => 'Allowed Users',
                'name' => 'frontend_allowed_users',
                'type' => 'user',
                'multiple' => 1,
                'return_format' => 'array',
                'conditional_logic' => [
                    [['field'=>'field_sparx_restrict','operator'=>'==','value'=>'1'],['field'=>'field_sparx_state','operator'=>'==','value'=>'logged_in']]
                ]
            ],
            [
                'key' => 'field_sparx_redirect',
                'label' => 'Redirect URL',
                'name' => 'sparx_frontend_redirect_url',
                'type' => 'url',
                'instructions' => 'Leave empty to use Default.',
                'conditional_logic' => [[['field'=>'field_sparx_restrict','operator'=>'==','value'=>'1']]]
            ],
        ],
    ]);
});

/* --------------------------------------------------------------------------
 * PART 2: SECURITY ENFORCEMENT
 * -------------------------------------------------------------------------- */

class Sparx_Frontend_Access {

    public function __construct() {
        // UI Helpers
        add_filter('acf/load_field/name=sparx_frontend_allowed_roles', [$this,'load_roles']);
        add_filter('acf/load_field/name=sparx_permitted_user_groups', [$this,'load_roles']);

        // 1. Universal AJAX Guard
        add_action('init', [$this, 'secure_ajax'], 1);

        // 2. Query & List Filtering (Metadata Leak Protection)
        add_action('pre_get_posts', [$this, 'filter_main_query']);
        add_filter('rest_post_query', [$this, 'filter_rest_query'], 10, 2);
        add_filter('rest_page_query', [$this, 'filter_rest_query'], 10, 2);

        // 3. Single View Enforcement
        add_action('template_redirect', [$this, 'enforce_single_view'], 1);
        add_filter('rest_prepare_post', [$this, 'secure_single_rest'], 10, 3);
        add_filter('rest_prepare_page', [$this, 'secure_single_rest'], 10, 3);

        // 4. Output Scrubbing (Fallback)
        add_filter('the_content', [$this, 'secure_content_output']);
        add_filter('the_excerpt', [$this, 'secure_content_output']);

        // 5. Admin Security
        add_action('admin_init', [$this, 'enforce_admin']);
        add_filter('login_redirect', [$this, 'login_redirect'], 10, 3);
    }

    /* ----------------------------------------------------------------
     * 1. UNIVERSAL AJAX GUARD
     * ---------------------------------------------------------------- */
    public function secure_ajax() {
        if ( ! defined('DOING_AJAX') || ! DOING_AJAX ) return;
        if ( current_user_can('administrator') ) return;

        // Catch standard post_id parameters often used in custom AJAX handlers
        $post_id = 0;
        if ( isset($_REQUEST['post_id']) ) $post_id = (int) $_REQUEST['post_id'];
        elseif ( isset($_REQUEST['id']) ) $post_id = (int) $_REQUEST['id'];
        elseif ( isset($_REQUEST['pid']) ) $post_id = (int) $_REQUEST['pid'];

        if ( ! $post_id ) return;

        $post = get_post($post_id);
        if ( ! $post ) return;

        if ( ! $this->is_allowed($post) ) {
            wp_send_json_error(['message' => 'Access denied'], 403);
        }
    }

    /* ----------------------------------------------------------------
     * 2. QUERY & LIST FILTERING (Stop Meta Leaks)
     * ---------------------------------------------------------------- */
    public function filter_main_query( $query ) {
        if ( is_admin() || current_user_can('administrator') ) return;

        // Apply to Main Queries, Search, Feeds, and Archives
        if ( ! $query->is_main_query() && ! $query->is_search() && ! $query->is_feed() && ! $query->is_archive() ) return;

        $this->apply_exclusion_query( $query );
    }

    public function filter_rest_query( $args, $request ) {
        if ( current_user_can('administrator') ) return $args;

        // We can't modify the query object directly here, we modify the args
        // Since $args is an array, we manually append meta_query
        $meta_query = isset($args['meta_query']) ? $args['meta_query'] : [];
        
        // Exclude restricted posts
        $meta_query[] = [
            'key'     => 'sparx_frontend_restriction',
            'value'   => '1',
            'compare' => '!='
        ];

        $args['meta_query'] = $meta_query;
        return $args;
    }

    private function apply_exclusion_query( $query ) {
        $meta_query = $query->get('meta_query') ?: [];
        
        // Hide posts where restriction == 1
        // Note: We use != 1 to allow posts where field is 0 OR field doesn't exist
        $meta_query[] = [
            'key'     => 'sparx_frontend_restriction',
            'value'   => '1',
            'compare' => '!=' 
        ];

        $query->set('meta_query', $meta_query);
    }

    /* ----------------------------------------------------------------
     * 3. SINGLE VIEW ENFORCEMENT
     * ---------------------------------------------------------------- */
    public function enforce_single_view() {
        if ( is_admin() ) return;
        
        global $post;
        if ( ! $post ) return;

        if ( $this->is_allowed($post) ) return;

        $redirect = get_field('sparx_frontend_redirect_url', $post->ID)
                 ?: get_field('sparx_default_redirection_target_url', 'option')
                 ?: home_url();

        if ( $this->is_current_url($redirect) ) return;

        wp_redirect($redirect);
        exit;
    }

    public function secure_single_rest( $response, $post, $request ) {
        if ( ! $this->is_allowed($post) ) {
            return new WP_Error(
                'rest_forbidden', 
                __( 'You do not have permission to view this content.', 'sparx' ), 
                array( 'status' => 403 )
            );
        }
        return $response;
    }

    /* ----------------------------------------------------------------
     * 4. OUTPUT SCRUBBING
     * ---------------------------------------------------------------- */
    public function secure_content_output( $content ) {
        if ( is_admin() && ! ( defined('DOING_AJAX') && DOING_AJAX ) ) return $content;

        global $post;
        if ( ! $post ) return $content;

        if ( ! $this->is_allowed($post) ) {
            return apply_filters('sparx_restricted_placeholder', '');
        }

        return $content;
    }

    /* ----------------------------------------------------------------
     * CORE LOGIC & UTILS
     * ---------------------------------------------------------------- */
    private function is_allowed($post) {
        if ( ! function_exists('get_field') ) return true;
        
        // Global switch
        if ( ! get_field('sparx_frontend_restrictions_enabled', 'option') ) return true;
        
        // Post specific switch
        // Note: We check specifically for '1' because ACF unchecked might be null/0
        if ( get_field('sparx_frontend_restriction', $post->ID) != 1 ) return true;

        $user = wp_get_current_user();
        if ( user_can($user, 'administrator') ) return true;

        $state = get_field('sparx_frontend_allow_if', $post->ID);
        $roles = (array) get_field('sparx_frontend_allowed_roles', $post->ID);
        $users = (array) get_field('frontend_allowed_users', $post->ID);

        $logged_in = is_user_logged_in();

        if ( $state === 'logged_in' && ! $logged_in ) return false;
        if ( $state === 'logged_out' && $logged_in ) return false;

        if ( ! $logged_in ) {
            return empty($roles) && empty($users);
        }

        foreach ($users as $u) {
            $uid = is_array($u) ? $u['ID'] : $u;
            if ( $uid == $user->ID ) return true;
        }

        if ( in_array('author_only', $roles, true) && (int)$post->post_author === (int)$user->ID ) {
            return true;
        }

        $roles_clean = array_diff($roles, ['author_only']);
        if ( ! empty($roles_clean) ) {
            if ( array_intersect($roles_clean, $user->roles) ) return true;
            return false;
        }

        return empty($users) && empty($roles_clean);
    }

    public function enforce_admin() {
        if ( ! function_exists('get_field') ) return;
        if ( ! get_field('sparx_restrict_wp_admin', 'option') ) return;
        if ( defined('DOING_AJAX') && DOING_AJAX ) return;

        $user = wp_get_current_user();
        if ( user_can($user, 'administrator') ) return;

        $roles = (array) get_field('sparx_permitted_user_groups', 'option');
        $users = (array) get_field('sparx_permitted_user_access', 'option');

        foreach ($users as $u) {
            $uid = is_array($u) ? $u['ID'] : $u;
            if ( $uid == $user->ID ) return;
        }

        if ( ! empty($roles) && array_intersect($roles, $user->roles) ) return;

        wp_redirect(home_url());
        exit;
    }

    public function load_roles($field) {
        global $wp_roles;
        if ( ! isset($wp_roles) ) $wp_roles = wp_roles();
        $field['choices'] = [];
        foreach ($wp_roles->roles as $key => $role) {
            $field['choices'][$key] = translate_user_role($role['name']);
        }
        if ( $field['name'] === 'sparx_frontend_allowed_roles' ) {
            $field['choices']['author_only'] = 'Author Only';
        }
        return $field;
    }

    public function login_redirect($redirect_to, $request, $user) {
        if ( ! is_a($user, 'WP_User') ) return $redirect_to;
        if ( get_field('sparx_restrict_wp_admin', 'option') && ! user_can($user, 'administrator') ) {
            $dash = get_field('sparx_redirect_url_after_login', 'option');
            if ( $dash ) return $dash;
        }
        return $redirect_to;
    }

    private function is_current_url($redirect_url) {
        $current = (is_ssl() ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return rtrim(preg_replace('(^https?://)', '', $current), '/') === rtrim(preg_replace('(^https?://)', '', $redirect_url), '/');
    }
}

new Sparx_Frontend_Access();

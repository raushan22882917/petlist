<?php
/**
 * Dog Directory - Custom Roles & Capabilities
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Roles {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'init', [ $this, 'register_roles' ] );
        add_filter( 'user_has_cap', [ $this, 'dd_subscriber_caps' ], 10, 4 );
    }

    public function register_roles() {
        // Subscriber role (dog directory subscriber)
        if ( ! get_role( 'dd_subscriber' ) ) {
            add_role( 'dd_subscriber', __('Dog Directory Subscriber', 'petslist'), [
                'read'                  => true,
                'upload_files'          => true,
                'dd_manage_own_dogs'    => true,
                'dd_access_directory'   => true,
            ] );
        }

        // Admin gets all DD caps
        $admin = get_role('administrator');
        if ( $admin ) {
            $admin->add_cap('dd_manage_own_dogs');
            $admin->add_cap('dd_access_directory');
            $admin->add_cap('dd_manage_all_dogs');
            $admin->add_cap('dd_manage_subscriptions');
            $admin->add_cap('dd_view_reports');
        }

        // Ensure standard subscriber role has upload_files capability for photo uploads
        $subscriber = get_role('subscriber');
        if ( $subscriber && ! $subscriber->has_cap('upload_files') ) {
            $subscriber->add_cap('upload_files');
        }
    }

    /**
     * Dynamic cap check: subscribers can edit their own dogs and upload files
     */
    public function dd_subscriber_caps( $allcaps, $caps, $args, $user ) {
        // Any logged-in user can upload photos for their dogs and profile
        if ( ! empty( $user->ID ) ) {
            $allcaps['upload_files'] = true;
        }

        if ( in_array('edit_post', $caps) || in_array('delete_post', $caps) ) {
            $post_id = $args[2] ?? 0;
            if ( $post_id && get_post_type($post_id) === 'dd_dog' ) {
                $post = get_post($post_id);
                if ( $post && (int) $post->post_author === (int) $user->ID ) {
                    $allcaps['edit_post']   = true;
                    $allcaps['delete_post'] = true;
                }
            }
        }
        return $allcaps;
    }
}

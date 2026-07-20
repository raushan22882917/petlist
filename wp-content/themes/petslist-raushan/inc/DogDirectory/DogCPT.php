<?php
/**
 * Dog Directory - Custom Post Type & Taxonomies
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class DogCPT {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'init', [ $this, 'register_dog_post_type' ] );
        add_action( 'init', [ $this, 'register_dog_taxonomies' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_dog_meta_boxes' ] );
        add_action( 'save_post_dd_dog', [ $this, 'save_dog_meta' ] );
        add_filter( 'manage_dd_dog_posts_columns', [ $this, 'dog_admin_columns' ] );
        add_action( 'manage_dd_dog_posts_custom_column', [ $this, 'dog_admin_column_content' ], 10, 2 );
        add_filter( 'template_include', [ $this, 'dog_single_template' ] );
        add_filter( 'template_include', [ $this, 'dog_archive_template' ] );
    }

    public function register_dog_post_type() {
        $labels = [
            'name'               => __( 'Dogs', 'petslist' ),
            'singular_name'      => __( 'Dog', 'petslist' ),
            'add_new'            => __( 'Add Dog', 'petslist' ),
            'add_new_item'       => __( 'Add New Dog', 'petslist' ),
            'edit_item'          => __( 'Edit Dog', 'petslist' ),
            'new_item'           => __( 'New Dog', 'petslist' ),
            'view_item'          => __( 'View Dog', 'petslist' ),
            'search_items'       => __( 'Search Dogs', 'petslist' ),
            'not_found'          => __( 'No dogs found', 'petslist' ),
            'not_found_in_trash' => __( 'No dogs in trash', 'petslist' ),
            'menu_name'          => __( 'Dog Directory', 'petslist' ),
        ];

        register_post_type( 'dd_dog', [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => [ 'slug' => 'dog-directory', 'with_front' => false ],
            'capability_type'     => 'post',
            'has_archive'         => 'dog-directory',
            'hierarchical'        => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-pets',
            'supports'            => [ 'title', 'editor', 'thumbnail', 'author' ],
            'show_in_rest'        => true,
        ] );
    }

    public function register_dog_taxonomies() {
        // Breed taxonomy
        register_taxonomy( 'dd_breed', 'dd_dog', [
            'labels'            => [
                'name'          => __( 'Breeds', 'petslist' ),
                'singular_name' => __( 'Breed', 'petslist' ),
                'search_items'  => __( 'Search Breeds', 'petslist' ),
                'all_items'     => __( 'All Breeds', 'petslist' ),
                'edit_item'     => __( 'Edit Breed', 'petslist' ),
                'add_new_item'  => __( 'Add New Breed', 'petslist' ),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => 'dog-breed' ],
        ] );

        // Kennel taxonomy
        register_taxonomy( 'dd_kennel', 'dd_dog', [
            'labels'            => [
                'name'          => __( 'Kennels', 'petslist' ),
                'singular_name' => __( 'Kennel', 'petslist' ),
                'add_new_item'  => __( 'Add New Kennel', 'petslist' ),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => 'dog-kennel' ],
        ] );

        // Country/Location taxonomy
        register_taxonomy( 'dd_location', 'dd_dog', [
            'labels'            => [
                'name'          => __( 'Locations', 'petslist' ),
                'singular_name' => __( 'Location', 'petslist' ),
                'add_new_item'  => __( 'Add New Location', 'petslist' ),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => 'dog-location' ],
        ] );
    }

    public function add_dog_meta_boxes() {
        add_meta_box(
            'dd_dog_details',
            __( 'Dog Profile Details', 'petslist' ),
            [ $this, 'render_dog_meta_box' ],
            'dd_dog',
            'normal',
            'high'
        );
        add_meta_box(
            'dd_dog_health',
            __( 'Health & Awards', 'petslist' ),
            [ $this, 'render_dog_health_meta_box' ],
            'dd_dog',
            'normal',
            'default'
        );
        add_meta_box(
            'dd_dog_photos',
            __( 'Dog Photos', 'petslist' ),
            [ $this, 'render_dog_photos_meta_box' ],
            'dd_dog',
            'side',
            'default'
        );
    }

    public function render_dog_meta_box( $post ) {
        wp_nonce_field( 'dd_dog_meta_nonce', 'dd_dog_meta_nonce_field' );
        $meta = get_post_meta( $post->ID, '_dd_dog_meta', true ) ?: [];
        $fields = $this->get_dog_profile_fields();
        echo '<div class="dd-meta-grid">';
        foreach ( $fields as $key => $field ) {
            $value = isset( $meta[$key] ) ? esc_attr( $meta[$key] ) : '';
            echo '<div class="dd-meta-field">';
            echo '<label for="dd_' . esc_attr($key) . '">' . esc_html($field['label']) . '</label>';
            if ( $field['type'] === 'select' ) {
                echo '<select id="dd_' . esc_attr($key) . '" name="dd_dog_meta[' . esc_attr($key) . ']">';
                if ( $key === 'breed' ) {
                    // Breed uses structured optgroup renderer
                    dd_render_breed_options( $value );
                } else {
                    foreach ( $field['options'] as $val => $label ) {
                        $opt_val = is_numeric($val) && !is_string($val) ? $label : $val;
                        $sel = selected( $value, $opt_val, false );
                        echo '<option value="' . esc_attr($opt_val) . '"' . $sel . '>' . esc_html($label) . '</option>';
                    }
                }
                echo '</select>';

            } elseif ( $field['type'] === 'textarea' ) {
                echo '<textarea id="dd_' . esc_attr($key) . '" name="dd_dog_meta[' . esc_attr($key) . ']" rows="3">' . esc_textarea($value) . '</textarea>';
            } else {
                $type = $field['type'] === 'date' ? 'date' : 'text';
                echo '<input type="' . esc_attr($type) . '" id="dd_' . esc_attr($key) . '" name="dd_dog_meta[' . esc_attr($key) . ']" value="' . $value . '">';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    public function render_dog_health_meta_box( $post ) {
        $meta = get_post_meta( $post->ID, '_dd_dog_health', true ) ?: [];
        $fields = [
            'health_clearances' => [ 'label' => __('Health Clearances', 'petslist'), 'type' => 'textarea' ],
            'vaccinations'      => [ 'label' => __('Vaccinations', 'petslist'), 'type' => 'textarea' ],
            'pedigree'          => [ 'label' => __('Pedigree Information', 'petslist'), 'type' => 'textarea' ],
            'awards'            => [ 'label' => __('Awards & Titles', 'petslist'), 'type' => 'textarea' ],
            'microchip'         => [ 'label' => __('Microchip #', 'petslist'), 'type' => 'text' ],
        ];
        echo '<div class="dd-meta-grid">';
        foreach ( $fields as $key => $field ) {
            $value = isset( $meta[$key] ) ? $meta[$key] : '';
            echo '<div class="dd-meta-field dd-meta-field--full">';
            echo '<label for="dd_health_' . esc_attr($key) . '">' . esc_html($field['label']) . '</label>';
            if ( $field['type'] === 'textarea' ) {
                echo '<textarea id="dd_health_' . esc_attr($key) . '" name="dd_dog_health[' . esc_attr($key) . ']" rows="3">' . esc_textarea($value) . '</textarea>';
            } else {
                echo '<input type="text" id="dd_health_' . esc_attr($key) . '" name="dd_dog_health[' . esc_attr($key) . ']" value="' . esc_attr($value) . '">';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    public function render_dog_photos_meta_box( $post ) {
        $front_photo = get_post_meta( $post->ID, '_dd_front_photo', true );
        $side_photo  = get_post_meta( $post->ID, '_dd_side_photo', true );
        echo '<p><strong>' . __('Front Photo ID', 'petslist') . '</strong></p>';
        echo '<input type="text" name="dd_front_photo" value="' . esc_attr($front_photo) . '" class="widefat">';
        echo '<p><strong>' . __('Side Photo ID', 'petslist') . '</strong></p>';
        echo '<input type="text" name="dd_side_photo" value="' . esc_attr($side_photo) . '" class="widefat">';
        echo '<p class="description">' . __('Enter WordPress media attachment IDs.', 'petslist') . '</p>';
    }

    public function save_dog_meta( $post_id ) {
        if ( ! isset( $_POST['dd_dog_meta_nonce_field'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['dd_dog_meta_nonce_field'], 'dd_dog_meta_nonce' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['dd_dog_meta'] ) ) {
            $meta_data = array_map( 'sanitize_text_field', $_POST['dd_dog_meta'] );
            update_post_meta( $post_id, '_dd_dog_meta', $meta_data );

            // Sync the breed taxonomy term
            if ( ! empty( $meta_data['breed'] ) ) {
                $breed_name = dd_match_breed_name( $meta_data['breed'] );
                $term = term_exists( $breed_name, 'dd_breed' );
                if ( ! $term ) {
                    $term = wp_insert_term( $breed_name, 'dd_breed' );
                }
                if ( ! is_wp_error( $term ) ) {
                    wp_set_post_terms( $post_id, array( (int) $term['term_id'] ), 'dd_breed', false );
                }
            }
        }
        if ( isset( $_POST['dd_dog_health'] ) ) {
            update_post_meta( $post_id, '_dd_dog_health', array_map( 'sanitize_textarea_field', $_POST['dd_dog_health'] ) );
        }
        if ( isset( $_POST['dd_front_photo'] ) ) {
            update_post_meta( $post_id, '_dd_front_photo', absint( $_POST['dd_front_photo'] ) );
        }
        if ( isset( $_POST['dd_side_photo'] ) ) {
            update_post_meta( $post_id, '_dd_side_photo', absint( $_POST['dd_side_photo'] ) );
        }
    }

    public function get_dog_profile_fields() {
        return [
            'dog_name'          => [ 'label' => __('Dog Name', 'petslist'), 'type' => 'text' ],
            'breed'             => [ 
                'label' => __('Breed', 'petslist'), 
                'type' => 'select', 
                'options' => dd_get_breed_options()
            ],
            'gender'            => [ 'label' => __('Gender', 'petslist'), 'type' => 'select', 'options' => ['', 'Male', 'Female'] ],
            'dob'               => [ 'label' => __('Date of Birth', 'petslist'), 'type' => 'date' ],
            'color'             => [ 'label' => __('Color', 'petslist'), 'type' => 'text' ],
            'weight'            => [ 'label' => __('Weight (kg)', 'petslist'), 'type' => 'text' ],
            'registration_no'   => [ 'label' => __('Registration Number', 'petslist'), 'type' => 'text' ],
            'country'           => [ 'label' => __('Country', 'petslist'), 'type' => 'text' ],
            'city'              => [ 'label' => __('City', 'petslist'), 'type' => 'text' ],
            'contact_phone'     => [ 'label' => __('Contact Phone', 'petslist'), 'type' => 'text' ],
            'contact_email'     => [ 'label' => __('Contact Email', 'petslist'), 'type' => 'text' ],
            'contact_website'   => [ 'label' => __('Website', 'petslist'), 'type' => 'text' ],
            'is_sponsored'      => [ 'label' => __('Sponsored Ad (Homepage)', 'petslist'), 'type' => 'select', 'options' => ['No', 'Yes'] ],
        ];
    }

    public function dog_admin_columns( $columns ) {
        $new = [
            'cb'     => $columns['cb'],
            'image'  => __('Photo', 'petslist'),
            'title'  => __('Dog Name', 'petslist'),
            'breed'  => __('Breed', 'petslist'),
            'gender' => __('Gender', 'petslist'),
            'owner'  => __('Owner', 'petslist'),
            'status' => __('Status', 'petslist'),
            'date'   => $columns['date'],
        ];
        return $new;
    }

    public function dog_admin_column_content( $column, $post_id ) {
        $meta = get_post_meta( $post_id, '_dd_dog_meta', true ) ?: [];
        switch ( $column ) {
            case 'image':
                if ( has_post_thumbnail( $post_id ) ) {
                    echo get_the_post_thumbnail( $post_id, [50, 50] );
                }
                break;
            case 'breed':
                echo esc_html( $meta['breed'] ?? '—' );
                break;
            case 'gender':
                echo esc_html( $meta['gender'] ?? '—' );
                break;
            case 'owner':
                $author_id = get_post_field( 'post_author', $post_id );
                echo esc_html( get_the_author_meta( 'display_name', $author_id ) );
                break;
            case 'status':
                $status = get_post_status( $post_id );
                echo '<span class="dd-status dd-status--' . esc_attr($status) . '">' . esc_html( ucfirst($status) ) . '</span>';
                break;
        }
    }

    public function dog_single_template( $template ) {
        if ( is_singular( 'dd_dog' ) ) {
            $theme_template = get_template_directory() . '/templates/single-dog.php';
            if ( file_exists( $theme_template ) ) return $theme_template;
        }
        return $template;
    }

    public function dog_archive_template( $template ) {
        if ( is_post_type_archive( 'dd_dog' ) || is_tax( [ 'dd_breed', 'dd_kennel', 'dd_location' ] ) ) {
            $theme_template = get_template_directory() . '/templates/archive-dog.php';
            if ( file_exists( $theme_template ) ) return $theme_template;
        }
        return $template;
    }
}

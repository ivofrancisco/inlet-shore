<?php

/**
 * Registers a custom post types
 *
 * @since 1.0.0
 * @return void
 */
function formosa_post_types(): void
{
    // Registers a custom post type
    // for page sections.
    register_post_type('section', array(
        'public' => true,
        'show_in_rest' => true,
        'capability_type' => 'section',
        'map_meta_cap' => true,
        'labels' => array(
            'name' => 'Sections',
            'add_new_item' => 'Add New Section',
            'edit_item' => 'Edit Section',
            'all_items' => 'All Sections',
            'singular_name' => 'Section'
        ),
        'menu_icon' => 'dashicons-editor-insertmore'
    ));
}
// Hook the function to the
// WordPress initialization action.
add_action('init', 'formosa_post_types');

function formosa_dish_post_type(): void
{
    // Registers a custom post type
    // for food menus management.
    register_post_type('dish', array(
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor'),
        'capability_type' => 'dish',
        'map_meta_cap' => true,
        // 'taxonomies' => array('category'),
        'labels' => array(
            'name' => 'Dishes',
            'add_new_item' => 'Add Dish',
            'edit_item' => 'Edit Dish',
            'all_items' => 'All Dishes',
            'singular_name' => 'Dish'
        ),
        'menu_icon' => 'dashicons-food'
    ));
}
// Hook the function to the
// WordPress initialization action.
add_action('init', 'formosa_dish_post_type');

function formosa_category_taxonomy()
{
    $args = array(
        'labels' => array(
            'name' => 'Dish Categories',
            'singular_name' => 'Dish Category',
        ),
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite' => array(
            'slug' => 'dish-categories',
        ),
    );
    register_taxonomy('dish-categories', 'dish', $args);
}
add_action('init', 'formosa_category_taxonomy');

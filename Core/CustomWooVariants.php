<?php

namespace Core;

class CustomWooVariants
{
    /**
     * Create custom fields for WooCommerce variants.
     * @param string $label  Field label.
     * @param  string  $name      Field name.
     * @param  string  $type      Field type (text, textarea, select, radio, checkbox, hidden, etc.).
     * @param  array  $options   Additional options for the field.
     */
    public static function create(string $label, string $name, string $type, array $options = [])
    {
        add_action('woocommerce_product_after_variable_attributes', function ($loop, $variation_data, $variation) use ($label, $name, $type, $options) {
            switch ($type) {
                case 'text':
                    woocommerce_wp_text_input([
                        'id' => $name . '[' . $loop . ']',
                        'label' => $label,
                        'wrapper_class' => 'form-row',
                        'placeholder' => '',
                        'desc_tip' => 'true',
                        'description' => $options['description'] ?? '',
                        'value' => get_post_meta($variation->ID, $name, true),
                    ]);
                    break;

                case 'textarea':
                    woocommerce_wp_textarea_input([
                        'id' => $name . '[' . $loop . ']',
                        'label' => $label,
                        'wrapper_class' => 'form-row',
                        'value' => get_post_meta($variation->ID, $name, true),
                    ]);
                    break;

                case 'select':
                    woocommerce_wp_select([
                        'id' => $name . '[' . $loop . ']',
                        'label' => $label,
                        'wrapper_class' => 'form-row',
                        'description' => isset($options['description']) ? $options['description'] : '',
                        'value' => get_post_meta($variation->ID, $name, true),
                        'options' => isset($options['options']) ? $options['options'] : [],
                    ]);
                    break;

                case 'radio':
                    woocommerce_wp_radio([
                        'id' => $name . '[' . $loop . ']',
                        'label' => $label,
                        'wrapper_class' => 'form-row',
                        'value' => get_post_meta($variation->ID, $name, true),
                        'options' => isset($options['options']) ? $options['options'] : [],
                    ]);
                    break;

                case 'checkbox':
                    woocommerce_wp_checkbox([
                        'id' => $name . '[' . $loop . ']',
                        'label' => $label,
                        'wrapper_class' => 'form-row',
                        'value' => get_post_meta($variation->ID, $name, true),
                    ]);
                    break;

                default:
                    break;
            }
        }, 10, 3);

        add_action('woocommerce_save_product_variation', function ($variation_id, $loop) use ($name) {
            $value = !empty($_POST[$name][$loop]) ? $_POST[$name][$loop] : '';
            update_post_meta($variation_id, $name, sanitize_text_field($value));
        }, 10, 2);

        add_filter('woocommerce_available_variation', function ($variation) use ($name) {
            $variation[$name] = get_post_meta($variation['variation_id'], $name, true);
            return $variation;
        });
    }
}
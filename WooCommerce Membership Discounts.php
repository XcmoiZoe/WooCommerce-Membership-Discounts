<?php
/*
Plugin Name: WooCommerce Membership Discounts
Description: Apply membership-based quantity discounts in WooCommerce using WordPress user roles.
Version: 1.0.0
Author: Dev Emman
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Hook into WooCommerce
add_action('woocommerce_cart_calculate_fees', 'apply_membership_quantity_discounts');
add_action('woocommerce_before_cart_table', 'display_membership_discount_message');

function apply_membership_quantity_discounts($cart) {
    if (is_admin() && !defined('DOING_AJAX'))
        return;

    // Get the current user's role
    $user_role = get_user_role();

    $total_quantity = 0;

    // Calculate the total quantity of products in the cart
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $total_quantity += $cart_item['quantity'];
    }

    // Apply the discounts based on quantity and membership level
    if ($user_role === 'bronze') {
        $discount_percentage = 0.15; // 15% discount for all items
    } elseif ($user_role === 'silver') {
        if ($total_quantity <= 3) {
            $discount_percentage = 0.15; // 15% discount for 3 or below products
        } elseif ($total_quantity > 3) {
            $discount_percentage = 0.25; // 25% discount for above 4 products
        }
    } elseif ($user_role === 'gold') {
        if ($total_quantity <= 3) {
            $discount_percentage = 0.15; // 15% discount for 3 or below products
        } elseif ($total_quantity >= 4 && $total_quantity <= 11) {
            $discount_percentage = 0.25; // 25% discount for 4 to 11 products
        } elseif ($total_quantity > 11) {
            $discount_percentage = 0.30; // 30% discount for above 12 products
        }
    } else {
        $discount_percentage = 0; // No discount for unknown membership levels
    }

    // Calculate the discount amount
    $discount_amount = $cart->subtotal * $discount_percentage;

    // Add the discount to the cart as a negative fee
    $cart->add_fee('Membership Discount', -$discount_amount);
}

function get_user_role() {
    $user_id = get_current_user_id();
    $user_roles = get_userdata($user_id)->roles;

    if (!empty($user_roles)) {
        return strtolower($user_roles[0]);
    }

    return 'bronze'; // Default to the name of the bronze membership level if not found
}

function display_membership_discount_message() {
    $user_role = get_user_role();

    $message = '';

    if ($user_role === 'bronze') {
        $message = '<p>You are currently a Bronze member. You get a 15% discount on all items.</p>';
    } elseif ($user_role === 'silver') {
        $message = '<p>You are currently a Silver member. You get a 15% discount on 3 or below items, and a 25% discount on above 4 items.</p>';
    } elseif ($user_role === 'gold') {
        $message = '<p>You are currently a Gold member. You get a 15% discount on 3 or below items, a 25% discount on 4 to 11 items, and a 30% discount on above 11 items.</p>';
    }

    echo $message;
}

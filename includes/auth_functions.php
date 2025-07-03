<?php
/**
 * Authentication Functions for NSBM Shop
 */

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has admin privileges
 */
function is_admin()
{
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

/**
 * Redirect non-admin users
 */
function require_admin()
{
    if (!is_admin()) {
        header("Location: ../index.php");
        exit();
    }
}

/**
 * Secure password hashing
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Redirect to login if not authenticated
 */
function require_login()
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * CSRF token generation and validation
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
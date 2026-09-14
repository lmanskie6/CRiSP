<?php
require_once __DIR__ . '/database/config.php';

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateRequired($field, $fieldName) {
    return empty(trim($field)) ? "$fieldName is required." : null;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? null : "Please enter a valid email address.";
}

function validatePasswordMatch($password, $confirm) {
    return $password === $confirm ? null : "Passwords do not match.";
}

function checkUniqueUser($username, $email, $excludeUserId = null) {
    $pdo = getConnection();
    $sql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    $params = [$username, $email];
    if ($excludeUserId) {
        $sql .= " AND user_id != ?";
        $params[] = $excludeUserId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ? "Username or email is already registered." : null;
}

function validateNumeric($value, $min, $max, $fieldName) {
    return (is_numeric($value) && $value >= $min && $value <= $max) ? null : "$fieldName must be between $min and $max.";
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return ($d && $d->format($format) === $date) ? null : "Invalid date format. Use YYYY-MM-DD.";
}

function validate($data, $rules) {
    foreach ($rules as $field => $validators) {
        $value = $data[$field] ?? null;
        foreach ($validators as $validator) {
            if (is_callable($validator)) {
                $error = $validator($value, $field);
                if ($error) return $error;
            }
        }
    }
    return null;
}
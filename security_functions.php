<?php
// security_functions.php

define('MAX_FAILED_ATTEMPTS', 5);
define('LOCK_DURATION_MINUTES', 30);

function isAccountLocked(PDO $pdo, int $userId): bool {
    $stmt = $pdo->prepare("SELECT account_locked_until FROM utenti WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && $user['account_locked_until'] && strtotime($user['account_locked_until']) > time();
}

function logLoginAttempt(PDO $pdo, ?int $userId, string $ip, string $userAgent, bool $success): void {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (user_id, ip_address, user_agent, was_successful) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $ip, $userAgent, $success ? 1 : 0]);
}

function handleFailedLogin(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("UPDATE utenti SET failed_login_attempts = failed_login_attempts + 1, last_failed_login = NOW() WHERE id = ?");
    $stmt->execute([$userId]);

    $stmt = $pdo->prepare("SELECT failed_login_attempts FROM utenti WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user['failed_login_attempts'] >= MAX_FAILED_ATTEMPTS) {
        $lockUntil = date('Y-m-d H:i:s', strtotime("+" . LOCK_DURATION_MINUTES . " minutes"));
        $stmt = $pdo->prepare("UPDATE utenti SET account_locked_until = ? WHERE id = ?");
        $stmt->execute([$lockUntil, $userId]);
    }
}

function resetFailedAttempts(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("UPDATE utenti SET failed_login_attempts = 0, account_locked_until = NULL WHERE id = ?");
    $stmt->execute([$userId]);
}
?>
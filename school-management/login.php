<?php
// login.php
require_once 'config.php';
require_once 'auth.php';

$auth = new Auth();

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $result = $auth->login($username, $password);
        if ($result['success']) {
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
            error_log("Failed login attempt for user: {$username} from IP: {$_SERVER['REMOTE_ADDR']}");
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Admin Login - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background with gradient orbs - starts immediately */
        .bg-orbs {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            overflow: hidden;
            animation: fadeIn 0.01s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: floatOrb 20s ease-in-out infinite;
        }

        .orb-1 {
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            top: -200px;
            right: -200px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            bottom: -150px;
            left: -150px;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
            opacity: 0.3;
        }

        @keyframes floatOrb {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(50px, -50px) scale(1.1);
            }
            50% {
                transform: translate(-30px, 30px) scale(0.9);
            }
            75% {
                transform: translate(40px, 40px) scale(1.05);
            }
        }

        /* Particle animation - starts immediately */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            opacity: 0;
            animation: floatParticle 15s linear infinite;
        }

        @keyframes floatParticle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            5% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            95% {
                opacity: 0;
            }
            100% {
                transform: translateY(-10vh) rotate(720deg);
                opacity: 0;
            }
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            padding: 0 4px;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 28px;
            padding: 28px 28px 22px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }

        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.6);
        }

        .login-header {
            text-align: center;
            margin-bottom: 22px;
        }

        /* Animated logo with pulse - starts immediately */
        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            margin-bottom: 10px;
            position: relative;
            animation: pulseLogo 3s ease-in-out infinite;
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
        }

        @keyframes pulseLogo {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 12px 45px rgba(102, 126, 234, 0.5);
            }
        }

        .logo-wrapper i {
            font-size: 28px;
            color: white;
            animation: rotateIcon 20s linear infinite;
        }

        @keyframes rotateIcon {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .login-header h1 {
            font-size: clamp(20px, 4vw, 24px);
            font-weight: 800;
            color: white;
            margin-bottom: 2px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 0%, #a8b5e6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            word-break: break-word;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: clamp(11px, 2vw, 13px);
            margin: 0;
            font-weight: 400;
        }

        .admin-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.08);
            padding: 2px 12px;
            border-radius: 16px;
            font-size: clamp(9px, 1.5vw, 10px);
            color: rgba(255, 255, 255, 0.6);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 4px;
        }

        .alert {
            border-radius: 14px;
            padding: 10px 14px;
            font-size: clamp(12px, 1.8vw, 13px);
            border: none;
            margin-bottom: 16px;
            animation: shake 0.5s ease-in-out;
            backdrop-filter: blur(10px);
            word-break: break-word;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border-left: 3px solid #ef4444;
            backdrop-filter: blur(10px);
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            font-size: clamp(9px, 1.5vw, 10px);
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 4px;
            display: block;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .input-group {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            width: 100%;
        }

        .input-group:focus-within {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            background: rgba(255, 255, 255, 0.08);
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.4);
            padding: 8px 12px;
            font-size: clamp(13px, 1.8vw, 14px);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .input-group:focus-within .input-group-text {
            color: #667eea;
        }

        .form-control {
            border: none;
            padding: 8px 12px 8px 4px;
            font-size: clamp(13px, 2vw, 14px);
            transition: all 0.3s ease;
            background: transparent;
            color: white;
            min-height: 38px;
            width: 100%;
        }

        .form-control:focus {
            border-color: none;
            background: transparent;
            box-shadow: none;
            color: white;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.25);
            font-size: clamp(12px, 1.8vw, 13px);
        }

        .form-control:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px rgba(15, 15, 26, 0.8) inset !important;
            -webkit-text-fill-color: white !important;
        }

        .password-toggle {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.4);
            padding: 8px 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            flex-shrink: 0;
            font-size: clamp(13px, 1.8vw, 14px);
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        .password-toggle:focus {
            outline: none;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            margin-top: 2px;
            flex-wrap: wrap;
            gap: 6px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0;
            background: transparent;
            flex-shrink: 0;
            min-width: 16px;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-label {
            font-size: clamp(12px, 1.8vw, 13px);
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            user-select: none;
        }

        .forgot-link {
            font-size: clamp(12px, 1.8vw, 13px);
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 2px 0;
            border-bottom: 1.5px solid transparent;
            white-space: nowrap;
        }

        .forgot-link:hover {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: clamp(10px, 2vw, 12px);
            font-size: clamp(13px, 2vw, 14px);
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 14px;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.3px;
            min-height: 44px;
            cursor: pointer;
            touch-action: manipulation;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-login i {
            font-size: clamp(14px, 2vw, 16px);
            transition: transform 0.3s ease;
        }

        .btn-login:hover i {
            transform: translateX(3px);
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .login-footer .text-muted {
            color: rgba(255, 255, 255, 0.25);
            font-size: clamp(11px, 1.5vw, 12px);
        }

        /* ===== LOGIN CREDENTIALS - IMPROVED VISIBILITY ===== */
        .demo-credentials-wrapper {
            margin-top: 12px;
            padding: 14px 18px;
            background: rgba(102, 126, 234, 0.12);
            border-radius: 16px;
            border: 2px solid rgba(102, 126, 234, 0.25);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
        }

        .demo-credentials-wrapper:hover {
            background: rgba(102, 126, 234, 0.18);
            border-color: rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(102, 126, 234, 0.2);
        }

        /* Animated gradient border */
        .demo-credentials-wrapper::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #667eea);
            background-size: 300% 300%;
            border-radius: 18px;
            z-index: -1;
            animation: gradientBorder 4s ease-in-out infinite;
            opacity: 0.4;
        }

        @keyframes gradientBorder {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .demo-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: clamp(13px, 2vw, 15px);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #a8b5e6;
            text-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
        }

        .demo-label i {
            font-size: 16px;
            color: #667eea;
            animation: pulseIcon 2s ease-in-out infinite;
        }

        @keyframes pulseIcon {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.9); }
        }

        .demo-credentials {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 4px 0;
        }

        .demo-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            padding: 6px 16px 6px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .demo-item:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.3);
            transform: scale(1.02);
        }

        .demo-item .label {
            font-size: clamp(10px, 1.5vw, 11px);
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }

        .demo-item .value {
            font-size: clamp(14px, 2.2vw, 16px);
            font-weight: 700;
            font-family: 'Inter', monospace;
            letter-spacing: 0.5px;
            padding: 2px 6px;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.2);
        }

        .demo-item .value.username {
            color: #93c5fd;
            border: 1px solid rgba(147, 197, 253, 0.2);
        }

        .demo-item .value.password {
            color: #a78bfa;
            border: 1px solid rgba(167, 139, 250, 0.2);
        }

        .demo-divider {
            color: rgba(255, 255, 255, 0.15);
            font-size: 20px;
            font-weight: 100;
        }

        .demo-hint {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            padding: 6px 16px;
            background: rgba(102, 126, 234, 0.08);
            border-radius: 20px;
            font-size: clamp(11px, 1.6vw, 12px);
            color: rgba(255, 255, 255, 0.5);
            animation: fadeHint 3s ease-in-out infinite;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .demo-hint i {
            font-size: 12px;
            color: #667eea;
        }

        @keyframes fadeHint {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.02); }
        }

        .demo-hint strong {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== END LOGIN CREDENTIALS ===== */

        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ========== RESPONSIVE BREAKPOINTS ========== */

        /* Extra Small Devices (Phones) */
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .login-card {
                padding: 20px 16px 16px;
                border-radius: 22px;
            }

            .logo-wrapper {
                width: 50px;
                height: 50px;
                border-radius: 14px;
                margin-bottom: 8px;
            }

            .logo-wrapper i {
                font-size: 22px;
            }

            .login-header h1 {
                font-size: 20px;
            }

            .login-header {
                margin-bottom: 18px;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .forgot-link {
                white-space: normal;
            }

            .input-group {
                border-radius: 12px;
            }

            .btn-login {
                min-height: 42px;
                border-radius: 12px;
            }

            .login-footer {
                margin-top: 16px;
                padding-top: 14px;
            }

            .demo-credentials-wrapper {
                padding: 12px 14px;
                margin-top: 10px;
                border-radius: 14px;
            }

            .demo-credentials {
                gap: 10px;
            }

            .demo-item {
                padding: 4px 12px 4px 8px;
            }

            .demo-item .value {
                font-size: 13px;
            }

            .demo-divider {
                font-size: 16px;
            }

            .demo-hint {
                padding: 4px 12px;
                font-size: 10px;
                margin-top: 8px;
            }

            .demo-label {
                font-size: 12px;
                margin-bottom: 8px;
                letter-spacing: 1px;
            }

            .demo-label i {
                font-size: 14px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .orb-1 {
                width: 300px;
                height: 300px;
                top: -100px;
                right: -100px;
            }

            .orb-2 {
                width: 250px;
                height: 250px;
                bottom: -80px;
                left: -80px;
            }

            .orb-3 {
                width: 200px;
                height: 200px;
            }

            .form-control {
                min-height: 36px;
                padding: 6px 10px 6px 4px;
            }
        }

        /* Small Devices (Tablets) */
        @media (min-width: 481px) and (max-width: 768px) {
            .login-card {
                padding: 26px 24px 20px;
            }

            .logo-wrapper {
                width: 56px;
                height: 56px;
            }

            .login-container {
                max-width: 380px;
            }

            .orb-1 {
                width: 400px;
                height: 400px;
            }

            .orb-2 {
                width: 350px;
                height: 350px;
            }
        }

        /* Landscape Phones */
        @media (max-height: 600px) and (orientation: landscape) {
            body {
                padding: 10px;
                align-items: center;
            }

            .login-card {
                padding: 16px 20px 14px;
                border-radius: 18px;
            }

            .logo-wrapper {
                width: 40px;
                height: 40px;
                border-radius: 12px;
                margin-bottom: 6px;
            }

            .logo-wrapper i {
                font-size: 18px;
            }

            .login-header h1 {
                font-size: 18px;
                margin-bottom: 1px;
            }

            .login-header p {
                font-size: 11px;
            }

            .admin-badge {
                font-size: 8px;
                padding: 1px 8px;
                margin-top: 2px;
            }

            .login-header {
                margin-bottom: 12px;
            }

            .form-group {
                margin-bottom: 10px;
            }

            .form-options {
                margin-bottom: 14px;
            }

            .btn-login {
                min-height: 36px;
                padding: 6px;
                font-size: 13px;
            }

            .login-footer {
                margin-top: 12px;
                padding-top: 10px;
            }

            .demo-credentials-wrapper {
                padding: 8px 12px;
                margin-top: 6px;
                border-radius: 12px;
            }

            .demo-label {
                font-size: 10px;
                margin-bottom: 4px;
                letter-spacing: 0.8px;
            }

            .demo-label i {
                font-size: 11px;
            }

            .demo-credentials {
                gap: 6px;
            }

            .demo-item {
                padding: 3px 8px 3px 6px;
            }

            .demo-item .label {
                font-size: 8px;
            }

            .demo-item .value {
                font-size: 11px;
                padding: 1px 4px;
            }

            .demo-divider {
                font-size: 12px;
            }

            .demo-hint {
                margin-top: 4px;
                padding: 3px 10px;
                font-size: 9px;
            }

            .demo-hint i {
                font-size: 9px;
            }

            .form-control {
                min-height: 32px;
                padding: 4px 8px 4px 4px;
                font-size: 12px;
            }

            .input-group-text {
                padding: 4px 8px;
                font-size: 12px;
            }

            .password-toggle {
                padding: 4px 8px;
                font-size: 12px;
            }

            .orb-1, .orb-2, .orb-3 {
                display: none;
            }

            .particles {
                display: none;
            }
        }

        /* Very Small Screens */
        @media (max-width: 360px) {
            .login-card {
                padding: 16px 12px 14px;
                border-radius: 18px;
            }

            .logo-wrapper {
                width: 44px;
                height: 44px;
                border-radius: 12px;
            }

            .logo-wrapper i {
                font-size: 20px;
            }

            .login-header h1 {
                font-size: 18px;
            }

            .form-control {
                font-size: 13px;
                min-height: 34px;
            }

            .btn-login {
                min-height: 38px;
                font-size: 13px;
            }

            .form-check-input {
                width: 14px;
                height: 14px;
                min-width: 14px;
            }

            .demo-credentials-wrapper {
                padding: 10px 10px;
            }

            .demo-item .value {
                font-size: 12px;
            }

            .demo-label {
                font-size: 11px;
            }
        }

        /* Print and Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .orb {
                animation: none !important;
            }

            .particle {
                animation: none !important;
                opacity: 0.1 !important;
            }

            .logo-wrapper {
                animation: none !important;
            }

            .logo-wrapper i {
                animation: none !important;
            }

            .login-container {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }

            .btn-login::before {
                display: none !important;
            }

            .demo-credentials-wrapper::before {
                animation: none !important;
            }

            .demo-hint {
                animation: none !important;
            }

            .demo-label i {
                animation: none !important;
            }
        }

        /* High DPI Screens */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .login-card {
                backdrop-filter: blur(40px);
                -webkit-backdrop-filter: blur(40px);
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Orbs - Starts Immediately -->
    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Floating Particles - Starts Immediately -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-wrapper">
                    <i class="fas fa-school"></i>
                </div>
                <h1><?php echo htmlspecialchars(APP_NAME); ?></h1>
                <p>Administrator Access Only</p>
                <div class="admin-badge">
                    <i class="fas fa-shield-alt me-1"></i> Secure Login
                </div>
            </div>

            <!-- Error Messages -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-1"></i> Username or Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            placeholder="Enter your username or email"
                            value="<?php echo htmlspecialchars($username); ?>"
                            required
                            autocomplete="username"
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i> Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-key"></i>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                            minlength="6"
                        >
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="togglePassword"
                            aria-label="Toggle password visibility"
                        >
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="btnText">Sign In</span>
                    <span class="spinner" id="btnSpinner"></span>
                    <i class="fas fa-arrow-right" id="btnIcon"></i>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <!-- Login Credentials - Clearly Visible -->
                <div class="demo-credentials-wrapper">
                    <div class="demo-label">
                        <i class="fas fa-key"></i>
                        <span>Login Credentials</span>
                        <i class="fas fa-key" style="opacity: 0.5;"></i>
                    </div>
                    
                    <div class="demo-credentials">
                        <div class="demo-item">
                            <span class="label"><i class="fas fa-user"></i> Username</span>
                            <span class="value username">admin</span>
                        </div>
                        
                        <span class="demo-divider">|</span>
                        
                        <div class="demo-item">
                            <span class="label"><i class="fas fa-lock"></i> Password</span>
                            <span class="value password">password</span>
                        </div>
                    </div>
                    
                    <div class="demo-hint">
                        <i class="fas fa-arrow-up"></i>
                        <span>Use these credentials to <strong>sign in</strong> to your account</span>
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                
                <p class="text-muted mt-2">
                    <i class="fas fa-lock me-1"></i> Secure &bull; Encrypted
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';

            // Create particles immediately
            function createParticles() {
                const container = document.getElementById('particles');
                if (!container) return;
                
                const count = window.innerWidth < 480 ? 15 : 30;
                
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = Math.random() * 100 + '%';
                    const size = Math.random() * 3 + 2;
                    particle.style.width = size + 'px';
                    particle.style.height = size + 'px';
                    particle.style.animationDuration = (Math.random() * 20 + 10) + 's';
                    particle.style.animationDelay = (Math.random() * 10) + 's';
                    particle.style.opacity = Math.random() * 0.3 + 0.1;
                    container.appendChild(particle);
                }
            }
            
            // Create particles immediately
            createParticles();

            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });

                // Touch support for mobile
                togglePassword.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    this.click();
                });
            }

            // Form submission handler
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const btnIcon = document.getElementById('btnIcon');

            if (form) {
                form.addEventListener('submit', function(e) {
                    loginBtn.disabled = true;
                    btnText.textContent = 'Signing In...';
                    btnSpinner.style.display = 'inline-block';
                    btnIcon.style.display = 'none';

                    // Enable button after 5 seconds (fallback)
                    setTimeout(function() {
                        resetButton();
                    }, 5000);
                });
            }

            function resetButton() {
                loginBtn.disabled = false;
                btnText.textContent = 'Sign In';
                btnSpinner.style.display = 'none';
                btnIcon.style.display = 'inline-block';
            }

            // Auto-focus username field
            const usernameInput = document.getElementById('username');
            if (usernameInput && !usernameInput.value) {
                setTimeout(function() {
                    usernameInput.focus();
                }, 100);
            }

            // Remove shake animation after it completes
            document.querySelectorAll('.alert').forEach(function(alert) {
                alert.addEventListener('animationend', function() {
                    this.style.animation = '';
                });
            });

            // Handle resize for particles
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    const container = document.getElementById('particles');
                    if (container) {
                        container.innerHTML = '';
                        createParticles();
                    }
                }, 500);
            });

            // Handle orientation change
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    const container = document.getElementById('particles');
                    if (container) {
                        container.innerHTML = '';
                        createParticles();
                    }
                }, 300);
            });

        })();
    </script>
</body>
</html>
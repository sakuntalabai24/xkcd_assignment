<?php
require_once 'functions.php';

$step = $_POST['step'] ?? 'register';
$emailValue = $_POST['email'] ?? '';
$message = '';
$messageType = ''; // success or error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'register' && !empty($emailValue)) {
        $code = generateVerificationCode();
        saveCodeForEmail($emailValue, $code);
        if (sendVerificationEmail($emailValue, $code)) {
            $message = 'Verification code has been sent to your email.';
            $messageType = 'success';
            $step = 'verify';
        } else {
            $message = 'Failed to send verification code. Try again.';
            $messageType = 'error';
        }
    } elseif ($step === 'verify') {
        $code = $_POST['code'] ?? '';
        if (verifyCode($emailValue, $code)) {
            registerEmail($emailValue);
            $message = 'Code verified successfully! You are now subscribed.';
            $messageType = 'success';
            $step = 'registered';
        } else {
            $message = 'Invalid verification code. Please try again.';
            $messageType = 'error';
        }
    } elseif ($step === 'unsubscribe') {
        if (!empty($emailValue)) {
            $code = generateVerificationCode();
            saveCodeForEmail($emailValue, $code);
            if (sendVerificationEmail($emailValue, $code, true)) {
                $message = 'Verification code sent to your email to confirm unsubscription.';
                $messageType = 'success';
                $step = 'unsubscribeVerify';
            } else {
                $message = 'Failed to send verification code.';
                $messageType = 'error';
            }
        }
    } elseif ($step === 'unsubscribeVerify') {
        $code = $_POST['code'] ?? '';
        if (verifyCode($emailValue, $code)) {
            unsubscribeEmail($emailValue);
            $message = 'You have been unsubscribed.';
            $messageType = 'success';
            $step = 'register';
        } else {
            $message = 'Invalid code. Please try again.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>XKCD Email Subscription</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(145deg, #f0f0f0, #ffffff);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      width: 400px;
      padding: 35px 30px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #1a1a1a;
      transition: all 0.3s ease-in-out;
    }

    .container h2 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 26px;
      font-weight: 600;
      color: #1a1a1a;
    }

    input[type="email"],
    input[type="text"] {
      width: 100%;
      padding: 12px 14px;
      margin-bottom: 18px;
      border: none;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.35);
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
      font-size: 15px;
      color: #1a1a1a;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.08);
      outline: none;
      transition: all 0.3s ease;
    }

    input::placeholder {
      color: #555;
    }

    input:focus {
      background: rgba(255, 255, 255, 0.5);
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }

    button {
      width: 100%;
      padding: 12px;
      margin-top: 5px;
      border: none;
      border-radius: 10px;
      background: #0077ff;
      color: white;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    button:hover {
      background: #005ad3;
      transform: scale(1.02);
    }

    .message {
      text-align: center;
      margin-bottom: 18px;
      font-size: 14px;
      padding: 10px;
      border-radius: 10px;
    }
    .message.success {
      background-color: #d4edda;
      color: #155724;
    }
    .message.error {
      background-color: #f8d7da;
      color: #721c24;
    }

    form[action*="unsubscribe"] button,
    button[style*="e74c3c"] {
      background-color: #e74c3c !important;
    }

    form[action*="unsubscribe"] button:hover,
    button[style*="e74c3c"]:hover {
      background-color: #c0392b !important;
    }

    @media (max-width: 500px) {
      .container {
        width: 90%;
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>XKCD Email Subscription</h2>

    <?php if (!empty($message)): ?>
      <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($step === 'register'): ?>
      <form method="POST">
        <input type="hidden" name="step" value="register">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($emailValue) ?>" required>
        <button type="submit">Register</button>
      </form>

    <?php elseif ($step === 'verify'): ?>
      <form method="POST">
        <input type="hidden" name="step" value="verify">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($emailValue) ?>" required>
        <label>Verification Code:</label>
        <input type="text" name="code" required>
        <button type="submit">Verify</button>
      </form>

    <?php elseif ($step === 'unsubscribeVerify'): ?>
      <form method="POST">
        <input type="hidden" name="step" value="unsubscribeVerify">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($emailValue) ?>" required>
        <label>Verification Code:</label>
        <input type="text" name="code" required>
        <button type="submit" style="background-color: #e74c3c;">Confirm Unsubscribe</button>
      </form>

    <?php elseif ($step === 'registered'): ?>
      <div class="message success">You are successfully subscribed to XKCD updates.</div>
    <?php endif; ?>

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">

    <form method="POST">
      <input type="hidden" name="step" value="unsubscribe">
      <label>Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($emailValue) ?>" required>
      <button type="submit" style="background-color: #e74c3c;">Unsubscribe</button>
    </form>
  </div>

</body>
</html>

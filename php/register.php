<?php
// register.php
session_start();
require_once 'db_connect.php';

// 如果已經登入，就不允許再進來註冊，直接導向 welcome.php
if (isset($_SESSION['UserID'])) {
  header("Location: ../welcome.html");
  exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $account = trim($_POST['account'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $role = trim($_POST['role'] ?? '');

  // 基本欄位檢查
  if ($account === '') {
    $errors[] = '請輸入帳號。';
  }
  if (strlen($password) < 3) {
    $errors[] = '請輸入密碼 (至少 3 個字元)。';
  }
  if ($name === '') {
    $errors[] = '請輸入姓名。';
  }
  if ($role !== 'student' && $role !== 'teacher') {
    $errors[] = '請選擇正確的身分 (學生 或 教師)。';
  }

  // 檢查帳號是否重複
  if (empty($errors)) {
    $stmt = $mysqli->prepare("SELECT UserID FROM `user` WHERE Account = ?");
    $stmt->bind_param("s", $account);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $errors[] = '此帳號已被使用，請換一個。';
    }
    $stmt->close();
  }

  // 如果到這裡都沒有錯誤，就寫入資料庫
  if (empty($errors)) {
    $password_hashed = $password;
    $sql = "INSERT INTO `user` (Account, Password, Name, Role) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssss", $account, $password_hashed, $name, $role);

    if ($stmt->execute()) {
      $success = true;
    } else {
      $errors[] = '註冊失敗，請稍後再試。';
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>註冊</title>
  <!-- 假設你把登入/註冊頁面的 CSS 放在 ./css/login.css -->
  <link rel="stylesheet" href="../css/register.css" />
  <style>
    /* 以下只針對註冊成功訊息做微調，如果你有自己的 success/error 樣式，可以改成 class */
    .form-message {
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 4px;
    }

    .form-message.error {
      background: #ffe6e6;
      border: 1px solid #ffcccc;
      color: #cc0000;
    }

    .form-message.success {
      background: #e6ffea;
      border: 1px solid #ccffcc;
      color: #006600;
    }
  </style>
</head>

<body>
  <canvas id="autumnCanvas"></canvas>
  <div class="space"></div>
  <div class="container">
    <!-- 左邊卡片 -->
    <div class="card card-left">
      <a class="back-button" href="../index.html">← 返回首頁</a>
      <div class="divcontent">
        <h2>歡迎</h2>
        <p>已經註冊過了?</p>
        <button class="register-button" onclick="location.href='login.php'">登入</button>
      </div>
    </div>

    <!-- 中間卡片 (註冊表單) -->
    <div class="card card-middle">
      <h2>註冊</h2>

      <!-- 如果註冊成功，顯示綠底成功訊息，否則顯示錯誤 -->
      <?php if ($success): ?>
        <div class="form-message success">註冊成功！3 秒後自動跳回登入頁…</div>
        <script>
          setTimeout(function () {
            location.href = 'login.php?registered=1';
          }, 3000);
        </script>
      <?php else: ?>
        <?php if (!empty($errors)): ?>
          <div class="form-message error">
            <ul style="margin:0; padding-left:20px;">
              <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
          <input type="text" name="name" placeholder="姓名"
            value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
          <input type="text" name="account" placeholder="帳號"
            value="<?php echo htmlspecialchars($_POST['account'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
          <input type="password" name="password" placeholder="密碼 (至少 3 個字元)" required />
          <select name="role" required>
            <option value="" disabled <?php echo empty($_POST['role']) ? 'selected' : ''; ?>>身分 (請選擇)</option>
            <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>學生</option>
            <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'selected' : ''; ?>>教師</option>
          </select>
          <button class="login-button" type="submit">註冊</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- 右邊卡片 (裝飾用) -->
    <div class="card card-right">
      <div class="icon-placeholder">👁️‍🗨️</div>
    </div>
  </div>
  <div class="space"></div>
  <script>
    const canvas = document.getElementById('autumnCanvas');
    const ctx = canvas.getContext('2d');
    let W, H;
    function resize() {
      W = canvas.width = window.innerWidth;
      H = canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    class Leaf {
      constructor() {
        this.x = Math.random() * W;
        this.y = -20;
        this.size = 12 + Math.random() * 15;
        // 速度調整為 0.5 ~ 1.5
        this.speed = 0.5 + Math.random() * 1;
        this.angle = Math.random() * Math.PI;
        // 旋轉速度調整為 0.005 ~ 0.015
        this.angularSpeed = 0.005 + Math.random() * 0.01;
      }
      update() {
        this.y += this.speed;
        this.x += Math.sin(this.angle) * 0.5;
        this.angle += this.angularSpeed;
        if (this.y > H) {
          this.y = -20;
          this.x = Math.random() * W;
        }
      }
      draw() {
        ctx.save();
        ctx.translate(this.x, this.y);
        ctx.rotate(Math.sin(this.angle));
        ctx.fillStyle = `rgba(211, 144, 88, 0.8)`;
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.bezierCurveTo(this.size / 2, this.size / 2, this.size / 2, this.size, 0, this.size);
        ctx.bezierCurveTo(-this.size / 2, this.size, -this.size / 2, this.size / 2, 0, 0);
        ctx.fill();
        ctx.restore();
      }
    }

    // 減少葉子數量至 30
    const leaves = Array.from({ length: 30 }, () => new Leaf());
    function animate() {
      ctx.clearRect(0, 0, W, H);
      leaves.forEach(leaf => { leaf.update(); leaf.draw(); });
      requestAnimationFrame(animate);
    }
    animate();
  </script>
</body>

</html>
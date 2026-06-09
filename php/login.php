<?php
// login.php
session_start();           // 一定要在最上方呼叫，才能用 session
require_once 'db_connect.php';  // 引入我們剛剛寫好的 MySQL 連線

// 如果已經登入 (session 裡有 UserID)，就直接導到歡迎頁
if (isset($_SESSION['UserID'])) {
  header("Location: ../welcome.html");
  exit();
}

$errors = [];

// 若是 POST 表單送來的資料，就執行登入檢查
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $account = trim($_POST['account'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // 基本非空檢查
  if ($account === '') {
    $errors[] = '請輸入帳號。';
  }
  if ($password === '') {
    $errors[] = '請輸入密碼。';
  }

  if (empty($errors)) {
    // 從資料庫找有沒有這個帳號
    $stmt = $mysqli->prepare("SELECT UserID, Password, Name, Role FROM `user` WHERE Account = ?");
    $stmt->bind_param("s", $account);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
      // 取出該帳號的 hashed password、Name、Role
      $stmt->bind_result($dbUserID, $dbPasswordHash, $dbName, $dbRole);
      $stmt->fetch();

      // 用 password_verify 驗證使用者輸入密碼是否正確
      if ($password === $dbPasswordHash) {
        $_SESSION['UserID'] = $dbUserID;
        $_SESSION['Account'] = $account;
        $_SESSION['Name'] = $dbName;
        $_SESSION['Role'] = $dbRole;

        // 根據身分決定導向哪個頁面
        if ($dbRole === 'student') {
          header("Location: ../welcome.html");
        } elseif ($dbRole === 'teacher') {
          header("Location: ../theacherHomepage.html");
        } else {
          // 預設 fallback，如果 role 是未知的，導回登入
          header("Location: login.php");
        }
        exit();
      } else {
        $errors[] = '密碼錯誤，請重新輸入。';
      }
    } else {
      $errors[] = '找不到此帳號，請先註冊。';
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
  <title>登入</title>
  <link rel="stylesheet" href="../css/login.css" />
</head>

<body>
  <!-- Canvas 背景動畫 -->
  <canvas id="autumnCanvas"></canvas>
  <div class="space"></div>
  <div class="container">
    <!-- 左邊區塊 (提示切換到註冊) -->
    <div class="card card-left">
      <a class="back-button" href="../index.html">← 返回首頁</a>
      <div class="divcontent" id="left-content">
        <h2>歡迎</h2>
        <p>還沒註冊過?</p>
        <!-- 將註冊連結改向 register.php -->
        <button class="register-button" onclick="location.href='register.php'">註冊?</button>
      </div>
    </div>

    <!-- 中間區塊：登入表單 -->
    <div class="card card-middle" id="middle-card">
      <h2>登入</h2>
      <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
        <div class="success" style="background:#e6ffea; border:1px solid #ccffcc; padding:10px; margin-bottom: 15px;">
          註冊成功！請使用您的新帳號登入。
        </div>
      <?php endif; ?>
      <!-- 顯示錯誤訊息 -->
      <?php if (!empty($errors)): ?>
        <div class="errors">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- 登入表單：注意 name 屬性要和上方 $_POST 對應 -->
      <form method="POST" action="login.php">
        <input type="text" name="account" placeholder="使用者名稱/帳號"
          value="<?php echo htmlspecialchars($_POST['account'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
        <input type="password" name="password" placeholder="密碼" required />
        <button class="login-button" type="submit">登入</button>
      </form>
    </div>

    <!-- 右邊區塊 (純裝飾圖示) -->
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
<?php
session_start();
require_once 'db_connect.php';

$materialName = isset($_GET['name']) ? $mysqli->real_escape_string($_GET['name']) : '';
if ($materialName === '')
  die('缺少 name 參數');

// 讀取影片路徑與名稱
$stmt = $mysqli->prepare('SELECT content, name FROM video WHERE name=? LIMIT 1');
$stmt->bind_param('s', $materialName);
$stmt->execute();
$stmt->bind_result($videoPath, $videoName);
if (!$stmt->fetch())
  die('找不到影片');
$stmt->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
  <meta charset="UTF-8">
  <title>教材影片</title>
  <style>
    html,
    body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: "Microsoft JhengHei", sans-serif;
    }

    a {
      text-decoration: none;
    }

    #autumnCanvas {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      background: linear-gradient(to bottom, #FFDAB9, #A0522D);

    }

    .layout {
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    /* ===== Header ===== */
    .site-header {
      position: fixed;
      top: 0;
      width: 100%;
      height: 7vh;
      background: #cd863fad;
      backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      justify-items: center;
      z-index: 100;
    }

    .header-content {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-left: 20px;
    }

    .header-nav {
      margin-right: 20px;
    }

    /* Logo */
    .logo-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-icon {
      width: 3vh;
      height: 3vh;
      fill: currentColor;
      color: #FFF8DC;
    }

    .logo-text .site-title {
      font-size: 24pt;
      font-family: Arial, Helvetica, sans-serif;
      color: #FFF8DC;
    }

    .nav-link {
      text-decoration: none;
      color: #FFF8DC;
      font-size: 12pt;
      transition: color .3s;
    }

    .nav-link:hover {
      color: #FFDAB9;
    }

    .nav-link.about-us span {
      font-size: 10pt;
      margin-right: 20px;
    }

    .nav-link.login strong {
      font-size: 14pt;
    }

    /* nav {
      margin-left: auto;
    }

    nav a {
      color: #fff;
      text-decoration: none;
      margin-left: 15px;
    } */
    .back{
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: #8B4513;
      color: #FFDAB9;
      border-radius: 5px;
      cursor: pointer;
      z-index: 100;
    }
    .back:hover {
      background-color: #A0522D;
      color: #FFF8DC;
    }
    .course-nav {
      position: fixed;
      top: 7vh;
      width: 100%;
      height: 5vh;
      background: #fff5eeb1;
      border-bottom: 1px solid #DEB887;
      backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      z-index: 90;
      cursor: pointer;
    }

    .course-list-title {
      height: 5vh;
      width: 25vw;
      font-size: 14pt;
      font-weight: bold;
      justify-content: center;
      align-items: center;
      display: flex;
      padding-left: 3%;
      padding-right: 3%;
      color: #8B4513;
    }

    .course-list-title:hover {
      background-color: #A0522D;
      color: #FFDAB9;
    }

    .navbar {
      display: flex;
      align-items: center;
      background: #f9f9f9;
      border-bottom: 2px solid #8B4513;
      padding: 10px 20px;
    }

    .navbar span {
      font-size: 1.2rem;
      color: #8B4513;
      font-weight: bold;
    }

    .navbar a {
      margin-left: auto;
      text-decoration: none;
      color: #B22222;
    }

    .content {
      display: flex;
      height: calc(95vh - 60px);
      padding-top: 12vh;
    }


    /* .sidebar {
      background: rgba(210, 105, 30, 0.1);
      border: 2px solid #8B4513;
      border-radius: 8px;
      padding: 5px;
      margin: 5px;width: 10vw;
    }

    .sidebar h2 {
      margin: 10px;
      color: #D2691E;
      cursor: pointer;
    } */

    .sidebar h2:hover {
      background: #FFBF00;
      color: #8B4513;
    }

    .video-section {
      width: 100%;
      margin-top: 1%;
      margin-left: 10%;
      margin-right: 10%;
      background: rgba(210, 105, 30, 0.45);
      backdrop-filter: blur(10px);
      border: 2px solid #8B4513;
      border-radius: 8px;
      padding: 15px;
      display: flex;
      flex-direction: column;
    }

    .video-section h2 {
      margin: 15px;
      color: #D2691E;
      text-align: center;
    }

    .video-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 10px;
    }

    .video-container video {
      width: 100%;
      max-height: 70vh;
      border-radius: 8px;
      background: #000;

    }
  </style>
</head>

<body>
  <canvas id="autumnCanvas"></canvas>
  <div class="layout">
    <header class="site-header">
      <div class="header-content">
        <!-- Logo -->
        <div class="logo-container">
          <div class="logo-icon">
            <svg viewBox="0 0 24 24">
              <path
                d="M16 9h3l-5 7m-4-7h4l-2 8M5 9h3l2 7m5-12h2l2 3h-3m-5-3h2l1 3h-4M7 4h2L8 7H5m1-5L2 8l10 14L22 8l-4-6H6z">
              </path>
            </svg>
          </div>
          <div class="logo-text">
            <span class="site-title"><strong>數學教育平台</strong></span>
          </div>
        </div>

        <!-- 右側導覽連結：由「登入」改為「登出」 -->
        <nav class="header-nav">
          <a href="../about.html" class="nav-link about-us">
            <span>關於我們</span>
          </a>
          <!-- 這裡把 href 改成 logout.php，並把文字改為「登出」 -->
          <a href="./logout.php" class="nav-link login">
            <strong>登出</strong>
          </a>
        </nav>
      </div>
    </header>
    <nav class="course-nav">
      <a href="../welcome.html">
        <div class="course-list-title">首頁</div>
      </a>
      <a href="./menu.php">
        <div class="course-list-title">班級頁面</div>
      </a>
    </nav>
    <div class="content">
      <section class="video-section">
        <div class="back">
          <h2 onclick="history.back()">返回</h2>
        </div>
        <h2><?= htmlspecialchars($videoName, ENT_QUOTES) ?></h2>
        <div class="video-container">
          <video controls src="<?= htmlspecialchars($videoPath, ENT_QUOTES, 'UTF-8') ?>">
            您的瀏覽器不支援影片播放。
          </video>
        </div>
      </section>
    </div>
  </div>
  <script>
    // 落葉背景動畫
    const canvas = document.getElementById('autumnCanvas');
    const ctx = canvas.getContext('2d');
    let W, H;
    function resize() { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; }
    window.addEventListener('resize', resize);
    resize();
    class Leaf {
      constructor() { this.x = Math.random() * W; this.y = -20; this.size = 12 + Math.random() * 15; this.speed = 0.5 + Math.random(); this.angle = Math.random() * Math.PI; this.angularSpeed = 0.005 + Math.random() * 0.01; }
      update() { this.y += this.speed; this.x += Math.sin(this.angle) * 0.5; this.angle += this.angularSpeed; if (this.y > H) { this.y = -20; this.x = Math.random() * W; } }
      draw() { ctx.save(); ctx.translate(this.x, this.y); ctx.rotate(Math.sin(this.angle)); ctx.fillStyle = 'rgba(211,144,88,0.8)'; ctx.beginPath(); ctx.moveTo(0, 0); ctx.bezierCurveTo(this.size / 2, this.size / 2, this.size / 2, this.size, 0, this.size); ctx.bezierCurveTo(-this.size / 2, this.size, -this.size / 2, this.size / 2, 0, 0); ctx.fill(); ctx.restore(); }
    }
    const leaves = Array.from({ length: 30 }, () => new Leaf());
    (function animate() { ctx.clearRect(0, 0, W, H); leaves.forEach(l => { l.update(); l.draw(); }); requestAnimationFrame(animate); })();
  </script>
</body>

</html>
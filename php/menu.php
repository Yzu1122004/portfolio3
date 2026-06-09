<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['UserID'], $_SESSION['Role']))
  die('請先登入系統。');
$user_id = $_SESSION['UserID'];
$role = $_SESSION['Role'];

// 處理加入班級
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_classid'])) {
  $joinClassID = intval($_POST['join_classid']);
  if ($role !== 'student') {
    $err = '只有學生才能加入班級。';
  } else {
    $chk = $mysqli->prepare('SELECT 1 FROM class WHERE ClassID=?');
    $chk->bind_param('i', $joinClassID);
    $chk->execute();
    $exists = $chk->get_result()->num_rows > 0;
    $chk->close();
    if (!$exists) {
      $err = "找不到班級 ID：{$joinClassID}";
    } else {
      $chk2 = $mysqli->prepare('SELECT 1 FROM class_member WHERE ClassID=? AND MemberID=?');
      $chk2->bind_param('ii', $joinClassID, $user_id);
      $chk2->execute();
      $in = $chk2->get_result()->num_rows > 0;
      $chk2->close();
      if ($in)
        header("Location: class.php?classid={$joinClassID}");
      else {
        $ins = $mysqli->prepare('INSERT INTO class_member(ClassID,MemberID)VALUES(?,?)');
        $ins->bind_param('ii', $joinClassID, $user_id);
        $ins->execute();
        $ins->close();
        header("Location: class.php?classid={$joinClassID}");
      }
      exit;
    }
  }
}

// 取得課程與成員
$classes = [];
if ($role === 'teacher') {
  $stmt = $mysqli->prepare('SELECT ClassID,ClassName,TeacherID FROM class WHERE TeacherID=?');
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc())
    $classes[] = $r;
  $stmt->close();
} else {
  $stmt = $mysqli->prepare(
    'SELECT c.ClassID,c.ClassName,c.TeacherID FROM class c
     JOIN class_member m ON c.ClassID=m.ClassID
     WHERE m.MemberID=?'
  );
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc())
    $classes[] = $r;
  $stmt->close();
}
$members = [];
$mres = $mysqli->query('SELECT ClassID,MemberID FROM class_member');
while ($r = $mres->fetch_assoc())
  $members[$r['ClassID']][] = $r['MemberID'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
  <meta charset="UTF-8">
  <title>課程管理</title>
  <style>
    /* 全頁重構: Grid 佈局 */
    html,
    body {
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
      font-family: "Microsoft JhengHei", sans-serif;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
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

    /* ===== 第二排導覽 (課程列表) ===== */

    a {
      text-decoration: none;
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

    main {
      height: 80vh;
      padding: 10px;
      gap: 20px;
      display: grid;
      grid-template-columns: 2fr 1fr;
      margin-top: 16vh;
      margin-left: 7vw;
      margin-right: 7vw;
      flex: 1;
      overflow: auto;
    }

    .card {
      background: rgba(238, 138, 24, 0.8);
      border: 2px solid #8B4513;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      transition: box-shadow .3s;
    }

    .card:hover {
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .card h2 {
      margin: 0 0 10px;
      color: rgb(255, 255, 255);
    }

    .card p {
      margin: 4px 0;
      font-size: 0.9rem;
      color: #333;
    }

    .btn {
      display: inline-block;
      padding: 8px 12px;
      background: #D2691E;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
    }

    .btn:hover {
      background: #FFBF00;
      color: #8B4513;
    }

    .sidebar {
      background: rgba(200, 182, 170, 0.66);
      border: 2px solid #8B4513;
      border-radius: 8px;
      padding: 15px;
    }

    .sidebar h2 {
      margin-top: 0;
      color: #D2691E;
    }

    .sidebar form {
      display: flex;
      flex-direction: column;
    }

    .sidebar input {
      padding: 8px;
      margin: 10px 0;
      border: 1px solid #8B4513;
      border-radius: 4px;
    }

    .sidebar .error {
      color: #B22222;
    }
  </style>
</head>

<body>
  <canvas id="autumnCanvas"></canvas>
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
  <!-- 第二排導覽：課程列表 -->
  <nav class="course-nav">
    <a href="../welcome.html">
      <div class="course-list-title">首頁</div>
    </a>
  </nav>
  <main>
    <section>
      <?php if (empty($classes)): ?>
        <div class="card">
          <h2>目前沒有任何課程</h2>
        </div>
      <?php else:
        foreach ($classes as $c): ?>
          <div class="card">
            <h2><?= htmlspecialchars($c['ClassName']) ?></h2>
            <p>課程 ID：<?= htmlspecialchars($c['ClassID']) ?></p>
            <p>教師 ID：<?= htmlspecialchars($c['TeacherID']) ?></p>
            <p>成員：<?= isset($members[$c['ClassID']]) ? implode(', ', $members[$c['ClassID']]) : '無' ?></p>
            <a class="btn" href="class.php?classid=<?= $c['ClassID'] ?>">進入</a>
          </div>
        <?php endforeach; endif; ?>
    </section>
    <aside class="sidebar">
      <h2>加入班級</h2>
      <?php if (!empty($err)): ?>
        <div class="error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <form method="post" onsubmit="return validate();">
        <input type="text" id="cid" name="join_classid" placeholder="輸入班級 ID">
        <button type="submit" class="btn">加入 / 前往</button>
      </form>
    </aside>
  </main>
  <script>
    // 落葉動畫...
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
    function validate() {
      const v = document.getElementById('cid').value.trim();
      if (!/^\d+$/.test(v)) {
        alert('請輸入正確班級 ID');
        return false;
      }
      return true;
    }
  </script>
</body>

</html>
<?php
session_start();
require_once 'db_connect.php';

// 取得 classid 與 teacherId
$classid = isset($_GET['classid']) ? intval($_GET['classid']) : 0;
$teacher_stmt = $mysqli->prepare("SELECT TeacherID FROM class WHERE ClassID = ?");
$teacher_stmt->bind_param("i", $classid);
$teacher_stmt->execute();
$teacher_res = $teacher_stmt->get_result();
$teacherId = $teacher_res->num_rows ? intval($teacher_res->fetch_assoc()['TeacherID']) : 0;
$teacher_stmt->close();

// 撈取課程所有教材
$materials = [];
$material_stmt = $mysqli->prepare("SELECT * FROM material WHERE classID = ?");
$material_stmt->bind_param("i", $classid);
$material_stmt->execute();
$material_res = $material_stmt->get_result();
while ($row = $material_res->fetch_assoc()) {
  $materials[$row['materialID']] = [
    'name' => $row['name'],
    'type' => $row['type'],
    'questions' => []
  ];
}
$material_stmt->close();

// 撈取 teacher 底下所有題目
$question_stmt = $mysqli->prepare("SELECT DISTINCT name, MaterialID FROM materials_quations WHERE teacherid = ?");
$question_stmt->bind_param("i", $teacherId);
$question_stmt->execute();
$question_res = $question_stmt->get_result();
while ($row = $question_res->fetch_assoc()) {
  $mID = $row['MaterialID'];
  if (isset($materials[$mID]) && !in_array($row['name'], $materials[$mID]['questions'], true)) {
    $materials[$mID]['questions'][] = $row['name'];
  }
}
$question_stmt->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
  <meta charset="UTF-8">
  <title>課程管理頁面</title>
  <style>
    /* Canvas 背景 */
    #autumnCanvas {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: linear-gradient(to bottom, #FFDAB9, #A0522D);
      z-index: -1;
    }

    body {
      margin: 0;
      font-family: "Microsoft JhengHei", sans-serif;
    }

    a {
      text-decoration: none;
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

    .main {
      display: flex;
      height: calc(95vh - 60px);
      padding-top: 12vh;
      margin-left: 10%;
      margin-right: 10%;
    }

    .sidebar {
      margin: 20px;
      width: 200px;
      background: rgba(255, 255, 255, 0.53);
      backdrop-filter: blur(10px);
      border-right: 2px solid #8B4513;
      border-radius: 10px;
      padding: 10px;
    }

    .sidebar h3 {
      margin: 10px;
      padding: 10px;
      background: rgb(153, 109, 77);
      color: rgb(248, 248, 248);
      cursor: pointer;
      border-radius: 5px;
    }

    .sidebar ul {
      list-style: none;
      margin: 0;
      padding: 0;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
    }

    .sidebar ul.open {
      max-height: 300px;
    }

    .sidebar ul li {
      margin: 10px;
      padding: 10px;
      color: #fff;
      border-bottom: 1px solid #8B4513;
      color: #8B4513;
      cursor: pointer;
    }

    .sidebar ul li:hover {
      background: #FFBF00;
      color: #8B4513;
      border-radius: 5px;
    }

    .content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      position: relative;
    }

    .content .placeholder {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #8B4513;
      font-size: 1.2rem;
    }

    .section {
      opacity: 0;
      max-height: 0;
      overflow: hidden;
      transition: opacity 0.4s ease, max-height 0.4s ease;
    }

    .section.open {
      opacity: 1;
      max-height: 2000px;
    }

    .material,
    .task {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(210, 105, 30, 0.61);
      backdrop-filter: blur(10px);
      color: #fff;
      padding: 15px;
      margin-bottom: 15px;
      border: 2px solid #8B4513;
      border-radius: 10px;
    }

    .material button,
    .task button {
      background: #D2691E;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 1.2rem;
      color: #fff;
      cursor: pointer;
    }

    .material button:hover,
    .task button:hover {
      background: #FFBF00;
      color: #8B4513;
    }

    .task-info strong {
      font-size: 1.2rem;
      color: rgb(255, 255, 255);
    }

    .progress-container {
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .progress-bar {
      width: 150px;
      height: 16px;
      background: #e0e0e0;
      border-radius: 8px;
      overflow: hidden;
    }

    .progress-bar-fill {
      height: 100%;
      background: #FF7F50;
      width: 0;
      transition: width .3s;
    }

    .progress-text {
      font-size: 0.9rem;
      color:rgb(255, 255, 255);
      min-width: 40px;
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
  <nav class="course-nav">
    <a href="../welcome.html">
      <div class="course-list-title">首頁</div>
    </a>
    <a href="./menu.php">
      <div class="course-list-title">班級頁面</div>
    </a>
  </nav>
  <div class="main">
    <div class="sidebar">
      <h3 id="btn-material">教材</h3>
      <ul id="list-material">
        <?php foreach ($materials as $m):
          if ($m['type'] === '影片'): ?>
            <li data-name="<?= htmlspecialchars($m['name'], ENT_QUOTES) ?>"><?= htmlspecialchars($m['name']) ?></li>
          <?php endif; endforeach; ?>
      </ul>
      <h3 id="btn-task">任務</h3>
      <ul id="list-task">
        <?php $seenTaskNames = [];
        foreach ($materials as $m):
          if ($m['type'] === '題目' && !in_array($m['name'], $seenTaskNames, true)):
            $seenTaskNames[] = $m['name']; ?>
            <li data-name="<?= htmlspecialchars($m['name'], ENT_QUOTES) ?>"><?= htmlspecialchars($m['name']) ?></li>
          <?php endif; endforeach; ?>
      </ul>
    </div>
    <div class="content">
      <div class="placeholder">請選擇教材或任務</div>
      <div id="material-section" class="section">
        <?php foreach ($materials as $m):
          if ($m['type'] === '影片'): ?>
            <div class="material">
              <div><?= htmlspecialchars($m['name']) ?></div>
              <button onclick="location.href='material_video.php?name=<?= urlencode($m['name']) ?>'">&gt;</button>
            </div>
          <?php endif; endforeach; ?>
      </div>
      <div id="task-section" class="section">
        <?php $seenTasks = [];
        foreach ($materials as $m):
          if ($m['type'] === '題目' && !in_array($m['name'], $seenTasks, true)):
            $seenTasks[] = $m['name']; ?>
            <div class="task">
              <div class="task-info">
                <strong><?= htmlspecialchars($m['name']) ?></strong>
              </div>
              <button
                onclick="location.href='material_task.php?name=<?= urlencode($m['name']) ?>&classid=<?= $classid ?>'">&gt;</button>
            </div>
          <?php endif; endforeach; ?>
      </div>
    </div>
  </div>
  <script>
    // 落葉動畫
    const canvas = document.getElementById('autumnCanvas'), ctx = canvas.getContext('2d'); let W, H; function resize() { W = canvas.width = innerWidth; H = canvas.height = innerHeight; } window.addEventListener('resize', resize); resize(); class Leaf { constructor() { this.x = Math.random() * W; this.y = -20; this.size = 12 + Math.random() * 15; this.speed = 0.5 + Math.random(); this.angle = Math.random() * Math.PI; this.angularSpeed = 0.005 + Math.random() * 0.01; } update() { this.y += this.speed; this.x += Math.sin(this.angle) * 0.5; this.angle += this.angularSpeed; if (this.y > H) { this.y = -20; this.x = Math.random() * W; } } draw() { ctx.save(); ctx.translate(this.x, this.y); ctx.rotate(Math.sin(this.angle)); ctx.fillStyle = 'rgba(211,144,88,0.8)'; ctx.beginPath(); ctx.moveTo(0, 0); ctx.bezierCurveTo(this.size / 2, this.size / 2, this.size / 2, this.size, 0, this.size); ctx.bezierCurveTo(-this.size / 2, this.size, -this.size / 2, this.size / 2, 0, 0); ctx.fill(); ctx.restore(); } }; const leaves = Array.from({ length: 30 }, () => new Leaf()); function animate() { ctx.clearRect(0, 0, W, H); leaves.forEach(l => { l.update(); l.draw(); }); requestAnimationFrame(animate); } animate();
    // 切換列表與展開動畫
    // 取得 PHP 傳來的 classid
    const classid = <?= $classid ?>;
    const matBtn = document.getElementById('btn-material'), taskBtn = document.getElementById('btn-task');
    const listMat = document.getElementById('list-material'), listTask = document.getElementById('list-task');
    const sectionMat = document.getElementById('material-section'), sectionTask = document.getElementById('task-section');
    const placeholder = document.querySelector('.placeholder');
    matBtn.addEventListener('click', () => {
      listMat.classList.toggle('open'); listTask.classList.remove('open');
      placeholder.style.display = 'none';
      sectionTask.classList.remove('open'); sectionMat.classList.add('open');
    });
    taskBtn.addEventListener('click', () => {
      listTask.classList.toggle('open'); listMat.classList.remove('open');
      placeholder.style.display = 'none';
      sectionMat.classList.remove('open'); sectionTask.classList.add('open');
    });
    // 子項點擊同理
    listMat.querySelectorAll('li').forEach(li => li.addEventListener('click', () => {
      placeholder.style.display = 'none'; sectionTask.classList.remove('open'); sectionMat.classList.add('open');
    }));
    listTask.querySelectorAll('li').forEach(li => li.addEventListener('click', () => {
      placeholder.style.display = 'none'; sectionMat.classList.remove('open'); sectionTask.classList.add('open');
    }));
    // 點擊教材跳轉
    listMat.querySelectorAll('li').forEach(li => {
      li.addEventListener('click', () => {
        const name = li.dataset.name;
        window.location.href = `material_video.php?name=${encodeURIComponent(name)}`;
      });
    });

    // 點擊任務跳轉
    listTask.querySelectorAll('li').forEach(li => {
      li.addEventListener('click', () => {
        const name = li.dataset.name;
        window.location.href = `material_task.php?name=${encodeURIComponent(name)}&classid=${classid}`;
      });
    });
  </script>
</body>

</html>
<?php
/* ---------- 前置 ---------- */
session_start();
require_once 'db_connect.php';

$user_id = isset($_SESSION['UserID']) ? intval($_SESSION['UserID']) : 0;
$materialName = '';
$classid = '';

/* ---------- A. 處理 POST（學生送出作答） ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_answers') {
  // 取得並逃脫
  $materialName = $mysqli->real_escape_string($_POST['material_name']);
  $classid = intval($_POST['classid']);
  $correctCount = intval($_POST['correct_count']);
  $totalCount = intval($_POST['total_count']);

  // 檢查該學生是否已經有紀錄
  $stmt = $mysqli->prepare("
        SELECT complet_number, attemptCount
          FROM student_complet_quetion
         WHERE studentID = ? 
           AND name      = ? 
           AND classID   = ?
    ");
  $stmt->bind_param("isi", $user_id, $materialName, $classid);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows > 0) {
    // 已有紀錄 → UPDATE
    $row = $res->fetch_assoc();
    $newAttempt = $row['attemptCount'] + 1;

    $upd = $mysqli->prepare("
            UPDATE student_complet_quetion
               SET complet_number = ?,
                   attemptCount   = ?
             WHERE studentID     = ?
               AND name          = ?
               AND classID       = ?
        ");
    $upd->bind_param("iiisi", $correctCount, $newAttempt, $user_id, $materialName, $classid);
    $upd->execute();
    $upd->close();
  } else {
    // 尚無紀錄 → INSERT
    $initAttempt = 1;
    $ins = $mysqli->prepare("
            INSERT INTO student_complet_quetion
                (studentID, name, classID, complet_number, attemptCount)
            VALUES (?, ?, ?, ?, ?)
        ");
    $ins->bind_param("isiii", $user_id, $materialName, $classid, $correctCount, $initAttempt);
    $ins->execute();
    $ins->close();
  }

  $stmt->close();

  // 作答完畢後可重新導向或顯示訊息，這裡做簡單的重導回 GET 頁面
  header("Location: " . $_SERVER['PHP_SELF'] . "?name=" . urlencode($materialName) . "&classid=" . $classid);
  exit;
}

/* ---------- 1. 讀取 GET 參數並撈題目 ---------- */
if (isset($_GET['name'])) {
  $materialName = $mysqli->real_escape_string($_GET['name']);
}
if (isset($_GET['classid'])) {
  $classid = $mysqli->real_escape_string($_GET['classid']);
}

$questions = [];
$totalQuestions = 0;     // 真正要作答的題數（不含題目文字）

if ($materialName !== '') {
  // 1-1 找出所有該教材名稱的 materialID
  $materialIDs = [];
  $stmt = $mysqli->prepare("SELECT materialID FROM material WHERE name = ?");
  $stmt->bind_param("s", $materialName);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $materialIDs[] = $row['materialID'];
  }
  $stmt->close();

  // 1-2 取出所有題目
  if ($materialIDs) {
    $place = implode(',', array_fill(0, count($materialIDs), '?'));
    $types = str_repeat('s', count($materialIDs));  // materialID 為 varchar
    $sql = "SELECT * FROM question WHERE materialID IN ($place) ORDER BY question_order";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$materialIDs);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($q = $res->fetch_assoc()) {
      $questions[] = $q;
      if (in_array($q['type'], ['填空', '單選', '多選'], true)) {
        $totalQuestions++;
      }
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
  <meta charset="UTF-8">
  <title>任務題目</title>
  <style>
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
      font-family: "Microsoft JhengHei", sans-serif;
      margin: 0;

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

    .navbar {
      display: flex;
      align-items: center;
      background: #f9f9f9;
      padding: 10px 20px;
      border-bottom: 2px solid #8B4513;
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
      height: calc(92vh - 60px);
      margin-top: 12vh;
      margin-left: 10%;
      margin-right: 10%;
    }

    .sidebar {
      width: 200px;
      background: #D2691E;
      border-right: 2px solid #8B4513;

      border-radius: 10px;
    }

    .sidebar h3 {
      background: #8B4513;
      color: #FFBF00;
      padding: 10px;
      margin: 10px;
      border-radius: 5px;
      cursor: pointer;
    }

    .sidebar h3:hover {
      background: #FFBF00;
      color: #8B4513;
    }

    .content {
      flex: 1;
      padding: 30px;
      background: linear-gradient(to bottom right, #FFF5E1, #FFDAB9);
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
      overflow-y: auto;
    }

    .question-block {
      margin: 30px 0;
      padding: 20px;
      border: 2px solid #A0522D;
      border-radius: 12px;
      background: #F4A460;
      color: #fff;
      font-size: 1.2rem;
      box-shadow: 0 2px 6px rgba(160, 82, 45, 0.3);
    }

    .question-block.correct {
      border-color: #FFD700;
      background: #DEB887;
    }

    .question-block.wrong {
      border-color: #8B0000;
      background: #CD5C5C;
    }

    .label-highlight {
      /* background: #FFD700; */
      color: #4B2E2E;
      padding: 6px;
      border-radius: 6px;
      font-size: 2rem;
      font-weight: bold;
      display: inline-block;
    }

    button.primary {
      height: 44px;
      width: 110px;
      border-radius: 12px;
      background: #8B4513;
      color: #fff;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 10px;
    }

    button.primary:hover {
      background: #DAA520;
      color: #4B2E2E;
    }

    button.secondary {
      margin-left: 15px;
      height: 36px;
      padding: 0 16px;
      border-radius: 10px;
      background: #CD853F;
      color: #fff;
      border: none;
      cursor: pointer;
      font-size: 0.95rem;
    }

    button.secondary:hover {
      background: #FFA07A;
      color: #4B2E2E;
    }

    .result {
      margin-top: 8px;
      font-weight: bold;
      font-size: 1.1rem;
    }

    .answer-input {
      padding: 8px;
      font-size: 1rem;
      border-radius: 6px;
      border: 1px solid #8B4513;
      width: 80%;
      margin-top: 6px;
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
  <div class="main">
    <div class="sidebar">
      <h3 onclick="history.back()">返回課程</h3>
    </div>
    <div class="content">
      <form id="answerForm" method="post" action="">
        <input type="hidden" name="action" value="submit_answers">
        <input type="hidden" name="material_name" value="<?= htmlspecialchars($materialName) ?>">
        <input type="hidden" name="classid" value="<?= htmlspecialchars($classid) ?>">
        <input type="hidden" name="correct_count" id="correct_count" value="0">
        <input type="hidden" name="total_count" id="total_count" value="0">

        <button type="button" class="primary" onclick="checkAllAnswers()">提交答案</button>
        <button type="button" class="secondary" onclick="location.reload()">重新整理</button>

        <div class="question-container">
          <?php if ($questions): ?>
            <?php foreach ($questions as $idx => $q): ?>
              <?php
              $type = $q['type'];
              if ($type === '填空') {
                $correct = trim($q['text_or_answer']);
              } elseif ($type === '單選') {
                $correct = strtolower(trim($q['answer']));
              } elseif ($type === '多選') {
                $correct = preg_replace('/[^a-d]/i', '', strtolower($q['answer']));
              } else {
                $correct = '';
              }
              ?>
              <div class="question-block" data-type="<?= htmlspecialchars($type) ?>"
                data-answer="<?= htmlspecialchars($correct) ?>">
                <?php if ($type === '題目文字'): ?>
                  <p class="label-highlight"><?= htmlspecialchars($q['text_or_answer']) ?></p>
                <?php else: ?>
                  <p><strong><?= htmlspecialchars($type) ?></strong></p>
                  <?php if ($type === '單選'): ?>
                    <label><input type="radio" name="q<?= $idx ?>" value="a">
                      <?= htmlspecialchars($q['a_options']) ?></label><br>
                    <label><input type="radio" name="q<?= $idx ?>" value="b">
                      <?= htmlspecialchars($q['b_options']) ?></label><br>
                    <label><input type="radio" name="q<?= $idx ?>" value="c">
                      <?= htmlspecialchars($q['c_options']) ?></label><br>
                    <label><input type="radio" name="q<?= $idx ?>" value="d">
                      <?= htmlspecialchars($q['d_options']) ?></label><br>
                  <?php elseif ($type === '多選'): ?>
                    <label><input type="checkbox" name="q<?= $idx ?>[]" value="a">
                      <?= htmlspecialchars($q['a_options']) ?></label><br>
                    <label><input type="checkbox" name="q<?= $idx ?>[]" value="b">
                      <?= htmlspecialchars($q['b_options']) ?></label><br>
                    <label><input type="checkbox" name="q<?= $idx ?>[]" value="c">
                      <?= htmlspecialchars($q['c_options']) ?></label><br>
                    <label><input type="checkbox" name="q<?= $idx ?>[]" value="d">
                      <?= htmlspecialchars($q['d_options']) ?></label><br>
                  <?php elseif ($type === '填空'): ?>
                    <input type="text" name="q<?= $idx ?>" class="answer-input"
                      style="border:1px solid #8B4513; border-radius:4px; padding:4px;"><br>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>查無題目。</p>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
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
        this.speed = 0.5 + Math.random() * 1;
        this.angle = Math.random() * Math.PI;
        this.angularSpeed = 0.005 + Math.random() * 0.01;
      }
      update() {
        this.y += this.speed;
        this.x += Math.sin(this.angle) * 0.5;
        this.angle += this.angularSpeed;
        if (this.y > H) { this.y = -20; this.x = Math.random() * W; }
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
    const leaves = Array.from({ length: 30 }, () => new Leaf());
    function animate() {
      ctx.clearRect(0, 0, W, H);
      leaves.forEach(l => { l.update(); l.draw(); });
      requestAnimationFrame(animate);
    }
    animate();

    function checkAllAnswers() {
      const blocks = document.querySelectorAll('.question-block');
      let total = 0, wrong = 0;
      blocks.forEach(b => b.classList.remove('correct', 'wrong'));
      blocks.forEach(b => b.querySelectorAll('.result').forEach(r => r.remove()));

      blocks.forEach(block => {
        const type = block.dataset.type;
        if (type === '題目文字') return;
        const correct = (block.dataset.answer || '').toLowerCase().trim();
        let userAns = '';
        let ok = false;
        if (type === '單選') {
          const sel = block.querySelector('input[type="radio"]:checked');
          if (sel) { userAns = sel.value; ok = (userAns === correct); }
        } else if (type === '多選') {
          const sel = block.querySelectorAll('input[type="checkbox"]:checked');
          const vals = Array.from(sel).map(cb => cb.value).sort();
          userAns = vals.join(''); ok = (userAns === correct.split('').sort().join(''));
        } else if (type === '填空') {
          const inp = block.querySelector('input[type="text"]');
          userAns = (inp ? inp.value.trim() : ''); ok = (userAns === correct);
        }
        total++;
        const div = document.createElement('div');
        div.className = 'result'; div.textContent = ok ? '✔ 正確' : '✘ 錯誤';
        div.style.color = ok ? '#DAA520' : '#B22222';
        block.appendChild(div);
        block.classList.add(ok ? 'correct' : 'wrong'); if (!ok) wrong++;
      });
      document.getElementById('correct_count').value = total - wrong;
      document.getElementById('total_count').value = total;
      alert(wrong === 0 ? `全部正確！共 ${total} 題。` : `作答完畢：正確 ${total - wrong} / ${total} 題。`);

      // 延遲 3 秒後提交
      setTimeout(() => {
        document.getElementById('answerForm').submit();
      }, 3000);
    }
  </script>
</body>

</html>
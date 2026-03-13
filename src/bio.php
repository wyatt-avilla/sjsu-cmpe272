<?php
// ─────────────────────────────────────────────
//  LOGIC LAYER
// ─────────────────────────────────────────────
session_start();

define('STAFF_PASSWORD', 'staff_2026');

// Auth gate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === STAFF_PASSWORD) {
        $_SESSION['authorized'] = true;
    } else {
        $_SESSION['authorized'] = false;
        $auth_error = true;
    }
}

// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Visitor name form
$team_name = 'Bio Directory';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['visitor_name'])) {
    $visitor_name = trim($_POST['visitor_name']);
    if ($visitor_name !== '') {
        $_SESSION['visitor_name'] = $visitor_name;
    }
}
$visitor_name = $_SESSION['visitor_name'] ?? '';

$authorized = isset($_SESSION['authorized']) && $_SESSION['authorized'] === true;

// Member directory — string, int, float all represented
$members = [
    [
        'name'  => 'Lucas Nguyen',
        'age'   => 23,          // int
        'major' => 'Software Engineering',
        'gpa'   => 3.94,        // float
    ],
    [
        'name'  => 'Andrew Song',
        'age'   => 25,
        'major' => 'Software Engineering',
        'gpa'   => 3.78,
    ],
    [
        'name'  => 'Robbie Tambunting',
        'age'   => 26,
        'major' => 'Software Engineering',
        'gpa'   => 4.00,
    ],
    [
        'name'  => 'Wyatt Avilla',
        'age'   => 23,
        'major' => 'Software Engineering',
        'gpa'   => 3.87,
    ],
];

$member_count = count($members); // int
$special_phrase = 'Precision in logic leads to elegance in execution.'; // string
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bio Directory</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #f9f9f9;
      color: #1a1a1a;
      font-family: system-ui, sans-serif;
      font-size: 15px;
    }

    header {
      border-bottom: 1px solid #e2e2e2;
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    h1 { font-size: 1.1rem; font-weight: 600; }

    .auth-form { display: flex; gap: 0.5rem; align-items: center; }

    .auth-form input {
      border: 1px solid #e2e2e2;
      padding: 0.4rem 0.6rem;
      font-size: 0.85rem;
      width: 160px;
      background: #fff;
    }

    .auth-form button {
      background: #1a1a1a;
      color: #fff;
      border: none;
      padding: 0.4rem 0.8rem;
      font-size: 0.85rem;
      cursor: pointer;
    }

    .auth-error { color: #c0392b; font-size: 0.8rem; }

    .special-insight { font-style: italic; color: #555; font-size: 0.9rem; }

    .greeting { padding: 1rem 2rem; border-bottom: 1px solid #e2e2e2; font-size: 0.95rem; }
    .greeting form { display: inline-flex; gap: 0.5rem; align-items: center; }
    .greeting input { border: 1px solid #e2e2e2; padding: 0.4rem 0.6rem; font-size: 0.85rem; width: 160px; background: #fff; }
    .greeting button { background: #1a1a1a; color: #fff; border: none; padding: 0.4rem 0.8rem; font-size: 0.85rem; cursor: pointer; }

    main { max-width: 1100px; margin: 0 auto; padding: 2.5rem 2rem; }

    .page-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 2rem; }

    .directory-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 1px;
      background: #e2e2e2;
      border: 1px solid #e2e2e2;
    }

    .member-card {
      background: #f9f9f9;
      padding: 1.5rem;
    }

    .card-name { font-size: 1.05rem; font-weight: 600; margin-bottom: 0.25rem; }
    .card-major { font-size: 0.82rem; color: #888; margin-bottom: 1rem; }

    .card-stats { display: flex; gap: 2rem; }
    .stat-label { font-size: 0.72rem; color: #aaa; margin-bottom: 2px; }
    .stat-value { font-size: 0.95rem; }

    footer {
      border-top: 1px solid #e2e2e2;
      padding: 1rem 2rem;
      font-size: 0.75rem;
      color: #aaa;
      display: flex;
      justify-content: space-between;
    }

    @media (max-width: 600px) {
      header { flex-direction: column; align-items: flex-start; }
      .directory-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<header>
  <h1>Bio Directory</h1>

  <div>
    <?php if ($authorized): ?>
      <span class="special-insight">&ldquo;<?= htmlspecialchars($special_phrase) ?>&rdquo;</span>
      <form method="POST" action="" style="display:inline;">
        <button type="submit" name="logout" style="background:none;border:1px solid #e2e2e2;color:#aaa;font-size:0.75rem;padding:0.25rem 0.6rem;cursor:pointer;margin-left:0.75rem;">Log out</button>
      </form>
    <?php else: ?>
      <form class="auth-form" method="POST" action="">
        <?php if (isset($auth_error)): ?>
          <span class="auth-error">Wrong password.</span>
        <?php endif; ?>
        <input type="password" name="password" placeholder="Staff password" autocomplete="current-password" />
        <button type="submit">Enter</button>
      </form>
    <?php endif; ?>
  </div>
</header>

<div class="greeting">
  <?php if ($visitor_name !== ''): ?>
    Hello <strong><?= htmlspecialchars($visitor_name) ?></strong>, welcome to the <strong><?= htmlspecialchars($team_name) ?></strong> page.
  <?php else: ?>
    <form method="POST" action="">
      <input type="text" name="visitor_name" placeholder="Your name" maxlength="80" required />
      <button type="submit">Say hello</button>
    </form>
  <?php endif; ?>
</div>

<main>
  <p class="page-title">Members &mdash; <?= $member_count ?></p>

  <div class="directory-grid">
    <?php foreach ($members as $member): ?>
      <div class="member-card">
        <p class="card-name"><?= htmlspecialchars($member['name']) ?></p>
        <p class="card-major"><?= htmlspecialchars($member['major']) ?></p>
        <div class="card-stats">
          <div>
            <p class="stat-label">Age</p>
            <p class="stat-value"><?= htmlspecialchars((string)$member['age']) ?></p>
          </div>
          <div>
            <p class="stat-label">GPA</p>
            <p class="stat-value"><?= number_format($member['gpa'], 2) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<footer>
  <span>Bio Directory</span>
  <span><?= date('Y') ?></span>
</footer>

</body>
</html>

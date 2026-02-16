<?php
include 'config.php';

// 1. Get the total count
$total_res = $conn->query("SELECT COUNT(*) as total FROM dreams");
$total_count = ($total_res) ? $total_res->fetch_assoc()['total'] : 0;

// 2. Calculate progress to next milestone (every 50)
$next_milestone = (floor($total_count / 50) + 1) * 50;
$progress_percent = ($total_count / $next_milestone) * 100;

// 3. SPECIAL MERIT LOGIC
// Check for "The Novelist" (Any single dream > 300 words)
$all_dreams = $conn->query("SELECT content, dream_date FROM dreams ORDER BY dream_date DESC");
$max_words = 0;
$total_words = 0;
$dates = [];

if ($all_dreams->num_rows > 0) {
    while($row = $all_dreams->fetch_assoc()) {
        $word_count = str_word_count($row['content']);
        $total_words += $word_count;
        if ($word_count > $max_words) $max_words = $word_count;
        $dates[] = $row['dream_date'];
    }
}

// Achievement Booleans
$is_novelist = ($max_words >= 300);
$is_scholar = ($total_words >= 5000);

// Calculate Streak for "The Consistent"
$streak = 0;
$check_date = date('Y-m-d');
$unique_dates = array_unique($dates);
foreach($unique_dates as $d) {
    if ($d == $check_date) {
        $streak++;
        $check_date = date('Y-m-d', strtotime($check_date . ' -1 day'));
    } else if ($d < $check_date) { break; }
}
$is_consistent = ($streak >= 7);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dream Journal</title>
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.12);
            --accent: #a29bfe;
            --locked: rgba(255, 255, 255, 0.03);
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top, #1e1b4b, #0f172a);
            color: white; min-height: 100vh;
        }

        header {
            position: fixed; top: 0; width: 100%;
            background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px);
            z-index: 10; padding: 15px 0; border-bottom: 1px solid var(--border);
        }

        .header-content {
            max-width: 800px; margin: 0 auto; padding: 0 20px;
            display: flex; justify-content: space-between; align-items: flex-start;
        }

        .logo-area h1 { margin: 0; font-size: 1.2rem; font-weight: 300; letter-spacing: 3px; }
        .counter-sub { font-size: 0.9rem; font-weight: 500; opacity: 0.9; margin-top: 4px; color: var(--accent); }

        .nav-controls { display: flex; flex-direction: column; align-items: flex-end; gap: 12px; }
        .main-nav a { color: white; text-decoration: none; font-size: 0.8rem; opacity: 0.5; margin-left: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .main-nav a.active { opacity: 1; border-bottom: 1px solid var(--accent); padding-bottom: 4px; }

        .container { max-width: 800px; margin: 0 auto; padding: 160px 20px 50px 20px; }

        .progress-section {
            background: var(--glass); padding: 30px; border-radius: 20px;
            margin-bottom: 40px; border: 1px solid var(--border);
        }

        .progress-bar-bg { background: rgba(255,255,255,0.1); height: 10px; border-radius: 10px; margin: 15px 0; overflow: hidden; }
        .progress-fill { background: var(--accent); height: 100%; width: <?php echo $progress_percent; ?>%; box-shadow: 0 0 15px var(--accent); }

        .section-label { font-size: 0.7rem; text-transform: uppercase; opacity: 0.4; letter-spacing: 2px; margin: 40px 0 20px 0; display: block; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; }

        .badge {
            background: var(--locked); border: 1px solid var(--border);
            border-radius: 20px; padding: 25px 15px; text-align: center;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); opacity: 0.4;
        }

        .badge.unlocked {
            background: var(--glass); border-color: var(--accent);
            opacity: 1; transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .badge-icon { font-size: 2.2rem; margin-bottom: 12px; display: block; filter: grayscale(1); }
        .badge.unlocked .badge-icon { filter: grayscale(0); }
        
        .badge-title { font-size: 0.8rem; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #fff; }
        .badge-req { font-size: 0.65rem; opacity: 0.6; line-height: 1.2; }

    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo-area">
            <h1>Dream Journal</h1>
            <div class="counter-sub">Let's get Achievements! ✓</div>
        </div>
        <div class="nav-controls">
            <nav class="main-nav">
                <a href="index.php">Homepage</a>
                <a href="achievements.php" class="active">Achievements</a>
                <a href="stats.php">Stats</a>
            </nav>
        </div>
    </div>
</header>

<div class="container">
    <div class="progress-section">
        <h2 style="margin:0; font-weight: 300;">Milestone Progress</h2>
        <p style="font-size: 0.8rem; opacity: 0.6; margin-top: 5px;">
            Current Count: <strong><?php echo $total_count; ?></strong>. 
            Only <?php echo ($next_milestone - $total_count); ?> dreams left until your next badge.
        </p>
        <div class="progress-bar-bg">
            <div class="progress-fill"></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 0.7rem; opacity: 0.5;">
            <span><?php echo floor($total_count/50)*50; ?></span>
            <span><?php echo $next_milestone; ?></span>
        </div>
    </div>

    <span class="section-label">Volume Milestones</span>
    <div class="grid">
        <?php
        for ($i = 50; $i <= 500; $i += 50) {
            $is_unlocked = ($total_count >= $i);
            $class = $is_unlocked ? 'unlocked' : '';
            $icon = $is_unlocked ? '🏆' : '🔒';
            echo "<div class='badge $class'>";
            echo "<span class='badge-icon'>$icon</span>";
            echo "<div class='badge-title'>Rank $i</div>";
            echo "<div class='badge-req'>$i Total Entries</div>";
            echo "</div>";
        }
        ?>
    </div>

    <span class="section-label">Special Merits</span>
    <div class="grid">
        <div class="badge <?php echo $is_novelist ? 'unlocked' : ''; ?>">
            <span class="badge-icon">✍️</span>
            <div class="badge-title">The Novelist</div>
            <div class="badge-req">A single entry over 300 words</div>
        </div>

        <div class="badge <?php echo $is_scholar ? 'unlocked' : ''; ?>">
            <span class="badge-icon">📚</span>
            <div class="badge-title">The Scholar</div>
            <div class="badge-req">Total volume of 5,000+ words</div>
        </div>

        <div class="badge <?php echo $is_consistent ? 'unlocked' : ''; ?>">
            <span class="badge-icon">🔥</span>
            <div class="badge-title">Consistent</div>
            <div class="badge-req">Record a dream 7 days in a row</div>
        </div>
        
        <div class="badge <?php echo ($total_count >= 1) ? 'unlocked' : ''; ?>">
            <span class="badge-icon">🗝️</span>
            <div class="badge-title">The Archivist</div>
            <div class="badge-req">Record your very first memory</div>
        </div>
    </div>
</div>

</body>
</html>

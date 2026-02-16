<?php
include 'config.php';

// 1. Basic Stats
$total_res = $conn->query("SELECT COUNT(*) as total FROM dreams");
$total_count = ($total_res) ? $total_res->fetch_assoc()['total'] : 0;

// 2. Data Processing
$all_dreams = $conn->query("SELECT content, dream_date FROM dreams ORDER BY dream_date DESC");
$total_words = 0;
$day_counts = [];
$month_counts = [];
$year_counts = [];
$longest_word_count = 0;
$longest_dream_date = "";
$dates = [];

if ($all_dreams->num_rows > 0) {
    while($row = $all_dreams->fetch_assoc()) {
        $word_count = str_word_count($row['content']);
        $total_words += $word_count;
        $dates[] = $row['dream_date'];
        
        if ($word_count > $longest_word_count) {
            $longest_word_count = $word_count;
            $longest_dream_date = date("M j, Y", strtotime($row['dream_date']));
        }

        $day = date('l', strtotime($row['dream_date']));
        $day_counts[$day] = ($day_counts[$day] ?? 0) + 1;

        $month = date('F', strtotime($row['dream_date']));
        $month_counts[$month] = ($month_counts[$month] ?? 0) + 1;

        $year = date('Y', strtotime($row['dream_date']));
        $year_counts[$year] = ($year_counts[$year] ?? 0) + 1;
    }
    $avg_words = round($total_words / $total_count);
    arsort($day_counts);
    $top_day = key($day_counts);

    // Streak Logic
    $streak = 0;
    $check_date = date('Y-m-d');
    $unique_dates = array_unique($dates);
    foreach($unique_dates as $d) {
        if ($d == $check_date) {
            $streak++;
            $check_date = date('Y-m-d', strtotime($check_date . ' -1 day'));
        } else if ($d < $check_date) { break; }
    }
} else {
    $avg_words = 0; $top_day = "N/A"; $longest_word_count = 0; $streak = 0;
}
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

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 25px; }
        
        .stat-card {
            background: var(--glass); border: 1px solid var(--border);
            padding: 20px; border-radius: 18px;
        }
        .stat-card h3 { font-size: 0.6rem; text-transform: uppercase; opacity: 0.5; margin: 0 0 5px 0; letter-spacing: 1px; }
        .stat-card .val { font-size: 1.6rem; color: var(--accent); font-weight: 300; margin-bottom: 5px; display: block; }
        .stat-card p { font-size: 0.7rem; opacity: 0.5; line-height: 1.3; margin: 0; }

        .record-box {
            background: linear-gradient(135deg, rgba(162, 155, 254, 0.1), transparent);
            border: 1px solid var(--border);
            padding: 25px; border-radius: 25px; margin-bottom: 25px;
        }

        /* Activity Sections */
        .activity-section {
            background: var(--glass); border: 1px solid var(--border);
            padding: 22px; border-radius: 22px; margin-bottom: 25px;
        }
        .section-title { font-size: 0.7rem; text-transform: uppercase; opacity: 0.5; margin-bottom: 15px; letter-spacing: 1px; display: block; }
        
        .row {
            display: flex; justify-content: space-between; padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .row:last-child { border: none; }
        
        .bar-container { background: rgba(255,255,255,0.05); height: 6px; width: 80px; border-radius: 10px; overflow: hidden; margin-left: 15px; }
        .bar-fill { background: var(--accent); height: 100%; border-radius: 10px; }
        .count-text { font-size: 0.75rem; opacity: 0.6; min-width: 60px; text-align: right; }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo-area">
            <h1>Dream Journal</h1>
            <div class="counter-sub"><strong>Analytical</strong> Archive</div>
        </div>
        <div class="nav-controls">
            <nav class="main-nav">
                <a href="index.php">Homepage</a>
                <a href="achievements.php">Achievements</a>
                <a href="stats.php" class="active">Stats</a>
            </nav>
        </div>
    </div>
</header>

<div class="container">
    
    <div class="record-box">
        <h3 style="font-size: 0.65rem; text-transform: uppercase; opacity: 0.5; margin: 0;">Longest Recorded Memory</h3>
        <h2 style="margin: 5px 0 0 0; font-weight: 300; color: var(--accent);"><?php echo $longest_word_count; ?> Words</h2>
        <p style="margin: 5px 0 0 0; font-size: 0.75rem; opacity: 0.6;">Captured on <?php echo $longest_dream_date; ?>. This reflects your highest level of narrative detail.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Active Streak</h3>
            <span class="val"><?php echo $streak; ?> Days</span>
            <p>Consecutive days logged.</p>
        </div>
        <div class="stat-card">
            <h3>Avg. Depth</h3>
            <span class="val"><?php echo $avg_words; ?> WPE</span>
            <p>Average words per entry.</p>
        </div>
        <div class="stat-card">
            <h3>Prime Night</h3>
            <span class="val" style="font-size: 1.3rem;"><?php echo $top_day; ?>s</span>
            <p>Most active logging day.</p>
        </div>
        <div class="stat-card">
            <h3>Total Words</h3>
            <span class="val" style="font-size: 1.3rem;"><?php echo number_format($total_words); ?></span>
            <p>Subconscious word count.</p>
        </div>
    </div>

    <div class="activity-section">
        <span class="section-title">Yearly Trends</span>
        <?php 
        if(!empty($year_counts)) {
            foreach($year_counts as $year => $count): 
                $y_percent = ($count / max($year_counts)) * 100; ?>
                <div class="row">
                    <span style="font-size: 0.85rem;"><?php echo $year; ?></span>
                    <div style="display: flex; align-items: center;">
                        <span class="count-text"><?php echo $count; ?> entries</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?php echo $y_percent; ?>%;"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; 
        } else { echo "<p style='opacity:0.3; font-size:0.8rem;'>No data available.</p>"; } ?>
    </div>

    <div class="activity-section">
        <span class="section-title">Monthly Distribution</span>
        <?php 
        if(!empty($month_counts)) {
            foreach($month_counts as $month => $count): 
                $m_percent = ($count / max($month_counts)) * 100; ?>
                <div class="row">
                    <span style="font-size: 0.85rem;"><?php echo $month; ?></span>
                    <div style="display: flex; align-items: center;">
                        <span class="count-text"><?php echo $count; ?> entries</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?php echo $m_percent; ?>%;"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; 
        } else { echo "<p style='opacity:0.3; font-size:0.8rem;'>No data available.</p>"; } ?>
    </div>

</div>

</body>
</html>
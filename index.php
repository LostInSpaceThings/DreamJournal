<?php
// --- DATABASE CONNECTION CONFIG ---
include 'config.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- SAVE LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_dream'])) {
    $stmt = $conn->prepare("INSERT INTO dreams (dream_date, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['dream_date'], $_POST['content']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// --- SEARCH & SORT LOGIC ---
$sort_order = (isset($_GET['sort']) && $_GET['sort'] == 'oldest') ? 'ASC' : 'DESC';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$total_res = $conn->query("SELECT COUNT(*) as total FROM dreams");
$total_count = ($total_res) ? $total_res->fetch_assoc()['total'] : 0;

$sql = "SELECT * FROM dreams";
if (!empty($search_query)) {
    $sql .= " WHERE content LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}
$sql .= " ORDER BY dream_date $sort_order";
$result = $conn->query($sql);
$match_count = ($result) ? $result->num_rows : 0;
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

        /* --- HEADER --- */
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

        /* Main Page Links */
        .main-nav { margin-bottom: 5px; }
        .main-nav a { 
            color: white; text-decoration: none; font-size: 0.8rem; 
            opacity: 0.5; margin-left: 20px; text-transform: uppercase; letter-spacing: 1px;
            transition: 0.3s;
        }
        .main-nav a.active { opacity: 1; border-bottom: 1px solid var(--accent); padding-bottom: 4px; }
        .main-nav a:hover { opacity: 1; color: var(--accent); }

        /* Search & Sort */
        .search-bar {
            background: var(--glass); border: 1px solid var(--border);
            border-radius: 20px; padding: 6px 15px; color: white; outline: none;
            width: 180px; transition: 0.3s; font-size: 0.8rem;
        }
        .search-bar:focus { width: 220px; border-color: var(--accent); }

        .sort-links a { color: white; text-decoration: none; font-size: 0.7rem; opacity: 0.4; margin-left: 10px; text-transform: uppercase; }
        .sort-links a.active { opacity: 1; color: var(--accent); font-weight: bold; }

        .feed-container { max-width: 800px; margin: 0 auto; padding: 160px 20px 50px 20px; }

        /* --- CARDS --- */
        .dream-card {
            background: var(--glass); backdrop-filter: blur(12px);
            border: 1px solid var(--border); border-radius: 20px;
            padding: 25px; margin-bottom: 30px;
            transition: 0.3s;
        }
        .dream-card h2 { margin: 0 0 10px 0; color: var(--accent); font-size: 1rem; font-weight: 400; }
        .dream-card p { line-height: 1.8; opacity: 0.9; margin: 0; white-space: pre-wrap; }

        .fab {
            position: fixed; bottom: 40px; right: 40px;
            width: 60px; height: 60px; background: #6c5ce7;
            border-radius: 50%; border: none; color: white;
            font-size: 30px; cursor: pointer; z-index: 100;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        #modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 200;
            justify-content: center; align-items: center;
        }

        .modal-content {
            background: #1e1b4b; padding: 30px; border-radius: 25px;
            width: 90%; max-width: 450px; border: 1px solid var(--accent);
        }

        input[type="date"], textarea {
            width: 100%; padding: 12px; margin: 10px 0 15px 0;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 10px; color: white; box-sizing: border-box; font-family: inherit;
        }

        .btn-submit { background: #6c5ce7; color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo-area">
            <h1>Dream Journal</h1>
            <div class="counter-sub">
                <?php if (!empty($search_query)): ?>
                    <strong><?php echo $match_count; ?></strong> results found
                <?php else: ?>
                    <strong><?php echo $total_count; ?></strong> archived entries
                <?php endif; ?>
            </div>
        </div>
        
        <div class="nav-controls">
            <nav class="main-nav">
                <a href="index.php" class="active">Homepage</a>
                <a href="achievements.php">Achievements</a>
                <a href="stats.php">Stats</a>
            </nav>

            <form action="index.php" method="GET">
                <input type="text" name="search" class="search-bar" placeholder="Find a dream..." value="<?php echo htmlspecialchars($search_query); ?>">
            </form>

            <div class="sort-links">
                <a href="?sort=recent&search=<?php echo urlencode($search_query); ?>" class="<?php echo ($sort_order == 'DESC') ? 'active' : ''; ?>">Recent</a>
                <a href="?sort=oldest&search=<?php echo urlencode($search_query); ?>" class="<?php echo ($sort_order == 'ASC') ? 'active' : ''; ?>">Oldest</a>
            </div>
        </div>
    </div>
</header>

<div class="feed-container">
    <?php
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $formatted_date = date("m-d-Y", strtotime($row["dream_date"]));
            echo '<div class="dream-card">';
            echo '<h2>' . htmlspecialchars($formatted_date) . '</h2>';
            echo '<p>' . nl2br(htmlspecialchars($row["content"])) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p style="text-align:center; opacity:0.3; margin-top:100px;">No memories found.</p>';
    }
    $conn->close();
    ?>
</div>

<button class="fab" onclick="document.getElementById('modal').style.display='flex'">+</button>

<div id="modal" onclick="if(event.target == this) this.style.display='none'">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h3 style="margin-top:0; font-weight:300; opacity:0.8;">New Entry</h3>
        <form method="POST">
            <label style="font-size: 0.7rem; opacity: 0.5; text-transform: uppercase;">Dream Date</label>
            <input type="date" name="dream_date" value="<?php echo date('Y-m-d'); ?>" required>
            <textarea name="content" rows="8" placeholder="Begin writing..." required></textarea>
            <button type="submit" name="save_dream" class="btn-submit">Save Memory</button>
        </form>
    </div>
</div>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations | Sustainable Travel Planner</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/destinations.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
    <header class="sub-header">
        <div class="container nav">
            <div>
                <h1 class="page-title">Eco-Rated Destinations</h1>
                <p class="tagline">Explore the world by region and sustainability rating.</p>
            </div>
            <nav class="nav-container">
            <button class="menu-toggle" aria-label="Toggle navigation">
                <span class="hamburger"></span>
            </button>

            <div class="nav-links">
                <a class="btn btn-outline" href="../index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
                    <a class="btn btn-outline" href="plan_trip.php">Plan Trip</a>
                    <a class="btn btn-outline" href="../auth/logout.php">Logout</a>
                    <a href="edit_user_profile.php" class="nav-profile" aria-label="Edit Profile">
                            <img src="<?php echo $user_pic; ?>" class="nav-avatar" alt="User Avatar">
                            <span class="mobile-profile-text">Edit Profile</span>
                    </a>
                  <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="container stack-gap">
        <section class="panel search-panel">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search destinations..." value="<?php echo htmlspecialchars($search); ?>">
                <div class="search-btn-container">
                    <button class="custom-btn-search" type="submit">🔍︎</button>
                    <a href="destinations.php" class="custom-btn-reset">Reset</a>
                </div>
            </form>
        </section>

        <section class="destination-results">
            <?php
            $currentRegion = '';
            if (!$destinations):
            ?>
                <div class="panel empty-results">
                    <p class="muted">No destinations found matching your search.</p>
                </div>
            <?php
            else:
                foreach ($destinations as $destination):
                    $region = getRegionName($destination['location']);

                    if ($region !== $currentRegion):
                        if ($currentRegion !== '') echo '</div>'; // Close previous grid
                        $currentRegion = $region;
            ?>
                        <h2 class="region-divider"><?php echo $currentRegion; ?></h2>
                        <div class="destination-grid">
            <?php
                    endif;
            ?>
                    <article class="destination-card">
                        <div class="destination-top">
                            <div class="destination-info">
                                <h3><?php echo htmlspecialchars($destination['name']); ?></h3>
                                <p class="muted">📍 <?php echo htmlspecialchars($destination['location']); ?></p>
                            </div>
                            <div class="rating-container">
                                <span class="rating-pill">
                                    <?php echo ecoBadge((int)$destination['eco_rating']); ?>
                                    <?php echo (int)$destination['eco_rating']; ?>/5
                                </span>
                            </div>
                        </div>
                        <p><?php echo htmlspecialchars($destination['description']); ?></p>
                        <div class="note success">
                            <strong>Eco Notes:</strong> <?php echo htmlspecialchars($destination['eco_notes']); ?>
                        </div>
                    </article>
            <?php
                endforeach;
                echo '</div>'; // Final grid closure
            endif;
            ?>
        </section>
    </main>

    <footer>
        <div class="container destinations-footer">
            <p class="muted">&copy; 2026 Eco-Friendly Travel Planner by Jane 🩷</p>
            <p class="muted">CMSC 207 Project</p>
        </div>
    </footer>
</body>
</html>

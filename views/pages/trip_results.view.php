<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Analysis | Sustainable Travel Planner</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/trip_results.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
    <header class="sub-header">
        <div class="container nav">
            <div>
                <h1 class="page-title">Trip Analysis</h1>
                <p class="tagline"><?php echo $tripId ? 'Your updates were saved!' : 'Your itinerary has been analyzed and saved.'; ?></p>
            </div>

            <nav class="nav-container">
                <button class="menu-toggle" aria-label="Toggle navigation">
                    <span class="hamburger"></span>
                </button>

                <div class="nav-links">
                    <a class="btn btn-outline" href="../index.php">Home</a>
                    <a class="btn btn-outline" href="destinations.php">Destinations</a>
                    <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
                    <a class="btn btn-outline" href="../auth/logout.php">Logout</a>
                    <a class="btn btn-primary" href="plan_trip.php">Plan New Trip</a>
                    <a href="edit_user_profile.php" class="nav-profile" aria-label="Edit Profile">
                            <img src="<?php echo $user_pic; ?>" class="nav-avatar" alt="User Avatar">
                            <span class="mobile-profile-text">Edit Profile</span>
                    </a>
                </div> </nav>
        </div>
    </header>

    <main class="container trip-results-main">
        <!-- Main Result Panel -->
        <section class="result-header-panel">
            <div>
                <span class="eyebrow" style="color: #89be40; letter-spacing: 1px; font-weight: bold; font-size: 0.75rem;">PLANNED DESTINATION</span>
                <h2 style="margin: 0.5rem 0; font-size: 2rem;"><?php echo htmlspecialchars($destination['name']); ?></h2>
                <p class="muted" style="font-size: 1.1rem;">📍 <?php echo htmlspecialchars($destination['location']); ?></p>
            </div>
            <div style="text-align: right;">
                <span style="display: block; font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Sustainability Score</span>
                <div class="score-value"><?php echo $sustainabilityScore; ?><span style="font-size: 1.2rem; color: #ccc;">/100</span></div>
            </div>
        </section>

        <div class="analysis-grid">
            <!-- Left: Journey Stats -->
            <section class="panel">
                <h3 style="margin-top: 0; color: #2d3748;">Journey Insights</h3>
                <div class="stat-grid">
                    <div class="stat-item">
                        <small class="muted" style="display:block; text-transform:uppercase;">Footprint</small>
                        <strong style="color: #e53e3e; font-size: 1.2rem;"><?php echo number_format($estimatedEmission, 1); ?> kg CO₂e</strong>
                    </div>
                    <div class="stat-item">
                        <small class="muted" style="display:block; text-transform:uppercase;">Transport</small>
                        <strong style="font-size: 1.2rem;"><?php echo ucwords(str_replace('_', ' ', $transportMode)); ?></strong>
                    </div>
                    <div class="stat-item">
                        <small class="muted" style="display:block; text-transform:uppercase;">Distance</small>
                        <strong style="font-size: 1.2rem;"><?php echo number_format($distanceKm, 1); ?> km</strong>
                    </div>
                    <div class="stat-item">
                        <small class="muted" style="display:block; text-transform:uppercase;">Eco-Rating</small>
                        <strong style="font-size: 1.2rem;"><?php echo str_repeat('🍃', (int)$destination['eco_rating']); ?></strong>
                    </div>
                </div>

                <?php if (!empty($suggestion)): ?>
                    <div class="tip-box">
                        <?php echo $suggestion; ?>
                    </div>
                <?php endif; ?>

                <div class="disclaimer-text">
                    <strong>Disclaimer:</strong>
                    Carbon footprints and sustainability scores are estimated. Our current calculation uses average emission factors and assumes your single selected transport mode is used for the entire distance. Actual emissions may vary if multiple transport types are used during the trip.
                </div>
            </section>

            <!-- Right: Sustainable Tips -->
            <section class="panel eco-notes-panel">
                <h3 style="color: #2e7d32; margin-top: 0;">🌿 Sustainable Tourism Tips</h3>
                <p style="line-height: 1.7; color: #2d3748;">
                    <?php echo htmlspecialchars($destination['eco_notes'] ?? 'Explore responsibly by minimizing waste and respecting local cultural sites.'); ?>
                </p>
                <div class="note success" style="margin-top: 1rem; background: rgba(255,255,255,0.5);">
                    Small choices like bringing reusable water bottles or staying in locally-owned lodges make a massive difference.
                </div>
            </section>
        </div>

        <!-- Planning Notes -->
        <?php if (!empty($notes)): ?>
            <section class="panel notes-preview">
                <strong>Your Itinerary Notes</strong>
                <p><?php echo htmlspecialchars($notes); ?></p>
            </section>
        <?php endif; ?>

        <p class="text-center small">
            <a href="dashboard.php" class="back-link">&larr; View Itineraries</a>
        </p>
    </main>

    <footer>
        <div class="container result-footer">
            <p>&copy; 2026 Eco-Friendly Travel Planner by Jane 🩷</p>
            <p>CMSC 207 Project</p>
        </div>
    </footer>
</body>
</html>

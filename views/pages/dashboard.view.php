<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sustainable Travel Planner</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="../assets/js/main.js" defer></script>
</head>

<body>

    <header class="sub-header">
        <div class="container nav">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="tagline">Welcome back, <?php echo htmlspecialchars((string)($_SESSION['user_name'] ?? 'Traveler'), ENT_QUOTES, 'UTF-8'); ?>! 👋</p>
            </div>
            <nav class="nav-container">
                <button class="menu-toggle" aria-label="Toggle navigation">
                    <span class="hamburger"></span>
                </button>

                <div class="nav-links">
                    <a class="btn btn-outline" href="../index.php">Home</a>
                    <a class="btn btn-outline" href="destinations.php">Destinations</a>
                    <a class="btn btn-outline" href="plan_trip.php">Plan Trip</a>
                    <a class="btn btn-outline" href="../auth/logout.php">Logout</a>
                    <a href="edit_user_profile.php" class="nav-profile" aria-label="Edit Profile">
                            <img src="<?php echo $user_pic; ?>" class="nav-avatar" alt="User Avatar">
                            <span class="mobile-profile-text">Edit Profile</span>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container dashboard-grid">
    <section class="dashboard-main-content">
        <div class="header-flex">
            <h2 class="panel-title">Recent Itineraries</h2>
            <?php if (!empty($recentTrips)): ?>
                <span class="count-badge"><?php echo count($recentTrips); ?> Total</span>
            <?php endif; ?>
        </div>

        <?php if (($_GET['msg'] ?? '') === 'updated'): ?>
            <div class="alert-success">Trip updated successfully!</div>
        <?php elseif (($_GET['error'] ?? '') === 'update_failed'): ?>
            <div class="alert-error">Could not update that trip. Please check your inputs and try again.</div>
        <?php endif; ?>

        <?php if (empty($recentTrips)): ?>
            <div class="empty-state">
                <p>No upcoming trips saved yet.</p>
                <a href="plan_trip.php" class="btn btn-primary">Start Planning</a>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
    <table class="itinerary-table">
        <thead>
            <tr>
                <th>Destination & Details</th>
                <th>Travel Date</th>
                <th>Carbon Impact</th>
                <th>Sustainability</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentTrips as $trip): ?>
                <tr class="main-data-row">
                    <td class="dest-cell">
                        <span class="dest-name">
                            <?php echo htmlspecialchars((string)$trip['destination_name'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php echo getPriorityIcon($trip['sustainability_priority'] ?? null); ?>
                        </span>
                        <div class="meta-info">
                            <span>🚗 <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string)($trip['transport_mode'] ?? 'unknown'))), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="dot">•</span>
                            <span>👥 <?php echo (int)($trip['traveler_count'] ?? 1); ?> Traveler(s)</span>
                        </div>
                    </td>

                    <td class="date-cell" data-label="Travel Date">
                        <span class="date-text">
                            <?php
                                $start = safelyFormatDate($trip['travel_date'] ?? null);
                                $end = safelyFormatDate($trip['return_date'] ?? null);
                                echo htmlspecialchars($start . ' to ' . $end, ENT_QUOTES, 'UTF-8');
                            ?>
                        </span>
                    </td>

                    <td class="impact-cell" data-label="Carbon Impact">
                        <?php $co2 = (float)($trip['carbon_footprint_kg'] ?? 0); ?>
                        <div class="impact-val <?php echo $co2 > 100 ? 'impact-high' : ''; ?>">
                            <?php echo number_format($co2, 1); ?> kg
                        </div>
                        <div class="label-muted">CO₂e Net</div>
                    </td>

                    <td class="rating-cell" data-label="Sustainability">
                        <?php $score = (int)($trip['sustainability_score'] ?? 0); ?>
                        <div class="rating-badge <?php echo getScoreClass($score); ?>">
                            <?php echo $score; ?>/100
                        </div>
                    </td>

                    <td class="actions-cell" data-label="Actions">
                        <div class="actions-group">
                            <button type="button" class="btn-action btn-quick-edit"
                                    aria-label="Quick Edit Trip"
                                    data-trip-id="<?php echo htmlspecialchars($trip['trip_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-travel-date="<?php echo htmlspecialchars($trip['travel_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-return-date="<?php echo htmlspecialchars($trip['return_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-traveler-count="<?php echo (int)($trip['traveler_count'] ?? 1); ?>"
                                    data-priority="<?php echo htmlspecialchars($trip['sustainability_priority'] ?? 'carbon', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-notes="<?php echo htmlspecialchars($trip['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="btn-icon">⚡</span>
                                <span class="btn-text">Quick Edit</span>
                            </button>
                            <a href="plan_trip.php?trip_id=<?php echo htmlspecialchars($trip['trip_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-action btn-edit" aria-label="Edit Trip">
                                <span class="btn-icon">📝</span>
                                <span class="btn-text">Edit</span>
                            </a>
                            <form method="POST" action="delete_trip.php" onsubmit="return confirm('Are you sure you want to cancel this trip? This cannot be undone.');" style="margin:0;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="trip_id" value="<?php echo htmlspecialchars($trip['trip_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn-action btn-delete" aria-label="Cancel Trip">
                                    <span class="btn-icon">❌</span>
                                    <span class="btn-text">Cancel</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <?php if (!empty($trip['notes'])): ?>
                <tr class="notes-row">
                    <td colspan="5">
                        <div class="trip-notes">
                            <span class="notes-label">📌 Your Itinerary Notes:</span> "<?php echo htmlspecialchars((string)$trip['notes'], ENT_QUOTES, 'UTF-8'); ?>"
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
        <?php endif; ?>

    </section>
    </main>

    <div class="modal-overlay" id="quickEditOverlay" hidden>
        <div class="modal-box">
            <div class="modal-header">
                <h3>Quick Edit Trip</h3>
                <button type="button" class="modal-close" id="quickEditClose" aria-label="Close">&times;</button>
            </div>
            <form method="POST" action="update_trip_process.php" class="form-grid" id="quickEditForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="trip_id" id="qe_trip_id">

                <div class="full-width date-grid">
                    <div class="form-group">
                        <label for="qe_travel_date">Departure Date & Time</label>
                        <input type="datetime-local" name="travel_date" id="qe_travel_date" required>
                    </div>
                    <div class="form-group">
                        <label for="qe_return_date">Return Date & Time</label>
                        <input type="datetime-local" name="return_date" id="qe_return_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="qe_traveler_count">Number of Travelers</label>
                    <input type="number" name="traveler_count" id="qe_traveler_count" min="1" required>
                </div>

                <div class="form-group">
                    <label for="qe_priority">Sustainability Priority</label>
                    <select name="priority" id="qe_priority">
                        <option value="carbon">Lowest Carbon Footprint</option>
                        <option value="balance">Balanced (Eco & Speed)</option>
                        <option value="local">Prioritize Local Operators</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="qe_notes">Trip Specifics & Preferences</label>
                    <textarea name="notes" id="qe_notes" rows="4"></textarea>
                </div>

                <div class="form-footer full-width">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="container dashboard-footer">
            <p class="muted">&copy; <?php echo date('Y'); ?> Eco-Friendly Travel Planner by Jane 🩷</p>
            <p class="muted">CMSC 207 Project</p>
        </div>
    </footer>

    <script>
        const quickEditOverlay = document.getElementById('quickEditOverlay');
        const quickEditForm = document.getElementById('quickEditForm');

        document.querySelectorAll('.btn-quick-edit').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('qe_trip_id').value = button.dataset.tripId;
                document.getElementById('qe_travel_date').value = button.dataset.travelDate;
                document.getElementById('qe_return_date').value = button.dataset.returnDate;
                document.getElementById('qe_traveler_count').value = button.dataset.travelerCount;
                document.getElementById('qe_priority').value = button.dataset.priority;
                document.getElementById('qe_notes').value = button.dataset.notes;
                quickEditOverlay.hidden = false;
            });
        });

        document.getElementById('quickEditClose').addEventListener('click', () => {
            quickEditOverlay.hidden = true;
        });

        quickEditOverlay.addEventListener('click', (e) => {
            if (e.target === quickEditOverlay) quickEditOverlay.hidden = true;
        });

        quickEditForm.addEventListener('submit', (e) => {
            const start = document.getElementById('qe_travel_date').value;
            const end = document.getElementById('qe_return_date').value;
            if (end < start) {
                e.preventDefault();
                alert('Return date must be on or after the departure date.');
            }
        });
    </script>

</body>
</html>

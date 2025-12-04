<?php
// public/index.php (homepage - public)
session_start();


include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

// --- Fetch featured clubs (latest 6) ---
$clubs_stmt = $connection->prepare("SELECT id, name, description, logo, category FROM clubs ORDER BY id DESC LIMIT 3");
$clubs_stmt->execute();
$clubs = $clubs_stmt->get_result();

// --- Fetch upcoming events (next 6) ---
$events_stmt = $connection->prepare("SELECT e.id, e.title, e.description, e.date, e.event_time, e.event_image, c.name AS club_name, c.id AS club_id
                                     FROM events e
                                     JOIN clubs c ON e.club_id = c.id
                                     WHERE e.date >= CURDATE()
                                     ORDER BY e.date ASC, e.event_time ASC
                                     LIMIT 3");
$events_stmt->execute();
$events = $events_stmt->get_result();

// --- Fetch distinct categories (for simple filters) ---
$catRes = $connection->query("SELECT DISTINCT category FROM clubs WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$categories = $catRes ? $catRes->fetch_all(MYSQLI_ASSOC) : [];
?>

<main class="homepage-only">

  <!-- HERO -->
  <section class="section hero-section about-hero-bg">
    <div class="container home-hero">

      <div class="hero-text">
        <h1>Discover clubs, events, and student communities</h1>
        <p>Join exciting groups, improve your skills, attend events and connect with other students.</p>

        <div class="hero-actions">
          <a href="../member/view_clubs.php" class="btn btn-primary">Browse Clubs</a>
          <a href="../member/view_events.php" class="btn btn-outline">Browse Events</a>
        </div>
      </div>

      <!-- Search Card -->
      <!-- <div class="card hero-card">
        <form method="GET" action="../member/view_clubs.php">
          <label>Search clubs or events</label>
          <input type="search" name="q" class="form-control" placeholder="e.g. robotics, music, sports">

          <label class="mt-2">Category</label>
          <select name="category" class="form-control">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= htmlspecialchars($c['category']) ?>"><?= htmlspecialchars($c['category']) ?></option>
            <?php endforeach; ?>
          </select>

          <button class="btn btn-primary w-full mt-2">Search</button>
        </form>
      </div> -->

    </div>
  </section>

  <!-- FEATURED CLUBS -->
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2>Featured Clubs</h2>
        <a href="../member/view_clubs.php" class="btn btn-ghost">View All</a>
      </div>

      <div class="home-grid">
        <?php while ($club = $clubs->fetch_assoc()): ?>

          <?php
            $club_logo = "../uploads/club_images/default_club.jpg";
            if (!empty($club['logo'])) {
              if (file_exists("../uploads/club_images/" . $club['logo'])) {
                $club_logo = "../uploads/club_images/" . $club['logo'];
              } elseif (file_exists($club['logo'])) {
                $club_logo = $club['logo'];
              }
            }
          ?>

          <div class="card">
            <img src="<?= $club_logo ?>" class="card-img" alt="">
            <div class="card-body">
              <h3 class="card-header"><?= htmlspecialchars($club['name']) ?></h3>
              <p class="text-muted small"><?= htmlspecialchars(substr($club['description'], 0, 110)) ?>...</p>
              <a href="../member/club_details.php?id=<?= $club['id'] ?>" class="btn btn-outline mt-2">View</a>
            </div>
          </div>

        <?php endwhile; ?>
      </div>
    </div>
  </section>

  <!-- UPCOMING EVENTS -->
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2>Upcoming Events</h2>
        <a href="../member/view_events.php" class="btn btn-ghost">View All</a>
      </div>

      <div class="home-grid">
        <?php while ($e = $events->fetch_assoc()): ?>

          <?php
            $event_img = "../uploads/event_images/default_event.jpeg";
            if (!empty($e['event_image'])) {
              if (file_exists("../uploads/event_images/" . $e['event_image'])) {
                $event_img = "../uploads/event_images/" . $e['event_image'];
              } elseif (file_exists($e['event_image'])) {
                $event_img = $e['event_image'];
              }
            }
          ?>

          <div class="card">
            <img src="<?= $event_img ?>" class="card-img" alt="">
            <div class="card-body">
              <h3 class="card-header"><?= htmlspecialchars($e['title']) ?></h3>
              <p class="text-muted small"><?= htmlspecialchars(substr($e['description'], 0, 110)) ?>...</p>
              <a href="../member/view_events.php?id=<?= $e['id'] ?>" class="btn btn-outline mt-2">Details</a>
            </div>
          </div>

        <?php endwhile; ?>
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section class="section light-bg">
    <div class="container">
      <h2 class="text-center mb-3">Why Join a Club?</h2>

      <div class="about-grid mt-3">
        <div class="card">
          <div class="card-body">
            <h3>Learn & Build</h3>
            <p>Workshops, teamwork, hands-on projects.</p>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h3>Connections</h3>
            <p>Meet students, seniors, and leaders.</p>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h3>Showcase</h3>
            <p>Host events and build your portfolio.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="section">
    <div class="container">
      <div class="card cta-box">
        <div>
          <h3>Ready to get started?</h3>
          <p>Join clubs, register for events, and grow your student life.</p>
        </div>

        <div>
          <a href="../public/register.php" class="btn btn-primary">Create Account</a>
          <a href="../public/contact.php" class="btn btn-outline">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

</main>


<?php include '../includes/footer.php'; ?>

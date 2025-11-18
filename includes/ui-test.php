<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UI Test</title>

    <!-- IMPORTANT: Use the exact same style.css reference you use in header -->
    <link rel="stylesheet" href="../assets/css/test.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="#">🎯 <span>ClubHub</span></a>
        </div>

        <nav class="main-nav">
            <ul class="nav-list">
                <li><a class="nav-link active" href="#">Home</a></li>
                <li><a class="nav-link" href="#">Clubs</a></li>
                <li><a class="nav-link" href="#">Events</a></li>
            </ul>
        </nav>

        <div class="user-menu">
            <button class="user-btn">👤 Test User ▼</button>
        </div>
    </div>
</header>

<div class="container" style="margin-top:40px;">

    <h2>Buttons</h2>
    <button class="btn btn-primary">Primary Button</button>
    <button class="btn btn-outline">Outline Button</button>
    <button class="btn btn-ghost">Ghost Button</button>

    <h2 style="margin-top:40px;">Card</h2>
    <div class="card" style="max-width:400px;">
        <div class="card-header">Card Title</div>
        <p>This is a test card. If shadows, radius, and spacing appear — CSS is working.</p>
    </div>

    <h2 style="margin-top:40px;">Form</h2>
    <div class="card" style="max-width:400px;">
        <div class="form-group">
            <label>Email</label>
            <input type="text" class="form-control" placeholder="you@example.com">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control">
        </div>
    </div>

</div>

</body>
</html>

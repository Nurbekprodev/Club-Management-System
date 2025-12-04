<?php
session_start();
$page_title = "About Us – ClubWeave";

include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";
?>

<main class="about-page">

    <!-- HERO -->
    <section class="section hero-section about-hero-bg">
        <div class="container">
            <h1 class="hero-title">About ClubWeave</h1>
            <p class="hero-subtitle">
                Connecting students with clubs, events, and opportunities across campus.
                ClubWeave is built to strengthen student communities and simplify the way
                students explore campus life.
            </p>
        </div>
    </section>

    <!-- WHO WE ARE -->
    <section class="section light-bg">
        <div class="container">
            <h2 class="section-heading text-center">Who We Are</h2>
            <p class="about-text">
                ClubWeave is a student-focused platform created to modernize the way university
                clubs operate. We started as a small tech initiative with one goal — making club
                participation easier and more accessible for everyone.
            </p>
            <p class="about-text">
                Today, ClubWeave brings together developers, designers, and students who are
                passionate about community-building, leadership, and creating real solutions for
                campus life.
            </p>
        </div>
    </section>

    <!-- WHAT WE DO -->
    <section class="section">
        <div class="container">
            <h2 class="section-heading text-center">What We Do</h2>
            <div class="features-grid">

                <div class="feature-box">
                    <h3>Centralized Club Directory</h3>
                    <p>
                        Discover all campus clubs in one unified place. Learn about their activities,
                        categories, and how to join.
                    </p>
                </div>

                <div class="feature-box">
                    <h3>Event Management</h3>
                    <p>
                        Browse upcoming events, register instantly, and stay informed about
                        everything happening on campus.
                    </p>
                </div>

                <div class="feature-box">
                    <h3>Role-Based Dashboards</h3>
                    <p>
                        Members, Admins, and Superadmins each get tools designed specifically
                        for their roles.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- OUR VISION -->
    <section class="section light-bg">
        <div class="container">
            <h2 class="section-heading text-center">Our Vision</h2>

            <p class="vision-text">
                We imagine a connected and active university community where every student can find
                opportunities to grow, collaborate, and express themselves.
            </p>
            <p class="vision-text">
                ClubWeave bridges students, clubs, and event organizers to create a thriving campus
                experience where passion, leadership, and innovation come to life.
            </p>
        </div>
    </section>

    <!-- IMPACT STATS -->
    <section class="section">
        <div class="container">
            <h2 class="section-heading text-center">Our Impact</h2>

            <div class="impact-grid">
                <div class="impact-item">
                    <h3>50+</h3>
                    <p>Active Clubs</p>
                </div>

                <div class="impact-item">
                    <h3>100+ / Year</h3>
                    <p>Events Organized</p>
                </div>

                <div class="impact-item">
                    <h3>1000+</h3>
                    <p>Student Members</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section light-bg">
        <div class="container text-center">
            <h2 class="section-heading">Join the Community</h2>
            <p class="vision-text">
                Become part of a growing network of motivated students and active organizations.
            </p>
        </div>
    </section>

</main>

<?php include "../includes/footer.php"; ?>

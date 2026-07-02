<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>About QR3K</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <a href="encode.php" class="about-nav">&larr; Back to Encoder</a>

    <div class="about-header">
        <h1 class="about-logo">QR3K</h1>
        <div class="about-tagline">CODE // COMPACT // COMPETE</div>
    </div>

    <div class="about-hero">
        <h2>THE CHALLENGE</h2>
        <p>Build a fully playable game in <span class="about-highlight">3 kilobytes</span> or less. Small enough to fit inside a QR code. Big enough to blow minds.</p>
    </div>

    <div class="stats-row">
        <div class="stat-card stat-card--magenta">
            <div class="stat-card__value">3KB</div>
            <div class="stat-card__label">MAX SIZE</div>
        </div>
        <div class="stat-card stat-card--cyan">
            <div class="stat-card__value">&infin;</div>
            <div class="stat-card__label">CREATIVITY</div>
        </div>
        <div class="stat-card stat-card--orange">
            <div class="stat-card__value">1</div>
            <div class="stat-card__label">QR CODE</div>
        </div>
    </div>

    <div class="rule-card rule-card--cyan">
        <h3>&rarr; ANY LANGUAGE</h3>
        <p>JavaScript, Python, C, Rust, assembly. If it compiles or runs, it counts.</p>
    </div>

    <div class="rule-card rule-card--orange">
        <h3>&rarr; REAL GAMES ONLY</h3>
        <p>Interactive, playable, with win/lose conditions. No excuses.</p>
    </div>

    <div class="rule-card rule-card--magenta">
        <h3>&rarr; READABLE AS QR</h3>
        <p>Your game must scan as a valid QR code. Form meets function.</p>
    </div>

    <div class="about-cta">
        <h2>READY TO GOLF?</h2>
        <div class="about-cta__buttons">
            <a href="encode.php" class="about-cta__btn about-cta__btn--primary">SUBMIT YOUR GAME</a>
            <a href="#rules" class="about-cta__btn about-cta__btn--secondary">READ FULL RULES</a>
        </div>
    </div>

    <div class="about-footer">
        <p>QR3K is an experiment in creative constraint.<br>
        Built by <a href="https://vincentbruijn.nl">vincentbruijn.nl</a></p>
        <div class="about-footer__logo">QR3K</div>
    </div>

</body>
</html>

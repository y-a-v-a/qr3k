<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>About QR3K</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include __DIR__ . '/nav.php'; ?>

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
        <h3>&rarr; JAVASCRIPT ONLY</h3>
        <p>Pure JavaScript, or HTML with a single script tag. If the browser runs it, it counts.</p>
    </div>

    <div class="rule-card rule-card--orange">
        <h3>&rarr; REAL GAMES ONLY</h3>
        <p>Interactive, playable, with win/lose conditions. No excuses.</p>
    </div>

    <div class="rule-card rule-card--magenta">
        <h3>&rarr; READABLE AS QR</h3>
        <p>Your game must scan as a valid QR code. Form meets function.</p>
    </div>

    <div class="about-history">
        <h2>STANDING ON TINY SHOULDERS</h2>
        <p>QR3K is the latest lap in a race programmers have run for decades: how much can you do with almost nothing? Long before AI could write code by the megabyte, size limits were the sport &mdash; and squeezing a whole game into a QR code is our way of keeping that sport alive.</p>
        <ul class="history-timeline">
            <li>
                <span class="history-year">1984</span>
                <span class="history-entry">The <a href="https://www.ioccc.org/">International Obfuscated C Code Contest</a> turns writing terrible-but-tiny C into an art form, with size limits measured in bytes.</span>
            </li>
            <li>
                <span class="history-year">1990s</span>
                <span class="history-entry">The demoscene pushes audiovisual madness out of 64K &mdash; and later 4K &mdash; executables, proving that constraints breed creativity, not limit it.</span>
            </li>
            <li>
                <span class="history-year">1999</span>
                <span class="history-entry">The term <a href="https://en.wikipedia.org/wiki/Code_golf">&ldquo;golf&rdquo;</a> is coined on the Perl newsgroup comp.lang.perl.misc: solve the problem in the fewest (key)strokes, like golfers counting theirs.</span>
            </li>
            <li>
                <span class="history-year">2000</span>
                <span class="history-entry"><a href="https://the5k.org/">The 5K</a>, conceived by Stewart Butterfield (later of Flickr and Slack fame), challenges designers to build an entire self-contained website in under 5,120 bytes. It runs until 2002 and inspires everything that follows.</span>
            </li>
            <li>
                <span class="history-year">2010</span>
                <span class="history-entry"><a href="https://js1k.com/">JS1k</a> drops the budget to a brutal 1,024 bytes of JavaScript, while 10K Apart revives the 5K's spirit for the modern web.</span>
            </li>
            <li>
                <span class="history-year">2012</span>
                <span class="history-entry"><a href="https://js13kgames.com/">js13kGames</a> gives game developers a comparatively luxurious 13 kilobytes, zipped, every year &mdash; and is still running.</span>
            </li>
            <li>
                <span class="history-year">NOW</span>
                <span class="history-entry">QR3K: 2,953 bytes, the maximum capacity of a QR code. The binary is the distribution medium. Scan, play, done.</span>
            </li>
        </ul>
    </div>

    <div class="about-cta">
        <h2>READY TO GOLF?</h2>
        <div class="about-cta__buttons">
            <a href="encode.php" class="about-cta__btn about-cta__btn--primary">SUBMIT YOUR GAME</a>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>

</body>
</html>

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
    <h1>About QR3K</h1>

    <div class="hero">
        <h2>Tiny Games. Big Fun.</h2>
        <p class="hero-intro">QR3K is a creative coding challenge where developers squeeze entire playable games into the tiny space of a QR code. Think of it as extreme digital minimalism meets game development!</p>
    </div>

    <div class="section">
        <h3>What's the Big Idea?</h3>
        <p>You know those black-and-white square barcodes you scan with your phone? Those are QR codes, and they can hold up to <span class="highlight">2,953 bytes</span> of data. That's not much—roughly the same as a short email!</p>
        <p>The challenge? Fit an <strong>entire game</strong> into that space. When someone scans the QR code, boom—instant game, no downloads, no app stores, just pure playable fun right in their browser.</p>
    </div>

    <div class="fun-fact">
        💡 Fun Fact: 2,953 bytes is smaller than most images you send in a text message. Yet developers have created Snake, Pong, Tetris, and more in this tiny space!
    </div>

    <div class="section">
        <h3>How Does It Work?</h3>
        <p>Here's the magic recipe:</p>
        <p><strong>1.</strong> Write a game using HTML, CSS, and JavaScript—but keep it <em>tiny</em>. Every character counts!</p>
        <p><strong>2.</strong> Encode your game into a special URL that the QR3K runtime can understand.</p>
        <p><strong>3.</strong> Generate a QR code from that URL.</p>
        <p><strong>4.</strong> Share it! Anyone can scan and play instantly.</p>
    </div>

    <div class="section">
        <h3>What Kind of Games Can You Make?</h3>
        <div class="example-grid">
            <div class="example-card">
                <h4>🐍 Snake</h4>
                <p>The classic! Guide a growing snake around the screen without hitting walls or yourself.</p>
            </div>
            <div class="example-card">
                <h4>🏓 Pong</h4>
                <p>Retro paddle action. Simple physics, timeless gameplay.</p>
            </div>
            <div class="example-card">
                <h4>🧱 Breakout</h4>
                <p>Bounce a ball to smash bricks. Satisfying and addictive!</p>
            </div>
            <div class="example-card">
                <h4>🎮 Your Idea!</h4>
                <p>The only limit is your creativity (and 2,953 bytes).</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Why Is This Cool?</h3>
        <p><strong>Zero Friction:</strong> No app stores, no downloads, no accounts. Scan and play. That's it.</p>
        <p><strong>Creative Constraints:</strong> Limited space forces you to think differently. Every byte matters. It's like writing poetry with code.</p>
        <p><strong>Shareable:</strong> Print your QR code on stickers, posters, business cards—anywhere! Your game becomes a physical artifact.</p>
        <p><strong>Universal:</strong> Works on any device with a camera and browser. iOS, Android, desktop—everyone can play.</p>
    </div>

    <div class="fun-fact">
        🎨 Pro Tip: The best QR3K games embrace simplicity. Think Atari 2600 vibes, not PlayStation 5.
    </div>

    <div class="section">
        <h3>Who's This For?</h3>
        <p>Anyone who loves creative challenges! Whether you're a seasoned developer looking for a fun constraint, a student learning to code, or just someone who thinks "can I fit a game in a QR code?" sounds like a cool weekend project—QR3K is for you.</p>
        <p>It's also perfect for game jams, coding workshops, hackathons, or just showing off your skills in the most compact way possible.</p>
    </div>

    <div class="section">
        <h3>Ready to Try?</h3>
        <p>Making a QR3K game is easier than you think. Start with simple graphics, basic controls, and a clear goal. The encoder handles the tricky parts—you just focus on making something fun!</p>
        <a href="encode.php" class="cta-button">Start Encoding</a>
        <a href="index.php" class="cta-button">Play Games</a>
    </div>

    <div class="footer-text">
        <p>QR3K: Where Less Is Definitely More</p>
    </div>

</body>
</html>

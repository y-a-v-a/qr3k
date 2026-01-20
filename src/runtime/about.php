<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>About QR3K</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hero {
            margin: 40px 0;
            padding: 30px;
            background: var(--bg-card);
            border: 4px solid var(--yellow);
            box-shadow: 10px 10px 0 var(--magenta);
            max-width: 900px;
        }
        .hero h2 {
            font-size: 32px;
            font-weight: 900;
            color: var(--blue);
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }
        .section {
            margin: 30px 0;
            padding: 25px;
            background: var(--bg-card);
            border: 4px solid var(--blue);
            box-shadow: 8px 8px 0 var(--yellow);
            max-width: 900px;
        }
        .section h3 {
            font-size: 24px;
            font-weight: 900;
            color: var(--magenta);
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }
        .section p {
            margin: 15px 0;
            font-size: 16px;
            line-height: 1.8;
        }
        .highlight {
            background: var(--yellow);
            color: var(--text-dark);
            padding: 2px 8px;
            font-weight: 700;
            border: 2px solid var(--text-dark);
        }
        .example-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .example-card {
            padding: 20px;
            background: var(--bg-dark);
            border: 3px solid var(--magenta);
            box-shadow: 4px 4px 0 var(--blue);
        }
        .example-card h4 {
            color: var(--yellow);
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 16px;
        }
        .fun-fact {
            margin: 30px 0;
            padding: 20px 25px;
            background: var(--magenta);
            color: var(--text-light);
            border: 4px solid var(--text-dark);
            box-shadow: 6px 6px 0 var(--blue);
            max-width: 900px;
            font-weight: 600;
            font-size: 18px;
        }
        .cta-button {
            display: inline-block;
            background: var(--yellow);
            color: var(--text-dark);
            border: 4px solid var(--text-dark);
            padding: 18px 36px;
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            text-decoration: none;
            margin: 20px 10px 10px 0;
            box-shadow: 6px 6px 0 var(--magenta);
            transition: all 0.1s ease;
        }
        .cta-button:hover {
            background: var(--blue);
            box-shadow: 6px 6px 0 var(--yellow);
            transform: translate(-2px, -2px);
        }
        .cta-button:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0 var(--magenta);
        }
    </style>
</head>
<body>
    <h1>About QR3K</h1>

    <div class="hero">
        <h2>Tiny Games. Big Fun.</h2>
        <p style="font-size: 18px;">QR3K is a creative coding challenge where developers squeeze entire playable games into the tiny space of a QR code. Think of it as extreme digital minimalism meets game development!</p>
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

    <div style="margin-top: 60px; padding: 20px; text-align: center; color: #666; font-size: 14px;">
        <p>QR3K: Where Less Is Definitely More</p>
    </div>

</body>
</html>

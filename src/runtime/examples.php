<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Include the encoder class. The library lives one level above the web root
// in the Docker image, or inside it when deployed via plain file copy.
$encoderPaths = [
    __DIR__ . '/../encoding/php/Encoder.php',
    __DIR__ . '/encoding/php/Encoder.php',
];
foreach ($encoderPaths as $encoderPath) {
    if (is_file($encoderPath)) {
        require_once $encoderPath;
        break;
    }
}

// Example game sources follow the same dual-location convention.
$gamesDirs = [
    __DIR__ . '/../games',
    __DIR__ . '/games',
];

$examples = [
    [
        'file' => 'snake-3310.js',
        'title' => 'Snake 3310',
        'accent' => 'cyan',
        'blurb' => 'The game every Nokia 3310 owner played under the school desk. Wrap around the edges Snake II style, eat, grow — just don\'t bite yourself.',
        'controls' => 'Arrow keys',
    ],
    [
        'file' => 'wall-pong.js',
        'title' => 'Wall Pong',
        'accent' => 'magenta',
        'blurb' => 'Pong against an opponent that never misses: the wall. Every return speeds the ball up and adds spin. How long can you keep the rally alive?',
        'controls' => 'Up/Down arrows, mouse or touch',
    ],
    [
        'file' => 'lander.js',
        'title' => 'Lunar Lander',
        'accent' => 'yellow',
        'blurb' => 'Gravity is free, fuel is not. Drop onto procedurally generated terrain and touch down gently on the green pad — level, slow, and preferably in one piece.',
        'controls' => 'Up/Space = thrust, Left/Right = side thrusters',
    ],
];

/**
 * Strip full-line // comments and blank lines before encoding: the source in
 * the repo stays readable while the QR code carries only what the game needs.
 */
function qr3kLeanCode($code) {
    $lean = preg_replace('~^\h*//.*$~m', '', $code);
    $lean = preg_replace("~\n{2,}~", "\n", $lean);
    return trim($lean);
}

$cards = [];
foreach ($examples as $example) {
    $code = null;
    foreach ($gamesDirs as $gamesDir) {
        $path = $gamesDir . '/' . $example['file'];
        if (is_file($path)) {
            $code = file_get_contents($path);
            break;
        }
    }

    if ($code === false || $code === null || !class_exists('QR3KEncoder')) {
        $example['error'] = 'This example could not be loaded right now.';
        $cards[] = $example;
        continue;
    }

    try {
        $result = QR3KEncoder::encode(qr3kLeanCode($code));
    } catch (Throwable $e) {
        error_log('QR3K examples encoding error: ' . $e->getMessage());
        $example['error'] = 'This example could not be encoded right now.';
        $cards[] = $example;
        continue;
    }

    $example['code'] = $code;
    $example['result'] = $result;
    // Relative play link so the example runs on localhost and production alike
    $example['playUrl'] = 'index.php?z=' . urlencode($result['encoded']);
    $cards[] = $example;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>QR3K Examples</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/nav.php'; ?>

    <h1>QR3K Examples</h1>
    <p>Three complete games, each small enough to live inside a single QR code. Scan one with your phone — the code below <em>is</em> the game, no download, no app store. Or hit play and try it right here.</p>

    <div class="examples-grid">
        <?php foreach ($cards as $card): ?>
            <div class="example-card example-card--<?php echo $card['accent']; ?>">
                <h2><?php echo htmlspecialchars($card['title']); ?></h2>
                <p class="example-card__blurb"><?php echo $card['blurb']; ?></p>

                <?php if (isset($card['error'])): ?>
                    <div class="example-card__error"><?php echo htmlspecialchars($card['error']); ?></div>
                <?php else: ?>
                    <?php $size = $card['result']['size']; ?>
                    <img class="example-card__qr"
                         src="<?php echo htmlspecialchars($card['result']['qrUrl']); ?>"
                         alt="QR code containing the full <?php echo htmlspecialchars($card['title']); ?> game"
                         loading="lazy">

                    <div class="example-card__meta">
                        <div><strong>Controls:</strong> <?php echo htmlspecialchars($card['controls']); ?></div>
                        <div><strong>QR payload:</strong> <?php echo number_format($size['total']); ?> / <?php echo number_format($size['limit']); ?> bytes
                            (<?php echo round($size['total'] / $size['limit'] * 100); ?>% of budget)</div>
                    </div>

                    <a class="example-card__btn" href="<?php echo htmlspecialchars($card['playUrl']); ?>">Play now</a>

                    <details class="example-card__source">
                        <summary>View source (<?php echo number_format(strlen($card['code'])); ?> bytes)</summary>
                        <pre><?php echo htmlspecialchars($card['code']); ?></pre>
                    </details>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p>Want your own game in a QR code? Head to the <a href="encode.php">encoder</a> and start golfing.</p>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>

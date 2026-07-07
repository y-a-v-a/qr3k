<?php
// Shared site navigation. Include on every page; the current page is
// rendered as a non-link so users always know where they are.
$navCurrent = basename($_SERVER['SCRIPT_NAME']);
$navPages = [
    'index.php' => 'Play',
    'encode.php' => 'Encoder',
    'examples.php' => 'Examples',
    'about.php' => 'About',
];
?>
<nav class="site-nav">
    <span class="site-nav__brand">QR3K</span>
    <?php foreach ($navPages as $navFile => $navLabel): ?>
        <?php if ($navFile === $navCurrent): ?>
            <span class="site-nav__link site-nav__link--active"><?php echo $navLabel; ?></span>
        <?php else: ?>
            <a class="site-nav__link" href="<?php echo $navFile; ?>"><?php echo $navLabel; ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

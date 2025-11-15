<?php
$navItems = [
    ['href' => '#home', 'label' => 'Home'],
    ['href' => '#about', 'label' => 'Over'],
    ['href' => '#services', 'label' => 'Diensten'],
    ['href' => '#contact', 'label' => 'Contact']
];

echo '<header>
        <nav>
            <ul>';
foreach ($navItems as $item) {
    echo '<li><a href="' . $item['href'] . '">' . $item['label'] . '</a></li>';
}
echo '      </ul>
            <a href="login.php" class="login-btn">Login</a>
        </nav>
      </header>';
?>
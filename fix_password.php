<?php
require_once 'config/database.php';

$hash = password_hash('224589herU!', PASSWORD_DEFAULT);
echo "New hash: $hash\n";

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$hash, 'herurizkyfajar@gmail.com']);
echo "Updated.\n";

// Verify
$stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
$stmt->execute(['herurizkyfajar@gmail.com']);
$dbHash = $stmt->fetchColumn();
echo "DB hash: $dbHash\n";
echo "Verify: " . (password_verify('224589herU!', $dbHash) ? 'MATCH' : 'NO MATCH') . "\n";
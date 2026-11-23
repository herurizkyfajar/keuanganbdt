<?php
$hash = '$2y$12$E0jIIIjOrcYiFHrdm3bsY.EH0jlF2gb64uu9voS6fNhPJ0DVlP0Ja';
echo password_verify('224589herU!', $hash) ? 'MATCH' : 'NO MATCH';
echo PHP_EOL;

// Also check what's in the DB
$pdo = new PDO('mysql:host=localhost;dbname=managerio2;charset=utf8mb4', 'root', '');
$stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
$stmt->execute(['herurizkyfajar@gmail.com']);
$dbHash = $stmt->fetchColumn();
echo "DB hash: " . substr($dbHash, 0, 30) . "...\n";
echo "Verify: " . (password_verify('224589herU!', $dbHash) ? 'MATCH' : 'NO MATCH') . "\n";
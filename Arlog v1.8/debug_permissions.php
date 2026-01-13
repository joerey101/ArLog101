<?php
require 'db.php';
session_start();

$pid = 19; // The problematic New postulation
echo "<h1>Debug Postulation $pid</h1>";

// 1. Get Postulation & Ad Info
$sql = "
    SELECT p.id as pid, p.estado, p.anuncio_id, 
           a.id as aid, a.titulo, a.usuario_id as owner_id
    FROM postulaciones p
    JOIN anuncios a ON p.anuncio_id = a.id
    WHERE p.id = $pid
";
$stmt = $pdo->query($sql);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($row);
echo "</pre>";

// 2. Check Session
echo "<h3>Current Session</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not Set') . "<br>";
echo "Role: " . ($_SESSION['user_rol'] ?? 'Not Set') . "<br>";

// 3. Match?
if (isset($_SESSION['user_id']) && $row) {
    if ($_SESSION['user_id'] == $row['owner_id']) {
        echo "<h2 style='color:green'>MATCH: User owns checking ad. Update should work.</h2>";
    } else {
        echo "<h2 style='color:red'>MISMATCH: User {$_SESSION['user_id']} != Owner {$row['owner_id']}</h2>";
        echo "<p>This is why 'Auto-Seen' fails.</p>";
    }
}

// 4. Force Update for testing UI
if (isset($_GET['force_seen'])) {
    $pdo->query("UPDATE postulaciones SET estado = 'visto' WHERE id = $pid");
    echo "<h3> Forced Update to 'visto'. Check Dashboard now.</h3>";
}
?>
<a href="?force_seen=1">Force 'Visto' on ID 19</a>
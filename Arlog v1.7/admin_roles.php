<?php
require 'db.php';

// Simple Admin Tool to Switch Roles
$action = $_GET['action'] ?? 'list';
$email = $_GET['email'] ?? '';
$role = $_GET['role'] ?? 'empresa';

echo "<h1>🛠️ ArLog Admin Tool</h1>";

if ($action === 'switch' && !empty($email)) {
    $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE email = ?");
    if ($stmt->execute([$role, $email])) {
        echo "<p style='color:green'>✅ Rol de <b>$email</b> cambiado a <b>$role</b>.</p>";
    } else {
        echo "<p style='color:red'>❌ Error al cambiar rol.</p>";
    }
}

// List Users
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>";

$users = $pdo->query("SELECT id, email, rol FROM usuarios")->fetchAll();

foreach ($users as $u) {
    echo "<tr>";
    echo "<td>" . $u['id'] . "</td>";
    echo "<td>" . $u['email'] . "</td>";
    // DEBUG: Ver longitud y espacios ocultos
    echo "<td><b>'" . $u['rol'] . "' (" . strlen($u['rol']) . ")</b></td>";
    echo "<td>
            <a href='?action=switch&email=" . $u['email'] . "&role=empresa'>[Hacer Empresa]</a> 
            <a href='?action=switch&email=" . $u['email'] . "&role=candidato'>[Hacer Candidato]</a>
          </td>";
    echo "</tr>";
}
echo "</table>";
?>
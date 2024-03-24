<link rel="stylesheet" href="css/manageUsers.css">

<?php

// Presupunând că ai o funcție `connectDBwithPDO` care returnează un obiect PDO.
require '../db/connectDB.php';
$conn = connectDBwithPDO('mobiromr');

$query = "SELECT id, username, permission_level FROM user_accounts";
$stmt = $conn->prepare($query);
$stmt->execute();

echo '<div class="users-list">';

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<div class="user-card">';
    echo '<div class="user-info">';
    echo '<div class="user-id">ID: ' . htmlspecialchars($row['id']) . '</div>';
    echo '<div class="username">Username: ' . htmlspecialchars($row['username']) . '</div>';
    echo '<div class="permission-level">Permission Level: <span class="permission-level-display">' . htmlspecialchars($row['permission_level']) . '</span>';
   
   
    echo '<select class="permission-level-edit" style="display:none;">';
    // Presupunând că ai 3 niveluri de permisiune
    $permissionLevels = [
        1 => 'admin',
        2 => 'user',
        3 => 'guest'
    ];
    foreach ($permissionLevels as $level => $label) {
        $selected = ($label == $row['permission_level']) ? 'selected' : '';
        echo "<option value='$level' $selected>$label</option>";
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '<div class="user-actions">';
    echo "<button class='delete-user' data-user-id='" . $row['id'] . "'>Șterge</button>";
    echo "<button class='edit-permission' data-user-id='" . $row['id'] . "'>Editează Permisiune</button>";
    echo "<button class='save-permission' data-user-id='" . $row['id'] . "' style='display:none;'>Salvează</button>";
    echo '</div>';
    echo '</div>';
}

echo '</div>';
?>

<?php

session_start();
// Verifică dacă user_id NU este setat în sesiune
if (!isset($_SESSION['user_id'])) {
    // Redirecționează către pagina de login
    header("Location: ../login");
    exit;
} else {
    // Dacă user_id este setat, atunci verifică nivelul de permisiune
    if($_SESSION['permission_level'] != "admin"){
        // Dacă utilizatorul nu este admin, redirecționează (sau alte acțiuni necesare)
        header("Location: ../login");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
        </header>
        <nav class="admin-nav">
            <ul>
                <li><a href="add_user_form.php">Adăugați Utilizator</a></li>
                <li><a href="manage_users.php">Gestionați Utilizatori</a></li>
                <li><a href="site_settings.php">Setări</a></li>
                <li><a href="logout.php">Deconectare</a></li>
            </ul>
        </nav>
        <main class="admin-main">
            <p>Bine ați venit în panoul de administrare al site-ului.</p>
            <!-- Conținutul principal va fi aici -->
        </main>
        <footer class="admin-footer">
            <p>&copy; 2024 Site Name - Toate drepturile rezervate.</p>
        </footer>
    </div>
</body>
</html>

<script>



  document.addEventListener('DOMContentLoaded', function() {
    attachFormSubmitListener();

    document.querySelectorAll('.admin-nav a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (this.href.includes('logout.php')) {
                // Dacă este pentru logout, permite acțiunea implicită (nu aplica preventDefault)
                return;
                }
            e.preventDefault();
            var url = this.href;
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.querySelector('.admin-main').innerHTML = html;
                });
        });
    });
});

function attachFormSubmitListener() {


    const adminMain = document.querySelector('.admin-main');

    // Delegarea evenimentelor pentru a evita reatașarea
    adminMain.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-user')) {
            handleDeleteUser(e);
        } else if (e.target.classList.contains('edit-permission')) {
            handleEditPermission(e);
        } else if (e.target.classList.contains('save-permission')) {
            handleSavePermission(e);
        }
    });

    adminMain.addEventListener('submit', function(e) {
        if (e.target.id === 'addUserForm') {
            handleAddUserFormSubmit(e);
        }
    });
}

function handleDeleteUser(e) {
    if (e.target.dataset.isProcessing) return;
        e.target.dataset.isProcessing = true;

        const userId = e.target.dataset.userId;
        if (confirm('Ești sigur că vrei să ștergi acest utilizator?')) {
            const formData = new FormData();
            formData.append('userId', userId);

            fetch('delete_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                alert(text);
                // Înlătură cardul utilizatorului din DOM
                e.target.closest('.user-card').remove();
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                // Permite din nou procesarea după finalizarea solicitării
                delete e.target.dataset.isProcessing;
            });
        } else {
            console.log('Operațiunea de ștergere a fost anulată.');
            delete e.target.dataset.isProcessing;
        }
    }

function handleEditPermission(e) {
    var button = e.target;
    var userId = button.getAttribute('data-user-id');
    var userCard = button.closest('.user-card');
    userCard.querySelector('.permission-level-display').style.display = 'none';
    userCard.querySelector('.permission-level-edit').style.display = '';
    button.style.display = 'none';
    userCard.querySelector('.save-permission').style.display = '';
}
function handleSavePermission(e) {
    // Folosește e.target pentru a identifica elementul care a declanșat evenimentul și .closest pentru a găsi butonul specific
    var button = e.target.closest('.save-permission');
    if (!button) return; // Asigură-te că am găsit butonul corect

    var userId = button.getAttribute('data-user-id');
    console.log('Saving permissions for user with ID:', userId);

    var userCard = button.closest('.user-card');
    var selectElement = userCard.querySelector('.permission-level-edit');
    var newPermissionLevel = selectElement.value;
    var selectedOption = selectElement.options[selectElement.selectedIndex];
    var newPermissionLevelLabel = selectedOption.text;

    fetch('update_permission.php', {
        method: 'POST',
        body: JSON.stringify({ userId: userId, permissionLevel: newPermissionLevel }),
        headers: { 'Content-Type': 'application/json' },
    })
    .then(response => response.json())
    .then(data => {
        console.log(data.message);
        if (data.success) {
            userCard.querySelector('.permission-level-display').textContent = newPermissionLevelLabel;
            userCard.querySelector('.permission-level-display').style.display = '';
            userCard.querySelector('.permission-level-edit').style.display = 'none';
            userCard.querySelector('.edit-permission').style.display = '';
            userCard.querySelector('.save-permission').style.display = 'none';
        } else {
            // Gestionează cazul de eșec
        }
    })
    .catch(error => console.error('Error:', error));
}


function handleAddUserFormSubmit(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
            // Pentru debug: Loghează valorile din formData
            for (var pair of formData.entries()) {
                console.log(pair[0]+ ', ' + pair[1]); 
            }

            fetch('add_user.php', {
                method: 'POST',
                body: formData,
                // Headers implicit setate pentru FormData, deci nu este necesar să adăugăm aici
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                document.querySelector('.admin-main').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
            });
            console.log('Formularul a fost trimis!');
}




</script>
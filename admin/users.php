<?php
include '../includes/db.php';
include '../includes/admin_navbar.php';
include '../auth_checks/admin_auth.php';

$statusMsg = '';
$statusColor = '';

if (isset($_GET['toggle'])) {
    $user_id = $_GET['toggle'];
    $currentStatus = $_GET['is_banned'];
    $newStatus = $currentStatus == 1 ? 0 : 1;
    $conn->query("UPDATE users SET is_banned = $newStatus WHERE id = $user_id");
    $statusMsg = $newStatus ? "🚫 User restricted!" : "✅ User Allowed access!";
    $statusColor = $newStatus ? 'red' : 'green';
}

$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Users - Admin</title>
    <link rel="stylesheet" href="/booksite/assets/css/user_table.css">
    <style>
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .search-bar-wrapper {
        max-width: 300px;
    }

    .search-bar-wrapper input {
        width: 100%;
        padding: 8px 14px 8px 38px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-image: url('https://upload.wikimedia.org/wikipedia/commons/5/55/Magnifying_glass_icon.svg');
        background-size: 18px;
        background-position: 12px center;
        background-repeat: no-repeat;
    }

    .highlight {
        background-color: rgba(255, 255, 0, 0.4);
        border-radius: 4px;
        padding: 0 0.25px;
    }

    .no-users-message {
        text-align: center;
        color: #777;
        margin-top: 20px;
        display: none;
    }
    </style>
</head>

<body>

    <?php if ($statusMsg): ?>
    <div class="status-toast" style="color: <?= $statusColor ?>;">
        <?= $statusMsg ?>
    </div>
    <?php endif; ?>

    <div class="table-wrapper">

        <div class="header-row">
            <h2 class="table-heading">👥 All Users</h2>
            <div class="search-bar-wrapper">
                <input type="text" id="userSearch" placeholder="Search username or email...">
            </div>
        </div>

        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php while ($u = $users->fetch_assoc()):
            $isBanned = $u['is_banned'];
            $hoverClass = $isBanned ? 'unban-hover' : 'ban-hover';
        ?>
                <tr class="user-row">
                    <td><?= $u['id'] ?></td>
                    <td class="user-username" data-value="<?= strtolower($u['username']) ?>">
                        <?= htmlspecialchars($u['username']) ?>
                    </td>
                    <td class="user-email" data-value="<?= strtolower($u['email']) ?>">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td><?= $isBanned ? '❌ Restricted' : '✅ Allowed' ?></td>
                    <td class="action-cell <?= $hoverClass ?>">
                        <?php if ($u['role'] == 0): ?>
                        <span class="admin-label">👑 Admin</span>
                        <?php else: ?>
                        <a href="?toggle=<?= $u['id'] ?>&is_banned=<?= $isBanned ?>"
                            onclick="return confirm('Are you sure?')" class="action-link">
                            <?= $isBanned ? '✅ Allow' : '🚫 Restrict' ?>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="no-users-message">No users match your search.</div>
    </div>

    <script>
    // LIVE PREFIX FILTER by username or email
    const input = document.getElementById('userSearch');
    const rows = document.querySelectorAll('.user-row');
    const noMatch = document.querySelector('.no-users-message');

    function highlight(text, keyword) {
        if (!keyword) return text;
        const regex = new RegExp(`(${keyword})`, 'ig');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const usernameCell = row.querySelector('.user-username');
            const emailCell = row.querySelector('.user-email');
            const username = usernameCell.getAttribute('data-value');
            const email = emailCell.getAttribute('data-value');

            if (query === '' || username.startsWith(query) || email.startsWith(query)) {
                row.style.display = '';
                usernameCell.innerHTML = highlight(usernameCell.textContent, query);
                emailCell.innerHTML = highlight(emailCell.textContent, query);
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // ❗️If no results, show all instead
        if (visibleCount === 0) {
            rows.forEach(row => {
                const usernameCell = row.querySelector('.user-username');
                const emailCell = row.querySelector('.user-email');
                row.style.display = '';
                usernameCell.innerHTML = usernameCell.textContent;
                emailCell.innerHTML = emailCell.textContent;
            });
        }

        noMatch.style.display = 'none'; // always hidden since fallback triggers
    });
    </script>

</body>

</html>
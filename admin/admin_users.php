<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fifteen Puzzle</title>
    <link rel="stylesheet" href="../css/admin.css">
  </head>
  
  <body>
    <div class="admin-section">
    <h2>User Management</h2>
    
    <div class="user-table-container">
        <table class="user-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Registered</th>
              <th>Last Login</th>
              <th>Actions</th> <!-- Removed "Status" column -->
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['user_id']) ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['role']) ?></td>
              <td><?= date('M j, Y', strtotime($user['registration_date'])) ?></td>
              <td><?= $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never' ?></td>
              <td class="actions">
                <button class="btn-edit" 
                  onclick="openEditModal(
                    <?= $user['user_id'] ?>, 
                    '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>', 
                    '<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>', 
                    '<?= htmlspecialchars($user['role']) ?>'
                  )">
                  Edit
                </button>
                <button class="btn-reset" onclick="resetPassword(<?= $user['user_id'] ?>)">
                  Reset Password
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>  
      </div>
    </div>
  </body>
</html>
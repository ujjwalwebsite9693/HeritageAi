<?php
// app/views/admin_users.php
$pageTitle = "User & Permission Management";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 30px; color: var(--primary);">User Roles & Permissions Administration</h1>
    <p style="color: var(--text-muted); font-size: 14px;">
        Manage access tiers across the platform: Visitor, Registered User, Researcher/Editor, and Administrator.
    </p>
</div>

<div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-subtle); text-align: left; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">
                <th style="padding: 10px;">ID</th>
                <th style="padding: 10px;">User Name</th>
                <th style="padding: 10px;">Email</th>
                <th style="padding: 10px;">Institution</th>
                <th style="padding: 10px;">Current Role</th>
                <th style="padding: 10px;">Change Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr style="border-bottom: 1px solid var(--border-subtle);">
                    <td style="padding: 12px 10px;">#<?= $u['id'] ?></td>
                    <td style="padding: 12px 10px; font-weight: 600;"><?= htmlspecialchars($u['name']) ?></td>
                    <td style="padding: 12px 10px;"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="padding: 12px 10px; color: var(--text-muted);"><?= htmlspecialchars($u['institution'] ?: 'Independent') ?></td>
                    <td style="padding: 12px 10px;">
                        <span class="badge <?= $u['role'] === 'admin' ? 'badge-verified' : ($u['role'] === 'researcher' ? 'badge-corrected' : 'badge-draft') ?>">
                            <?= strtoupper($u['role']) ?>
                        </span>
                    </td>
                    <td style="padding: 12px 10px;">
                        <form action="/admin/users" method="POST" style="display: flex; gap: 6px; align-items: center;">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role" class="form-control" style="padding: 4px 8px; font-size: 12px; width: 130px;">
                                <option value="visitor" <?= $u['role'] === 'visitor' ? 'selected' : '' ?>>Visitor</option>
                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="researcher" <?= $u['role'] === 'researcher' ? 'selected' : '' ?>>Researcher</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 11px;">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>


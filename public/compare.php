<?php
// public/compare.php - Historical Comparison Matrix (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();

$allPersons = $db->query("SELECT id, name, alt_names, birth_year, death_year, role, bio, portrait_url FROM persons ORDER BY name ASC")->fetchAll();
$allMonuments = $db->query("SELECT id, name, built_by, built_year, built_year_text, location_text, architectural_style, image_url, summary FROM monuments ORDER BY name ASC")->fetchAll();

$mode = $_GET['mode'] ?? 'person'; // 'person' or 'monument'
$id1 = isset($_GET['p1']) ? (int)$_GET['p1'] : ($mode === 'person' ? 6 : 1); // Default Akbar (6) or Taj Mahal (1)
$id2 = isset($_GET['p2']) ? (int)$_GET['p2'] : ($mode === 'person' ? 1 : 2); // Default Ashoka (1) or Red Fort (2)

if ($mode === 'person') {
    $stmt1 = $db->prepare("SELECT * FROM persons WHERE id = ?");
    $stmt1->execute([$id1]);
    $item1 = $stmt1->fetch();

    $stmt2 = $db->prepare("SELECT * FROM persons WHERE id = ?");
    $stmt2->execute([$id2]);
    $item2 = $stmt2->fetch();

    // Fetch monuments built by person 1 & 2
    $mons1 = [];
    $mons2 = [];
    if ($item1) {
        $stmtM1 = $db->prepare("SELECT * FROM monuments WHERE built_by LIKE ?");
        $stmtM1->execute(['%' . explode(' ', $item1['name'])[0] . '%']);
        $mons1 = $stmtM1->fetchAll();
    }
    if ($item2) {
        $stmtM2 = $db->prepare("SELECT * FROM monuments WHERE built_by LIKE ?");
        $stmtM2->execute(['%' . explode(' ', $item2['name'])[0] . '%']);
        $mons2 = $stmtM2->fetchAll();
    }
} else {
    $stmt1 = $db->prepare("SELECT * FROM monuments WHERE id = ?");
    $stmt1->execute([$id1]);
    $item1 = $stmt1->fetch();

    $stmt2 = $db->prepare("SELECT * FROM monuments WHERE id = ?");
    $stmt2->execute([$id2]);
    $item2 = $stmt2->fetch();
}

$pageTitle = "Historical Comparison Matrix · ऐतिहासिक तुलना";
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 1100px; margin: 0 auto;">
    <!-- Hero Header -->
    <div style="text-align: center; margin-bottom: 28px;">
        <div class="hero-pill">
            ⚖️ Comparative Historical Analytics · ऐतिहासिक तुलना
        </div>
        <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 8px;">
            Historical Comparison Matrix
        </h1>
        <p style="color: var(--text-muted); font-size: 15px; max-width: 720px; margin: 0 auto;">
            Compare India's legendary rulers, strategists, and architectural wonders side-by-side. Analyze dynasties, philosophies, architectural legacy, and governing reforms.
        </p>
    </div>

    <!-- Mode Selector & Quick Presets -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 20px 24px; box-shadow: var(--shadow-sm); margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; gap: 10px;">
                <a href="/compare.php?mode=person&p1=6&p2=1" class="btn <?= $mode === 'person' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 13.5px; padding: 8px 18px;">
                    👑 Compare Emperors & Thinkers
                </a>
                <a href="/compare.php?mode=monument&p1=1&p2=2" class="btn <?= $mode === 'monument' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 13.5px; padding: 8px 18px;">
                    🏰 Compare Monuments
                </a>
            </div>

            <!-- Quick Presets -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <span style="font-size: 12px; font-weight: 700; color: var(--text-muted);">Quick Presets:</span>
                <?php if ($mode === 'person'): ?>
                    <a href="/compare.php?mode=person&p1=6&p2=1" class="preset-pill">Akbar vs Ashoka</a>
                    <a href="/compare.php?mode=person&p1=10&p2=12" class="preset-pill">Shah Jahan vs Babur</a>
                    <a href="/compare.php?mode=person&p1=2&p2=7" class="preset-pill">Chanakya vs Todar Mal</a>
                <?php else: ?>
                    <a href="/compare.php?mode=monument&p1=1&p2=2" class="preset-pill">Taj Mahal vs Red Fort</a>
                    <a href="/compare.php?mode=monument&p1=3&p2=1" class="preset-pill">Humayun's Tomb vs Taj Mahal</a>
                    <a href="/compare.php?mode=monument&p1=5&p2=4" class="preset-pill">Nalanda vs Fatehpur Sikri</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dropdowns Form -->
        <form method="GET" action="/compare.php" style="margin-top: 18px; display: grid; grid-template-columns: 1fr auto 1fr auto; gap: 14px; align-items: center;">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
            
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <?= $mode === 'person' ? 'Select First Figure' : 'Select First Monument' ?>
                </label>
                <select name="p1" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); font-size: 14px; background: #fff;">
                    <?php if ($mode === 'person'): ?>
                        <?php foreach ($allPersons as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $id1 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['role']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($allMonuments as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $m['id'] == $id1 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['location_text']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div style="font-size: 22px; font-weight: 800; color: var(--accent-gold); padding-top: 18px;">
                ⚡ VS ⚡
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <?= $mode === 'person' ? 'Select Second Figure' : 'Select Second Monument' ?>
                </label>
                <select name="p2" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); font-size: 14px; background: #fff;">
                    <?php if ($mode === 'person'): ?>
                        <?php foreach ($allPersons as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $id2 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['role']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($allMonuments as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $m['id'] == $id2 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['location_text']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div style="padding-top: 18px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                    Compare Now ➔
                </button>
            </div>
        </form>
    </div>

    <?php if (!$item1 || !$item2): ?>
        <div class="flash-alert flash-error">Selected items not found in database.</div>
    <?php elseif ($mode === 'person'): ?>
        <!-- PERSON VS PERSON COMPARISON GRID -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
            
            <!-- CARD 1 -->
            <div class="compare-card">
                <div style="text-align: center; margin-bottom: 16px;">
                    <img src="<?= htmlspecialchars($item1['portrait_url']) ?>" alt="<?= htmlspecialchars($item1['name']) ?>" class="compare-avatar">
                    <h2 style="font-size: 22px; margin-top: 12px; margin-bottom: 4px;"><?= htmlspecialchars($item1['name']) ?></h2>
                    <div class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 12px;">
                        <?= htmlspecialchars($item1['role']) ?>
                    </div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Era & Lifespan</div>
                    <div class="compare-val">
                        <?= $item1['birth_year'] < 0 ? abs($item1['birth_year']) . ' BCE' : $item1['birth_year'] . ' CE' ?> – 
                        <?= $item1['death_year'] < 0 ? abs($item1['death_year']) . ' BCE' : $item1['death_year'] . ' CE' ?>
                    </div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Historical Titles / Aliases</div>
                    <div class="compare-val"><?= htmlspecialchars($item1['alt_names'] ?: 'None recorded') ?></div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Associated Monuments</div>
                    <div class="compare-val">
                        <?php if (!empty($mons1)): ?>
                            <ul style="padding-left: 18px; margin: 0;">
                                <?php foreach ($mons1 as $m): ?>
                                    <li><a href="/monument.php?id=<?= $m['id'] ?>"><strong><?= htmlspecialchars($m['name']) ?></strong></a> (<?= htmlspecialchars($m['location_text']) ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span style="color: var(--text-muted);">None directly attributed in register</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Historical Biography</div>
                    <div class="compare-val" style="font-size: 13.5px; line-height: 1.6;">
                        <?= htmlspecialchars($item1['bio']) ?>
                    </div>
                </div>

                <div style="margin-top: 18px; text-align: center;">
                    <a href="/person.php?id=<?= $item1['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                        View Full Dossier of <?= htmlspecialchars($item1['name']) ?> ➔
                    </a>
                </div>
            </div>

            <!-- CARD 2 -->
            <div class="compare-card">
                <div style="text-align: center; margin-bottom: 16px;">
                    <img src="<?= htmlspecialchars($item2['portrait_url']) ?>" alt="<?= htmlspecialchars($item2['name']) ?>" class="compare-avatar">
                    <h2 style="font-size: 22px; margin-top: 12px; margin-bottom: 4px;"><?= htmlspecialchars($item2['name']) ?></h2>
                    <div class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold); font-size: 12px;">
                        <?= htmlspecialchars($item2['role']) ?>
                    </div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Era & Lifespan</div>
                    <div class="compare-val">
                        <?= $item2['birth_year'] < 0 ? abs($item2['birth_year']) . ' BCE' : $item2['birth_year'] . ' CE' ?> – 
                        <?= $item2['death_year'] < 0 ? abs($item2['death_year']) . ' BCE' : $item2['death_year'] . ' CE' ?>
                    </div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Historical Titles / Aliases</div>
                    <div class="compare-val"><?= htmlspecialchars($item2['alt_names'] ?: 'None recorded') ?></div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Associated Monuments</div>
                    <div class="compare-val">
                        <?php if (!empty($mons2)): ?>
                            <ul style="padding-left: 18px; margin: 0;">
                                <?php foreach ($mons2 as $m): ?>
                                    <li><a href="/monument.php?id=<?= $m['id'] ?>"><strong><?= htmlspecialchars($m['name']) ?></strong></a> (<?= htmlspecialchars($m['location_text']) ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span style="color: var(--text-muted);">None directly attributed in register</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Historical Biography</div>
                    <div class="compare-val" style="font-size: 13.5px; line-height: 1.6;">
                        <?= htmlspecialchars($item2['bio']) ?>
                    </div>
                </div>

                <div style="margin-top: 18px; text-align: center;">
                    <a href="/person.php?id=<?= $item2['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                        View Full Dossier of <?= htmlspecialchars($item2['name']) ?> ➔
                    </a>
                </div>
            </div>

        </div>

    <?php else: ?>
        <!-- MONUMENT VS MONUMENT COMPARISON GRID -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
            
            <!-- MONUMENT 1 -->
            <div class="compare-card">
                <div style="margin-bottom: 16px;">
                    <img src="<?= htmlspecialchars($item1['image_url']) ?>" alt="<?= htmlspecialchars($item1['name']) ?>" style="width: 100%; height: 210px; object-fit: cover; border-radius: var(--radius-md);">
                    <h2 style="font-size: 22px; margin-top: 12px; margin-bottom: 4px;"><?= htmlspecialchars($item1['name']) ?></h2>
                    <span class="badge" style="background: var(--primary-light); color: var(--primary);"><?= htmlspecialchars($item1['location_text']) ?></span>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Commissioned By</div>
                    <div class="compare-val"><strong><?= htmlspecialchars($item1['built_by']) ?></strong></div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Construction Year</div>
                    <div class="compare-val"><?= htmlspecialchars($item1['built_year_text']) ?> (<?= $item1['built_year'] ?> CE)</div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Architectural Style</div>
                    <div class="compare-val"><?= htmlspecialchars($item1['architectural_style']) ?></div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Architectural Summary</div>
                    <div class="compare-val" style="font-size: 13.5px; line-height: 1.6;">
                        <?= htmlspecialchars($item1['summary']) ?>
                    </div>
                </div>

                <div style="margin-top: 18px; text-align: center;">
                    <a href="/monument.php?id=<?= $item1['id'] ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                        View Full Monument Story ➔
                    </a>
                </div>
            </div>

            <!-- MONUMENT 2 -->
            <div class="compare-card">
                <div style="margin-bottom: 16px;">
                    <img src="<?= htmlspecialchars($item2['image_url']) ?>" alt="<?= htmlspecialchars($item2['name']) ?>" style="width: 100%; height: 210px; object-fit: cover; border-radius: var(--radius-md);">
                    <h2 style="font-size: 22px; margin-top: 12px; margin-bottom: 4px;"><?= htmlspecialchars($item2['name']) ?></h2>
                    <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold);"><?= htmlspecialchars($item2['location_text']) ?></span>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Commissioned By</div>
                    <div class="compare-val"><strong><?= htmlspecialchars($item2['built_by']) ?></strong></div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Construction Year</div>
                    <div class="compare-val"><?= htmlspecialchars($item2['built_year_text']) ?> (<?= $item2['built_year'] ?> CE)</div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Architectural Style</div>
                    <div class="compare-val"><?= htmlspecialchars($item2['architectural_style']) ?></div>
                </div>

                <div class="compare-row">
                    <div class="compare-label">Architectural Summary</div>
                    <div class="compare-val" style="font-size: 13.5px; line-height: 1.6;">
                        <?= htmlspecialchars($item2['summary']) ?>
                    </div>
                </div>

                <div style="margin-top: 18px; text-align: center;">
                    <a href="/monument.php?id=<?= $item2['id'] ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                        View Full Monument Story ➔
                    </a>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<style>
.preset-pill {
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-body);
    transition: all 0.2s;
    border: 1px solid var(--border-light);
}
.preset-pill:hover {
    background: var(--accent-gold-light);
    color: var(--accent-gold);
    border-color: var(--accent-gold);
}
.compare-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
}
.compare-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--accent-gold);
    box-shadow: var(--shadow-md);
}
.compare-row {
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
}
.compare-row:last-child {
    border-bottom: none;
}
.compare-label {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.compare-val {
    font-size: 14.5px;
    color: var(--text-main);
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>

<?php
// app/views/person_detail.php
$pageTitle = cleanPersonName($person['name']) . " — Historical Figure";
require __DIR__ . '/layouts/header.php';
?>

<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 28px;">
    <div style="display: flex; gap: 28px; align-items: flex-start; flex-wrap: wrap;">
        <img src="<?= htmlspecialchars($person['portrait_url'] ?: '/assets/img/persons/ashoka.png') ?>" alt="<?= htmlspecialchars($person['name']) ?>" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid var(--border-light); object-fit: cover; box-shadow: var(--shadow-sm);">
        
        <div style="flex: 1; min-width: 300px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge" style="background: var(--primary-light); color: var(--primary);">
                    <?= htmlspecialchars(cleanRole($person['role'])) ?>
                </span>
                <span class="badge badge-score">
                    <?= $person['birth_formatted'] ?> – <?= $person['death_formatted'] ?>
                </span>
            </div>

            <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 6px;"><?= htmlspecialchars(cleanPersonName($person['name'])) ?></h1>
            
            <?php if (!empty($person['alt_names'])): ?>
                <div style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 12px;">
                    <strong>Historical Titles & Aliases (उपाधि व अन्य नाम):</strong> <?= htmlspecialchars($person['alt_names']) ?>
                </div>
            <?php endif; ?>

            <p id="narrate-bio" style="font-size: 15px; color: var(--text-body); line-height: 1.7; margin-bottom: 16px;">
                <?= htmlspecialchars($person['bio']) ?>
            </p>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button id="btn-audio-narrate" class="btn btn-secondary btn-sm" onclick="toggleAudioNarration()">
                    🔊 <span id="audio-btn-text">Listen Biography (जीवनी सुनें)</span>
                </button>
                <button class="btn btn-secondary btn-sm" onclick="window.print()">🖨️ Print</button>
                <?php if (Auth::hasRole('researcher', 'admin')): ?>
                    <a href="/persons/edit?id=<?= $person['id'] ?>" class="btn btn-primary btn-sm">✏️ Edit Figure</a>
                    <form action="/persons/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this historical figure?');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= $person['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;">🗑️ Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <a href="/persons" class="btn btn-secondary btn-sm">← Back to All Emperors</a>
        </div>
    </div>
</div>

<!-- Monuments Built by this Emperor / Ruler -->
<?php if (!empty($person['monuments'])): ?>
    <div style="margin-bottom: 32px;">
        <div style="margin-bottom: 16px;">
            <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold); margin-bottom: 6px;">
                🏰 Masterpieces Commissioned
            </span>
            <h3 style="font-size: 22px; color: var(--text-main);">Architectural Monuments Built by <?= htmlspecialchars(cleanPersonName($person['name'])) ?></h3>
        </div>
        <div class="clean-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
            <?php foreach ($person['monuments'] as $m): ?>
                <div class="clean-manuscript-card">
                    <div class="clean-card-img" style="height: 160px; background-image: url('<?= htmlspecialchars($m['image_url']) ?>');">
                        <span class="clean-card-pill"><?= htmlspecialchars($m['built_year_text']) ?></span>
                    </div>
                    <div class="clean-card-body">
                        <h4 class="clean-card-title">
                            <a href="/monument?id=<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></a>
                        </h4>
                        <p class="clean-card-desc"><?= htmlspecialchars($m['summary']) ?></p>
                        <div class="clean-card-footer">
                            <span>📍 <?= htmlspecialchars($m['location_text']) ?></span>
                            <a href="/monument?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Explore Monument →</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Relational Network & Manuscript Testimonies -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Surviving Manuscript Testimonies -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-xs);">
        <h3 style="font-size: 18px; margin-bottom: 14px; border-bottom: 1px solid var(--border-light); padding-bottom: 8px;">
            📜 Ancient Books & Manuscripts (ग्रन्थ)
        </h3>

        <?php if (empty($person['manuscripts'])): ?>
            <p style="color: var(--text-faint); font-size: 13.5px;">Mentioned across royal chronicles and court decrees.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($person['manuscripts'] as $m): ?>
                    <div style="background: var(--bg-surface-alt); padding: 14px; border-radius: 6px; border: 1px solid var(--border-light);">
                        <h4 style="font-size: 14.5px; margin-bottom: 4px;">
                            <a href="/manuscript?id=<?= $m['id'] ?>"><?= htmlspecialchars(cleanDocTitle($m['title'])) ?></a>
                        </h4>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            Era: <?= Calendar::formatYear((int)$m['era_start']) ?> · By <?= htmlspecialchars($m['author']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Direct Relationships -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-xs);">
        <h3 style="font-size: 18px; margin-bottom: 14px; border-bottom: 1px solid var(--border-light); padding-bottom: 8px;">
            🤝 Historical Family, Courtiers & Allies (संबंधी व दरबारी)
        </h3>

        <?php if (empty($person['relations'])): ?>
            <p style="color: var(--text-faint); font-size: 13.5px;">No direct familial relationships recorded.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($person['relations'] as $rel): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-alt); padding: 10px 14px; border-radius: 6px;">
                        <div>
                            <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 10.5px;">
                                <?= htmlspecialchars(ucfirst($rel['relationship_type'] ?? 'ally')) ?>
                            </span>
                            <strong style="margin-left: 6px;">
                                <a href="/person?id=<?= $rel['related_person_id'] ?>" style="color: var(--text-main);">
                                    <?= htmlspecialchars(cleanPersonName($rel['related_person_name'] ?? 'Historical Figure')) ?>
                                </a>
                            </strong>
                        </div>
                        <a href="/person?id=<?= $rel['related_person_id'] ?>" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 4px 10px;">View →</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
var isSpeaking = false;
function toggleAudioNarration() {
    if (!('speechSynthesis' in window)) {
        alert("Text-to-speech is not supported in this browser.");
        return;
    }

    if (isSpeaking) {
        window.speechSynthesis.cancel();
        isSpeaking = false;
        document.getElementById('audio-btn-text').innerText = "Listen Biography (जीवनी सुनें)";
        return;
    }

    var bio = document.getElementById('narrate-bio').innerText;
    var text = "<?= addslashes(cleanPersonName($person['name'])) ?>. <?= addslashes($person['role']) ?>. " + bio;

    var utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 0.95;
    utterance.pitch = 1.0;

    utterance.onend = function() {
        isSpeaking = false;
        document.getElementById('audio-btn-text').innerText = "Listen Biography (जीवनी सुनें)";
    };

    window.speechSynthesis.speak(utterance);
    isSpeaking = true;
    document.getElementById('audio-btn-text').innerText = "⏹️ Stop Audio (रोकें)";
}
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

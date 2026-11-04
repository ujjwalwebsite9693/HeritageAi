<?php
// app/views/monument_detail.php
$pageTitle = $monument['name'] . " — Historical Monument";
require __DIR__ . '/layouts/header.php';
?>

<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 32px;">
    <!-- Monument Hero Banner -->
    <div style="height: 360px; background-size: cover; background-position: center; position: relative; background-image: url('<?= htmlspecialchars($monument['image_url']) ?>');">
        <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0.25) 0%, rgba(15,23,42,0.88) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 36px;">
            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <span class="badge" style="background: var(--accent-gold); color: #fff;"><?= htmlspecialchars($monument['era_label']) ?></span>
                <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff;"><?= htmlspecialchars($monument['built_year_text']) ?></span>
            </div>
            <h1 style="color: #ffffff; font-size: 36px; margin-bottom: 6px;"><?= htmlspecialchars($monument['name']) ?></h1>
            <div style="color: #f1f5f9; font-size: 15px;">
                📍 <?= htmlspecialchars($monument['location_text']) ?> · Commissioned by <strong><?= htmlspecialchars($monument['built_by']) ?></strong>
            </div>
        </div>
    </div>

    <!-- Quick Facts Strip & Actions -->
    <div style="background: var(--bg-surface-alt); padding: 18px 36px; border-bottom: 1px solid var(--border-light); display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: center; font-size: 13.5px;">
        <div style="display: flex; flex-wrap: wrap; gap: 24px;">
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Builder / निर्माता</span>
                <strong><?= htmlspecialchars($monument['built_by']) ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Construction Period / निर्माण काल</span>
                <strong><?= htmlspecialchars($monument['built_year_text']) ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Architectural Style / वास्तुकला</span>
                <strong><?= htmlspecialchars($monument['architectural_style'] ?? 'Indo-Islamic') ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Location / स्थान</span>
                <strong><?= htmlspecialchars($monument['place_ancient'] ?? $monument['location_text']) ?></strong>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button id="btn-audio-narrate" class="btn btn-secondary btn-sm" onclick="toggleAudioNarration()">
                🔊 <span id="audio-btn-text">Listen Story (कहानी सुनें)</span>
            </button>
            <button class="btn btn-secondary btn-sm" onclick="window.print()">🖨️ Print</button>
            <?php if (Auth::hasRole('researcher', 'admin')): ?>
                <a href="/monuments/edit?id=<?= $monument['id'] ?>" class="btn btn-primary btn-sm">✏️ Edit Monument</a>
                <form action="/monuments/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this monument?');">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= $monument['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;">🗑️ Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px; align-items: start;">
    <!-- Left Column: History & Significance -->
    <div>
        <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 28px; box-shadow: var(--shadow-xs); margin-bottom: 24px;">
            <h2 style="font-size: 20px; color: var(--text-main); margin-bottom: 12px;">Historical Overview (ऐतिहासिक परिचय)</h2>
            <p id="narrate-summary" style="font-size: 15.5px; color: var(--text-body); line-height: 1.8; margin-bottom: 20px;">
                <?= htmlspecialchars($monument['summary']) ?>
            </p>

            <h3 style="font-size: 17px; color: var(--text-main); margin-bottom: 10px;">The Story of Construction (निर्माण की कहानी)</h3>
            <p id="narrate-history" style="font-size: 14.5px; color: var(--text-muted); line-height: 1.8;">
                <?= nl2br(htmlspecialchars($monument['history'])) ?>
            </p>
        </div>

        <!-- Architectural Highlights -->
        <?php if (!empty($monument['highlights_array'])): ?>
            <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-xs); margin-bottom: 24px;">
                <h3 style="font-size: 17px; color: var(--text-main); margin-bottom: 14px;">Key Architectural Highlights (मुख्य विशेषताएं)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <?php foreach ($monument['highlights_array'] as $h): ?>
                        <div style="background: var(--bg-surface-alt); padding: 12px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <span style="color: var(--status-verified); font-weight: bold;">✓</span>
                            <?= htmlspecialchars($h) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Visiting Info & Linked Manuscripts -->
    <div>
        <!-- Visiting Practical Info -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 22px; box-shadow: var(--shadow-xs); margin-bottom: 24px;">
            <h3 style="font-size: 16px; color: var(--text-main); margin-bottom: 10px;">🕒 Visiting & Heritage Guide (दर्शन समय)</h3>
            <p style="font-size: 13.5px; color: var(--text-body); line-height: 1.6; margin-bottom: 14px;">
                <?= htmlspecialchars($monument['visiting_info'] ?? 'Open daily from sunrise to sunset.') ?>
            </p>
            <a href="/map" class="btn btn-secondary btn-sm" style="width: 100%;">View on Historical Map 🗺️</a>
        </div>

        <!-- Connected Manuscripts in Archive -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 22px; box-shadow: var(--shadow-xs);">
            <h3 style="font-size: 16px; color: var(--text-main); margin-bottom: 12px;">📜 Related Ancient Texts (जुड़े हुए ग्रन्थ)</h3>
            <?php if (empty($monument['connected_manuscripts'])): ?>
                <p style="font-size: 13px; color: var(--text-faint);">Check regional manuscripts under the Manuscripts section.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($monument['connected_manuscripts'] as $cm): ?>
                        <div style="background: var(--bg-surface-alt); padding: 12px 14px; border-radius: 6px; font-size: 13px;">
                            <a href="/manuscript?id=<?= $cm['id'] ?>" style="font-weight: 600; color: var(--text-main);">
                                <?= htmlspecialchars(cleanDocTitle($cm['title'])) ?>
                            </a>
                            <div style="font-size: 11.5px; color: var(--text-faint); margin-top: 2px;">
                                Era: <?= Calendar::formatYear((int)$cm['era_start']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
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
        document.getElementById('audio-btn-text').innerText = "Listen Story (कहानी सुनें)";
        return;
    }

    var summary = document.getElementById('narrate-summary').innerText;
    var history = document.getElementById('narrate-history').innerText;
    var text = "<?= addslashes($monument['name']) ?>. Built by <?= addslashes($monument['built_by']) ?>. " + summary + " " + history;

    var utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 0.95;
    utterance.pitch = 1.0;

    utterance.onend = function() {
        isSpeaking = false;
        document.getElementById('audio-btn-text').innerText = "Listen Story (कहानी सुनें)";
    };

    window.speechSynthesis.speak(utterance);
    isSpeaking = true;
    document.getElementById('audio-btn-text').innerText = "⏹️ Stop Audio (रोकें)";
}
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

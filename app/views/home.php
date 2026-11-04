<?php
// app/views/home.php
$pageTitle = "Explore Monuments, Emperors & Manuscripts";
require __DIR__ . '/layouts/header.php';
?>

<!-- Friendly, Visual Hero Section -->
<div class="hero-clean">
    <div class="hero-pill">
        🏛️ भारतीय इतिहास और धरोहर · Indian History & Heritage
    </div>
    <h1 class="hero-clean-title">Discover India’s Famous Emperors, Monuments & Ancient Books</h1>
    <p class="hero-clean-subtitle">
        सम्राट अकबर, हुमायूँ, शाहजहाँ, बाबर और अशोक की रोचक कहानियां पढ़ें; ताजमहल और लाल किले का इतिहास देखें; और प्राचीन ग्रन्थों के सरल अनुवाद समझें।
    </p>

    <!-- Search Bar with Instant Query -->
    <form action="/search" method="GET" class="hero-search-bar">
        <input type="text" name="q" placeholder="Search by emperor, monument, city or book (e.g. Akbar, Taj Mahal, Humayun, Red Fort)...">
        <button type="submit" class="btn btn-primary">Search / खोजें</button>
    </form>

    <!-- 1-Click Search Shortcut Badges -->
    <div class="quick-pills-bar">
        <span style="font-size: 13px; color: var(--text-muted); font-weight: 600; align-self: center;">Popular Searches:</span>
        <a href="/monument?id=1" class="clean-pill">🏰 Taj Mahal (ताजमहल)</a>
        <a href="/person?id=6" class="clean-pill">👑 Emperor Akbar (अकबर)</a>
        <a href="/monument?id=2" class="clean-pill">🚩 Red Fort (लाल किला)</a>
        <a href="/person?id=11" class="clean-pill">🕌 Humayun (हुमायूँ)</a>
        <a href="/person?id=10" class="clean-pill">💎 Shah Jahan (शाहजहाँ)</a>
        <a href="/person?id=1" class="clean-pill">🕊️ Ashoka (अशोक)</a>
        <a href="/monument?id=5" class="clean-pill">🏛️ Nalanda (नालंदा)</a>
        <a href="/quiz" class="clean-pill" style="border-color: #f59e0b; background: #fffbeb; color: #b45309;">🎯 Play History Quiz (क्विज खेलें)</a>
    </div>
</div>

<!-- 4 Easy Category Navigation Cards -->
<div class="quick-features-grid">
    <a href="/monuments" class="quick-feature-box">
        <div class="quick-feature-icon">🏰</div>
        <div class="quick-feature-title">Historical Monuments (स्मारक)</div>
        <div class="quick-feature-desc">Taj Mahal, Red Fort, Humayun's Tomb, Fatehpur Sikri & more.</div>
    </a>

    <a href="/persons" class="quick-feature-box">
        <div class="quick-feature-icon">👑</div>
        <div class="quick-feature-title">Great Emperors (शासक व राजा)</div>
        <div class="quick-feature-desc">Meet Akbar, Humayun, Babur, Shah Jahan, Ashoka & Birbal.</div>
    </a>

    <a href="/search" class="quick-feature-box">
        <div class="quick-feature-icon">📜</div>
        <div class="quick-feature-title">Ancient Books (प्राचीन ग्रन्थ)</div>
        <div class="quick-feature-desc">Read Ain-i-Akbari, Arthashastra & edicts with Hindi translations.</div>
    </a>

    <a href="/map" class="quick-feature-box">
        <div class="quick-feature-icon">🗺️</div>
        <div class="quick-feature-title">Historical Map (ऐतिहासिक नक्शा)</div>
        <div class="quick-feature-desc">Explore cities, monuments, and trade routes across India.</div>
    </a>
</div>

<!-- SECTION 1: Famous Monuments with Photos & Plain Details -->
<div style="margin-bottom: 52px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
        <div>
            <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold); margin-bottom: 6px;">
                🏰 Architecture of India · भारतीय वास्तुकला
            </span>
            <h2 style="font-size: 24px; color: var(--text-main);">Famous Historical Monuments (प्रसिद्ध ऐतिहासिक स्मारक)</h2>
            <p style="font-size: 14.5px; color: var(--text-muted); margin-top: 4px;">
                भारत के भव्य महल, किले और मकबरे — जानें किसने बनवाया, कब बना, और इनका क्या इतिहास है।
            </p>
        </div>
        <a href="/monuments" class="btn btn-outline btn-sm">View All 7 Monuments →</a>
    </div>

    <div class="clean-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
        <?php foreach ($featuredMonuments as $mon): ?>
            <div class="clean-manuscript-card">
                <div class="clean-card-img" style="height: 180px; background-image: url('<?= htmlspecialchars($mon['image_url']) ?>');">
                    <span class="clean-card-pill"><?= htmlspecialchars($mon['built_year_text']) ?></span>
                </div>
                <div class="clean-card-body">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--accent-gold); font-weight: 700; margin-bottom: 4px;">
                        <?= htmlspecialchars($mon['era_label']) ?> · 📍 <?= htmlspecialchars($mon['location_text']) ?>
                    </div>
                    <h3 class="clean-card-title" style="font-size: 18px;">
                        <a href="/monument?id=<?= $mon['id'] ?>"><?= htmlspecialchars($mon['name']) ?></a>
                    </h3>
                    <div style="font-size: 13px; color: var(--text-body); margin-bottom: 8px;">
                        <strong>Built By / निर्माता:</strong> <?= htmlspecialchars($mon['built_by']) ?>
                    </div>
                    <p class="clean-card-desc" style="font-size: 13.5px; line-height: 1.5; color: var(--text-muted); margin-bottom: 14px;">
                        <?= htmlspecialchars(substr($mon['summary'], 0, 115)) ?>...
                    </p>
                    <div class="clean-card-footer">
                        <span style="font-size: 12px; color: var(--text-faint);"><?= htmlspecialchars($mon['architectural_style'] ?? 'Architecture') ?></span>
                        <a href="/monument?id=<?= $mon['id'] ?>" class="btn btn-primary btn-sm">Explore Monument →</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- SECTION 2: Legendary Emperors & Figures with Photos -->
<div style="margin-bottom: 52px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
        <div>
            <span class="badge" style="background: var(--primary-light); color: var(--primary); margin-bottom: 6px;">
                👑 Sovereign Figures · महान शासक और व्यक्तित्व
            </span>
            <h2 style="font-size: 24px; color: var(--text-main);">Legendary Emperors & Leaders (महान राजा और विचारक)</h2>
            <p style="font-size: 14.5px; color: var(--text-muted); margin-top: 4px;">
                Meet the emperors who shaped India's destiny, built legendary cities, and wrote history.
            </p>
        </div>
        <a href="/persons" class="btn btn-outline btn-sm">View All Emperors →</a>
    </div>

    <div class="clean-grid" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));">
        <?php foreach ($featuredFigures as $fig): ?>
            <div class="clean-manuscript-card" style="padding: 22px; text-align: center;">
                <div style="width: 100px; height: 100px; margin: 0 auto 14px auto;">
                    <img src="<?= htmlspecialchars($fig['portrait_url'] ?: '/assets/img/persons/ashoka.png') ?>" alt="<?= htmlspecialchars($fig['name']) ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #cbd5e1; box-shadow: var(--shadow-sm);">
                </div>
                <div style="font-size: 11px; color: var(--text-faint); margin-bottom: 4px;">
                    <?= Calendar::formatYear((int)$fig['birth_year']) ?> – <?= Calendar::formatYear((int)$fig['death_year']) ?>
                </div>
                <h3 style="font-size: 17px; margin-bottom: 4px;">
                    <a href="/person?id=<?= $fig['id'] ?>"><?= htmlspecialchars(cleanPersonName($fig['name'])) ?></a>
                </h3>
                <div style="font-size: 12px; font-weight: 600; color: var(--accent-gold); margin-bottom: 10px;">
                    <?= htmlspecialchars(cleanRole($fig['role'])) ?>
                </div>
                <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px; text-align: left; flex: 1;">
                    <?= htmlspecialchars(substr($fig['bio'] ?? '', 0, 105)) ?>...
                </p>
                <a href="/person?id=<?= $fig['id'] ?>" class="btn btn-outline btn-sm" style="width: 100%;">
                    View Life & Monuments →
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- SECTION 3: Preserved Manuscripts in Simple Cards -->
<div style="margin-bottom: 52px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
        <div>
            <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold); margin-bottom: 6px;">
                📜 Ancient Literature · ऐतिहासिक ग्रन्थ
            </span>
            <h2 style="font-size: 24px; color: var(--text-main);">Preserved Manuscripts & Texts (प्राचीन ग्रन्थ)</h2>
            <p style="font-size: 14.5px; color: var(--text-muted); margin-top: 4px;">
                Read high-resolution scanned original texts with side-by-side Hindi and English translations.
            </p>
        </div>
        <a href="/search" class="btn btn-outline btn-sm">Explore All Manuscripts →</a>
    </div>

    <div class="clean-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
        <?php foreach ($featured as $m): ?>
            <div class="clean-manuscript-card">
                <div class="clean-card-img" style="height: 180px; background-image: url('<?= htmlspecialchars($m['thumbnail'] ?: '/assets/img/manuscript-ashoka.jpg') ?>');">
                    <span class="clean-card-pill"><?= Calendar::formatYear((int)$m['era_start']) ?></span>
                </div>
                <div class="clean-card-body">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--text-faint); font-weight: 700; margin-bottom: 4px;">
                        <?= htmlspecialchars($m['language_name']) ?> · <?= htmlspecialchars($m['place_ancient'] ?? 'India') ?>
                    </div>
                    <h3 class="clean-card-title" style="font-size: 17px;">
                        <a href="/manuscript?id=<?= $m['id'] ?>"><?= htmlspecialchars(cleanDocTitle($m['title'])) ?></a>
                    </h3>
                    <p class="clean-card-desc" style="font-size: 13.5px; color: var(--text-muted); line-height: 1.5; margin-bottom: 14px;">
                        <?= htmlspecialchars(substr($m['summary'] ?? '', 0, 115)) ?>...
                    </p>
                    <div class="clean-card-footer">
                        <span>By <?= htmlspecialchars(substr($m['author'], 0, 18)) ?></span>
                        <a href="/manuscript?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Read in Hindi & English →</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- SECTION 4: Fascinating Historical Facts & Stories -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 40px;">
    <div style="margin-bottom: 24px;">
        <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold); margin-bottom: 6px;">
            💡 Did You Know? · क्या आप जानते हैं?
        </span>
        <h2 style="font-size: 24px; color: var(--text-main);">Fascinating Facts of Indian History (रोचक ऐतिहासिक तथ्य)</h2>
        <p style="font-size: 14.5px; color: var(--text-muted); margin-top: 4px;">
            Easy, interesting stories that bring Indian monuments and emperors to life.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div style="background: var(--bg-surface-alt); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid var(--primary);">
            <div style="font-size: 24px; margin-bottom: 8px;">🏰 ⇄ 🕌</div>
            <h4 style="font-size: 16px; margin-bottom: 6px; color: var(--text-main);">Inspiration for Taj Mahal</h4>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                Humayun’s Tomb in Delhi, built in 1570, was the first garden-tomb in India and served as the direct architectural blueprint for the Taj Mahal built 60 years later!
            </p>
        </div>

        <div style="background: var(--bg-surface-alt); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid var(--accent-gold);">
            <div style="font-size: 24px; margin-bottom: 8px;">👑 ⚡ 13</div>
            <h4 style="font-size: 16px; margin-bottom: 6px; color: var(--text-main);">Akbar Crowned at Age 13</h4>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                Akbar was just 13 years old when he became Emperor in Kalanaur, Punjab. With Bairam Khan’s guidance, he went on to build one of the most powerful empires in the world.
            </p>
        </div>

        <div style="background: var(--bg-surface-alt); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid #10b981;">
            <div style="font-size: 24px; margin-bottom: 8px;">🏛️ 📚 9M</div>
            <h4 style="font-size: 16px; margin-bottom: 6px; color: var(--text-main);">Nalanda's 9 Million Books</h4>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                Nalanda University was the world's first great residential university. Its library, Dharma Gunj, was so huge that it took months to burn when it was attacked in 1193 CE.
            </p>
        </div>

        <div style="background: var(--bg-surface-alt); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid #6366f1;">
            <div style="font-size: 24px; margin-bottom: 8px;">🕊️ 📜 2200</div>
            <h4 style="font-size: 16px; margin-bottom: 6px; color: var(--text-main);">Ashoka's Message of Peace</h4>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                After the brutal Kalinga war, Emperor Ashoka renounced violence and carved 33 rock edicts across India, preaching kindness to animals, truth, and respect for all faiths.
            </p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

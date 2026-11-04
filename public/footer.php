<?php
// public/footer.php - Shared Footer
?>
        </div><!-- /.container -->
    </main><!-- /.site-content -->

    <footer class="site-footer">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="brand-emblem" style="width: 34px; height: 34px; font-size: 16px;">🏛️</div>
                    <div>
                        <span style="color: #ffffff; font-weight: 700; font-size: 16px;">Bharat Heritage Archive</span>
                        <div style="font-size: 11px; color: #94a3b8;">भारतीय इतिहास, स्मारक और पाण्डुलिपि अभिलेखागार</div>
                    </div>
                </div>
                <div style="display: flex; gap: 18px; font-size: 13.5px; flex-wrap: wrap;">
                    <a href="/monuments.php" style="color: #cbd5e1;">🏰 Monuments (स्मारक)</a>
                    <a href="/persons.php" style="color: #cbd5e1;">👑 Emperors (शासक)</a>
                    <a href="/search.php" style="color: #cbd5e1;">📜 Manuscripts (ग्रन्थ)</a>
                    <a href="/map.php" style="color: #cbd5e1;">🗺️ Map (नक्शा)</a>
                    <a href="/timeline.php" style="color: #cbd5e1;">⏳ Timeline</a>
                    <a href="/graph.php" style="color: #cbd5e1;">🔗 Connections</a>
                    <a href="/quiz.php" style="color: #fef08a;">🎯 Quiz</a>
                    <a href="/admin.php" style="color: #fca5a5; font-weight: 600;">Admin Panel ⚙️</a>
                </div>
            </div>

            <div class="footer-bottom">
                <div>© <?= date('Y') ?> Bharat Heritage Archive · Indian History & Architecture</div>
                <div>Plain PHP · Simple & Easy to Understand and Modify</div>
            </div>
        </div>
    </footer>

    <?php if (!str_contains($currentUri ?? '', 'assistant')): ?>
    <!-- Floating Acharya AI Bubble -->
    <a href="/assistant.php" class="floating-ai-btn" title="Ask Acharya AI (ऐतिहासिक AI सहायक)">
        <span class="ai-pulse-ring"></span>
        <span style="font-size: 20px;">🤖</span>
        <span style="font-size: 13.5px; font-weight: 700; letter-spacing: 0.2px;">Ask Acharya AI</span>
    </a>
    <?php endif; ?>

    <!-- Hackathon Live Pitch Walkthrough Modal -->
    <div id="pitch-modal" class="pitch-modal-backdrop" onclick="if(event.target===this) closePitchModal()">
        <div class="pitch-modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light); padding-bottom: 14px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 26px;">🏆</span>
                    <div>
                        <h2 style="font-size: 20px; color: var(--text-main); margin: 0;">Bharat Heritage · Hackathon Pitch Showcase</h2>
                        <div style="font-size: 12px; color: var(--accent-gold); font-weight: 600;">Complete Demo Architecture & Key Innovations</div>
                    </div>
                </div>
                <button onclick="closePitchModal()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <!-- Pitch Tabs / Highlights -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 22px;">
                
                <div class="pitch-card">
                    <div style="font-size: 28px; margin-bottom: 8px;">📜</div>
                    <h3 style="font-size: 16px; margin-bottom: 6px;">1. Manuscript Intelligence</h3>
                    <p style="font-size: 13px; color: var(--text-body); line-height: 1.5;">
                        Phonetic <strong>Soundex algorithms</strong> overcome historical spelling variations (e.g. Akbar/Akbarr). Integrated with <strong>OpenSeadragon</strong> for deep-zoom manuscript exploration and bilingual Sanskrit/Persian transliterations.
                    </p>
                    <a href="/search.php" class="pitch-link">Test Soundex Search ➔</a>
                </div>

                <div class="pitch-card">
                    <div style="font-size: 28px; margin-bottom: 8px;">🏰</div>
                    <h3 style="font-size: 16px; margin-bottom: 6px;">2. Living Monuments & Audio</h3>
                    <p style="font-size: 13px; color: var(--text-body); line-height: 1.5;">
                        Real-time <strong>Web Speech API Text-to-Speech</strong> audio storytelling, high-resolution architectural photo galleries, interactive city filtering, and mobile QR code sharing for visitors.
                    </p>
                    <a href="/monuments.php" class="pitch-link">Explore Monuments ➔</a>
                </div>

                <div class="pitch-card">
                    <div style="font-size: 28px; margin-bottom: 8px;">🤖</div>
                    <h3 style="font-size: 16px; margin-bottom: 6px;">3. Acharya AI Historical Assistant</h3>
                    <p style="font-size: 13px; color: var(--text-body); line-height: 1.5;">
                        Autonomous AI historical scholar equipped with semantic entity recognition, voice-to-text queries, cross-referenced historical citations, and instant conversational answers.
                    </p>
                    <a href="/assistant.php" class="pitch-link">Ask Acharya AI ➔</a>
                </div>

                <div class="pitch-card">
                    <div style="font-size: 28px; margin-bottom: 8px;">⚖️</div>
                    <h3 style="font-size: 16px; margin-bottom: 6px;">4. Comparative Analytics Matrix</h3>
                    <p style="font-size: 13px; color: var(--text-body); line-height: 1.5;">
                        Side-by-side analytical comparisons between rulers (e.g. Akbar vs Ashoka) and monuments (Taj Mahal vs Red Fort) comparing eras, architecture, and governing philosophies.
                    </p>
                    <a href="/compare.php" class="pitch-link">Open Comparison Matrix ➔</a>
                </div>

                <div class="pitch-card">
                    <div style="font-size: 28px; margin-bottom: 8px;">🔗</div>
                    <h3 style="font-size: 16px; margin-bottom: 6px;">5. Knowledge Graph & Map</h3>
                    <p style="font-size: 13px; color: var(--text-body); line-height: 1.5;">
                        Interactive <strong>vis.js network connections</strong> mapping royal dynasties and royal scribes, alongside a geospatial <strong>Leaflet historical map</strong> with trade corridors.
                    </p>
                    <a href="/graph.php" class="pitch-link">Inspect Network Graph ➔</a>
                </div>

                <div class="pitch-card">
                    <div style="font-size: 28px; margin-bottom: 8px;">🎓</div>
                    <h3 style="font-size: 16px; margin-bottom: 6px;">6. Heritage Quiz & Verifiable Cert</h3>
                    <p style="font-size: 13px; color: var(--text-body); line-height: 1.5;">
                        Gamified 6-question Indian history challenge rendering an instant high-resolution, personalized parchment certificate on HTML5 Canvas ready to download and print.
                    </p>
                    <a href="/quiz.php" class="pitch-link">Take Quiz & Claim Cert ➔</a>
                </div>

            </div>

            <!-- Tech Stack Pill Footer -->
            <div style="background: #f8fafc; border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 12.5px; color: var(--text-muted);">
                    <strong>Tech Stack:</strong> Plain Single-File PHP · MySQL 8.0 · OpenSeadragon · Leaflet.js · vis.js · Web Speech API · HTML5 Canvas
                </div>
                <button onclick="closePitchModal()" class="btn btn-primary btn-sm">Close Pitch View</button>
            </div>
        </div>
    </div>

    <style>
    /* Floating AI Button */
    .floating-ai-btn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 999;
        background: linear-gradient(135deg, #991b1b, #7f1d1d);
        color: #ffffff !important;
        padding: 12px 20px;
        border-radius: var(--radius-full);
        box-shadow: 0 8px 24px rgba(153, 27, 27, 0.4);
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
    .floating-ai-btn:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 12px 28px rgba(153, 27, 27, 0.5);
    }
    .ai-pulse-ring {
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        border-radius: var(--radius-full);
        border: 2px solid #f59e0b;
        animation: pulseRing 2s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        opacity: 0.7;
    }
    @keyframes pulseRing {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.08); opacity: 0; }
        100% { transform: scale(0.95); opacity: 0; }
    }

    /* Pitch Modal */
    .pitch-modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .pitch-modal-content {
        background: var(--bg-surface);
        width: 100%;
        max-width: 940px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: var(--radius-lg);
        padding: 28px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
    }
    .pitch-card {
        background: #f8fafc;
        border: 1px solid var(--border-light);
        border-radius: var(--radius-md);
        padding: 16px;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .pitch-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
        border-color: var(--primary);
    }
    .pitch-link {
        font-size: 12px;
        font-weight: 700;
        color: var(--primary);
        margin-top: auto;
        padding-top: 10px;
        display: inline-block;
    }
    </style>

    <script>
    function openPitchModal() {
        var m = document.getElementById('pitch-modal');
        if (m) m.style.display = 'flex';
    }
    function closePitchModal() {
        var m = document.getElementById('pitch-modal');
        if (m) m.style.display = 'none';
    }
    </script>

    <script src="/assets/js/app.js"></script>
</body>
</html>

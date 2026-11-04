<?php
// app/views/layouts/footer.php
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
                    <a href="/monuments" style="color: #cbd5e1;">🏰 Monuments (स्मारक)</a>
                    <a href="/persons" style="color: #cbd5e1;">👑 Emperors (शासक)</a>
                    <a href="/search" style="color: #cbd5e1;">📜 Manuscripts (ग्रन्थ)</a>
                    <a href="/map" style="color: #cbd5e1;">🗺️ Map (नक्शा)</a>
                    <a href="/timeline" style="color: #cbd5e1;">⏳ Timeline</a>
                    <a href="/graph" style="color: #cbd5e1;">🔗 Connections</a>
                    <a href="/admin" style="color: #fca5a5; font-weight: 600;">Admin Panel ⚙️</a>
                </div>
            </div>

            <div class="footer-bottom">
                <div>© <?= date('Y') ?> Bharat Heritage Archive · Indian History & Architecture</div>
                <div>Visual, Simple & Easy to Understand</div>
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>
</html>

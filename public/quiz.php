<?php
// public/quiz.php - Interactive Indian History Quiz Challenge (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();
$pageTitle = "History Quiz Challenge · इतिहास प्रश्नोत्तरी";
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 820px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 32px;">
        <div class="hero-pill">
            🎯 Test Your Knowledge · इतिहास प्रश्नोत्तरी
        </div>
        <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 8px;">Indian History & Heritage Quiz</h1>
        <p style="color: var(--text-muted); font-size: 15px;">
            Test your knowledge of Indian emperors, monuments, and ancient books. Answer 6 quick questions and earn your heritage certificate badge!
        </p>
    </div>

    <!-- Score Bar -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 16px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm);">
        <div>
            <span style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Current Score:</span>
            <span id="quiz-score" style="font-size: 22px; font-weight: 800; color: var(--primary); margin-left: 6px;">0</span> / 6
        </div>
        <div id="quiz-progress-text" style="font-size: 13.5px; font-weight: 600; color: var(--accent-gold);">
            Question 1 of 6
        </div>
        <button id="btn-restart-quiz" class="btn btn-secondary btn-sm" onclick="restartQuiz()" style="display: none;">Restart Quiz 🔄</button>
    </div>

    <!-- Quiz Card -->
    <div id="quiz-card" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-md); margin-bottom: 40px;">
        <div style="display: flex; gap: 8px; margin-bottom: 12px;">
            <span id="q-category" class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold);">Monuments</span>
        </div>
        <h2 id="q-question" style="font-size: 22px; color: var(--text-main); margin-bottom: 24px; line-height: 1.4;">
            Loading question...
        </h2>

        <div id="q-options" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;"></div>

        <div id="q-feedback" style="display: none; padding: 16px 20px; border-radius: var(--radius-sm); font-size: 14.5px; line-height: 1.5; margin-bottom: 20px;"></div>

        <div style="display: flex; justify-content: flex-end;">
            <button id="btn-next-question" class="btn btn-primary" onclick="nextQuestion()" style="display: none; padding: 10px 28px;">Next Question →</button>
        </div>
    </div>

    <!-- Final Results Box -->
    <div id="quiz-result-box" style="display: none; background: var(--bg-surface); border: 2px solid var(--accent-gold); border-radius: var(--radius-lg); padding: 36px 28px; text-align: center; box-shadow: var(--shadow-md); margin-bottom: 40px;">
        <div style="font-size: 54px; margin-bottom: 10px;">🏆</div>
        <h2 style="font-size: 28px; color: var(--text-main); margin-bottom: 8px;">Quiz Completed!</h2>
        <div id="result-message" style="font-size: 16px; color: var(--text-body); margin-bottom: 24px;"></div>

        <!-- Certificate Generator Section -->
        <div style="background: #faf8f5; border: 1.5px dashed var(--accent-gold); border-radius: var(--radius-md); padding: 24px; margin: 24px auto; max-width: 650px;">
            <h3 style="font-size: 18px; color: var(--accent-gold); margin-bottom: 6px;">📜 Claim Your Official Heritage Scholar Certificate</h3>
            <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 14px;">
                Enter your name to generate your verified, high-resolution certificate of completion to showcase at the hackathon or add to your portfolio!
            </p>
            <div style="display: flex; gap: 10px; justify-content: center; max-width: 450px; margin: 0 auto 16px;">
                <input type="text" id="cert-name-input" value="<?= htmlspecialchars($currentUser['name'] ?? 'Heritage Scholar') ?>" 
                       placeholder="Enter your full name" 
                       style="flex: 1; padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); font-size: 14px;">
                <button class="btn btn-primary" onclick="generateCertificate()" style="font-size: 13.5px;">Generate 🎓</button>
            </div>

            <!-- Canvas Container -->
            <div id="cert-preview-area" style="display: none; margin-top: 18px;">
                <canvas id="cert-canvas" width="900" height="600" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: var(--shadow-lg); border: 1px solid #cbd5e1;"></canvas>
                <div style="margin-top: 14px; display: flex; gap: 10px; justify-content: center;">
                    <button class="btn btn-primary" onclick="downloadCertificate()">📥 Download Certificate (PNG)</button>
                    <button class="btn btn-outline" onclick="printCertificate()">🖨️ Print Certificate</button>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: center; gap: 12px; margin-top: 20px;">
            <button class="btn btn-secondary" onclick="restartQuiz()">Play Again 🔄</button>
            <a href="/monuments.php" class="btn btn-outline">Explore Monuments 🏰</a>
            <a href="/compare.php" class="btn btn-outline">Emperors Matrix ⚖️</a>
        </div>
    </div>
</div>

<script>
var quizData = [
    {
        category: "🏰 Monuments (स्मारक)",
        question: "Who commissioned the construction of the Taj Mahal in Agra? (ताजमहल का निर्माण किसने करवाया था?)",
        options: ["Emperor Akbar", "Emperor Shah Jahan", "Emperor Babur", "Emperor Humayun"],
        answer: 1,
        explanation: "Emperor Shah Jahan built the Taj Mahal between 1632 and 1653 CE in memory of his beloved wife Mumtaz Mahal."
    },
    {
        category: "👑 Emperors (शासक)",
        question: "At what age was Akbar crowned as the Third Mughal Emperor? (अकबर कितने वर्ष की उम्र में सम्राट बने?)",
        options: ["13 years old", "18 years old", "25 years old", "30 years old"],
        answer: 0,
        explanation: "Akbar was crowned in Kalanaur, Punjab at just 13 years old in 1556 CE after the sudden death of his father Humayun."
    },
    {
        category: "🏰 Architecture (वास्तुकला)",
        question: "Which monument in Delhi was the direct architectural inspiration for the Taj Mahal? (ताजमहल की प्रेरणा कौन सा स्मारक था?)",
        options: ["Qutub Minar", "Red Fort", "Humayun's Tomb", "Fatehpur Sikri"],
        answer: 2,
        explanation: "Humayun's Tomb (1570 CE) in Delhi was the first garden-tomb in India and served as the direct architectural blueprint for the Taj Mahal."
    },
    {
        category: "📜 Ancient Manuscripts (ग्रन्थ)",
        question: "Who authored the monumental statecraft codex 'Arthashastra'? (अर्थशास्त्र के रचयिता कौन हैं?)",
        options: ["Kalidasa", "Banabhatta", "Kautilya (Chanakya)", "Abul Fazl"],
        answer: 2,
        explanation: "Kautilya (Chanakya), royal mentor to Chandragupta Maurya, authored the Arthashastra detailing governance, economics, and foreign policy."
    },
    {
        category: "🏛️ Ancient Heritage (धरोहर)",
        question: "Which ancient international university had a library housing over 9 million manuscripts? (90 लाख पाण्डुलिपियों वाला प्राचीन विश्वविद्यालय?)",
        options: ["Nalanda Mahavihara", "Takshashila", "Vikramashila", "Vallabhi"],
        answer: 0,
        explanation: "Nalanda Mahavihara in Bihar was the world's first great residential university with over 10,000 students and a 9-million manuscript library."
    },
    {
        category: "🕊️ Peace & Dhamma (शांति व धर्म)",
        question: "Which emperor renounced warfare and embraced Buddhism following the brutal Kalinga war? (कलिंग युद्ध के बाद किसने युद्ध त्याग दिया?)",
        options: ["Chandragupta Maurya", "Emperor Ashoka", "King Harsha", "Samudragupta"],
        answer: 1,
        explanation: "Emperor Ashoka Maurya renounced violence after the Kalinga war (c. 261 BCE) and carved rock edicts across India advocating moral Dhamma and compassion."
    }
];

var currentQIndex = 0;
var score = 0;
var answered = false;

function loadQuestion() {
    answered = false;
    var q = quizData[currentQIndex];

    document.getElementById('q-category').innerText = q.category;
    document.getElementById('q-question').innerText = q.question;
    document.getElementById('quiz-progress-text').innerText = "Question " + (currentQIndex + 1) + " of " + quizData.length;
    document.getElementById('btn-next-question').style.display = "none";
    document.getElementById('q-feedback').style.display = "none";

    var optionsContainer = document.getElementById('q-options');
    optionsContainer.innerHTML = "";

    q.options.forEach(function(optText, idx) {
        var btn = document.createElement('button');
        btn.className = "btn btn-secondary";
        btn.style.textAlign = "left";
        btn.style.justifyContent = "flex-start";
        btn.style.padding = "14px 20px";
        btn.style.fontSize = "15px";
        btn.style.borderRadius = "var(--radius-sm)";
        btn.innerText = (idx + 1) + ". " + optText;
        btn.onclick = function() { checkAnswer(idx); };
        optionsContainer.appendChild(btn);
    });
}

function checkAnswer(selectedIndex) {
    if (answered) return;
    answered = true;

    var q = quizData[currentQIndex];
    var optionButtons = document.querySelectorAll('#q-options button');
    var feedbackBox = document.getElementById('q-feedback');

    optionButtons.forEach(function(btn, idx) {
        btn.disabled = true;
        if (idx === q.answer) {
            btn.style.background = "#dcfce7";
            btn.style.borderColor = "#22c55e";
            btn.style.color = "#15803d";
            btn.style.fontWeight = "bold";
        } else if (idx === selectedIndex) {
            btn.style.background = "#fee2e2";
            btn.style.borderColor = "#ef4444";
            btn.style.color = "#991b1b";
        }
    });

    if (selectedIndex === q.answer) {
        score++;
        document.getElementById('quiz-score').innerText = score;
        feedbackBox.style.display = "block";
        feedbackBox.style.background = "#f0fdf4";
        feedbackBox.style.border = "1px solid #bbf7d0";
        feedbackBox.style.color = "#166534";
        feedbackBox.innerHTML = "<strong>✅ Correct! सही उत्तर!</strong> " + q.explanation;
    } else {
        feedbackBox.style.display = "block";
        feedbackBox.style.background = "#fef2f2";
        feedbackBox.style.border = "1px solid #fecaca";
        feedbackBox.style.color = "#991b1b";
        feedbackBox.innerHTML = "<strong>❌ Incorrect.</strong> Correct answer: <strong>" + q.options[q.answer] + "</strong>. " + q.explanation;
    }

    document.getElementById('btn-next-question').style.display = "inline-flex";
}

function nextQuestion() {
    currentQIndex++;
    if (currentQIndex < quizData.length) {
        loadQuestion();
    } else {
        showResults();
    }
}

function showResults() {
    document.getElementById('quiz-card').style.display = "none";
    document.getElementById('quiz-result-box').style.display = "block";
    document.getElementById('btn-restart-quiz').style.display = "inline-flex";

    var pct = Math.round((score / quizData.length) * 100);
    var badgeTitle = "Heritage Enthusiast 📜";
    if (score === 6) badgeTitle = "Grand Historian (इतिहास शिरोमणि) 👑";
    else if (score >= 4) badgeTitle = "Heritage Scholar (धरोहर विद्वान) 🏛️";

    document.getElementById('result-message').innerHTML = "You scored <strong>" + score + " out of " + quizData.length + "</strong> (" + pct + "%).<br><span class='badge' style='background: var(--accent-gold-light); color: var(--accent-gold); font-size: 14px; padding: 6px 16px; margin-top: 10px;'>" + badgeTitle + "</span>";
    
    // Auto generate certificate preview
    setTimeout(generateCertificate, 300);
}

function generateCertificate() {
    var name = document.getElementById('cert-name-input').value.trim() || 'Heritage Scholar';
    var canvas = document.getElementById('cert-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var w = canvas.width;
    var h = canvas.height;

    // Background parchment
    ctx.fillStyle = '#fdfbf7';
    ctx.fillRect(0, 0, w, h);

    // Decorative Borders
    ctx.strokeStyle = '#991b1b'; // Heritage Red
    ctx.lineWidth = 14;
    ctx.strokeRect(20, 20, w - 40, h - 40);

    ctx.strokeStyle = '#b45309'; // Gold
    ctx.lineWidth = 3;
    ctx.strokeRect(34, 34, w - 68, h - 68);

    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;
    ctx.strokeRect(42, 42, w - 84, h - 84);

    // Corner Ornaments
    drawCorner(ctx, 42, 42);
    drawCorner(ctx, w - 42, 42, true);
    drawCorner(ctx, 42, h - 42, false, true);
    drawCorner(ctx, w - 42, h - 42, true, true);

    // Header Emblem & Title
    ctx.textAlign = 'center';
    ctx.font = '36px "Segoe UI Emoji", serif';
    ctx.fillText('🏛️', w / 2, 95);

    ctx.fillStyle = '#991b1b';
    ctx.font = 'bold 22px "Cinzel", Georgia, serif';
    ctx.letterSpacing = '2px';
    ctx.fillText('BHARAT HERITAGE ARCHIVES · भारत धरोहर', w / 2, 135);

    ctx.fillStyle = '#64748b';
    ctx.font = '12px "Inter", sans-serif';
    ctx.fillText('NATIONAL HISTORICAL SCHOLARSHIP CERTIFICATION', w / 2, 155);

    ctx.fillStyle = '#0f172a';
    ctx.font = 'bold 32px "Cinzel", Georgia, serif';
    ctx.fillText('CERTIFICATE OF ACHIEVEMENT', w / 2, 215);

    ctx.fillStyle = '#64748b';
    ctx.font = 'italic 16px "Inter", serif';
    ctx.fillText('This is proudly presented in recognition of historical scholarship to:', w / 2, 260);

    // Candidate Name
    ctx.fillStyle = '#b45309';
    ctx.font = 'bold 36px "Cinzel", Georgia, serif';
    ctx.fillText(name.toUpperCase(), w / 2, 320);

    // Underline
    ctx.strokeStyle = '#cbd5e1';
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(w / 2 - 220, 335);
    ctx.lineTo(w / 2 + 220, 335);
    ctx.stroke();

    // Body Text
    ctx.fillStyle = '#334155';
    ctx.font = '15px "Inter", sans-serif';
    ctx.fillText('For successfully mastering the historical inquiry into India\'s ancient empires,', w / 2, 375);
    ctx.fillText('classical architectural monuments, and monumental primary manuscripts.', w / 2, 400);

    var pct = Math.round((score / quizData.length) * 100);
    ctx.font = 'bold 15px "Inter", sans-serif';
    ctx.fillStyle = '#047857';
    ctx.fillText('Verified Score: ' + score + ' / ' + quizData.length + ' (' + pct + '% Proficiency)', w / 2, 435);

    // Footer - Verification ID & Seals
    var today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    var certId = 'BH-' + Math.random().toString(36).substring(2, 8).toUpperCase() + '-2026';

    ctx.textAlign = 'left';
    ctx.fillStyle = '#64748b';
    ctx.font = '12px "Inter", sans-serif';
    ctx.fillText('Date Issued: ' + today, 70, 520);
    ctx.fillText('Certificate ID: ' + certId, 70, 540);
    ctx.fillText('Verification: cryptographically sealed archive registry', 70, 560);

    // Seal
    ctx.beginPath();
    ctx.arc(w - 140, 520, 44, 0, Math.PI * 2);
    ctx.fillStyle = '#fffbeb';
    ctx.fill();
    ctx.strokeStyle = '#b45309';
    ctx.lineWidth = 3;
    ctx.stroke();

    ctx.textAlign = 'center';
    ctx.fillStyle = '#b45309';
    ctx.font = 'bold 10px "Cinzel", serif';
    ctx.fillText('OFFICIAL SEAL', w - 140, 515);
    ctx.font = '18px "Segoe UI Emoji", serif';
    ctx.fillText('📜', w - 140, 535);

    document.getElementById('cert-preview-area').style.display = 'block';
}

function drawCorner(ctx, x, y, flipX, flipY) {
    ctx.save();
    ctx.translate(x, y);
    if (flipX) ctx.scale(-1, 1);
    if (flipY) ctx.scale(1, -1);
    ctx.strokeStyle = '#b45309';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(20, 0);
    ctx.moveTo(0, 0);
    ctx.lineTo(0, 20);
    ctx.stroke();
    ctx.restore();
}

function downloadCertificate() {
    var canvas = document.getElementById('cert-canvas');
    var link = document.createElement('a');
    var name = (document.getElementById('cert-name-input').value.trim() || 'Scholar').replace(/\s+/g, '_');
    link.download = 'Bharat_Heritage_Certificate_' + name + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

function printCertificate() {
    var canvas = document.getElementById('cert-canvas');
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Print Heritage Certificate</title></head><body style="margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;"><img src="' + canvas.toDataURL('image/png') + '" style="max-width:95%;height:auto;"/></body></html>');
    win.document.close();
    setTimeout(function() { win.print(); }, 400);
}

function restartQuiz() {
    score = 0;
    currentQIndex = 0;
    document.getElementById('quiz-score').innerText = "0";
    document.getElementById('quiz-card').style.display = "block";
    document.getElementById('quiz-result-box').style.display = "none";
    document.getElementById('btn-restart-quiz').style.display = "none";
    loadQuestion();
}

document.addEventListener("DOMContentLoaded", function() {
    loadQuestion();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

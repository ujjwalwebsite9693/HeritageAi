<?php
// public/assistant.php - Acharya AI: Historical Research Scholar & Guide (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();

// Fetch all database records so the AI has 100% verified real database facts
$allPersons = $db->query("SELECT * FROM persons")->fetchAll();
$allMonuments = $db->query("SELECT * FROM monuments")->fetchAll();
$allManuscripts = $db->query("SELECT * FROM manuscripts")->fetchAll();
$allEvents = $db->query("SELECT * FROM events ORDER BY sort_year ASC")->fetchAll();

$pageTitle = "Acharya AI · Historical Research Assistant (धरोहर AI)";
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 980px; margin: 0 auto;">
    <!-- Hero Header -->
    <div style="text-align: center; margin-bottom: 28px;">
        <div class="hero-pill" style="background: linear-gradient(135deg, #fef3c7, #fee2e2); border-color: #fcd34d;">
            🤖 Interactive AI Scholar · धरोहर बुद्धिमान सहायक
        </div>
        <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 8px;">
            Acharya AI — Heritage Research Assistant
        </h1>
        <p style="color: var(--text-muted); font-size: 15px; max-width: 680px; margin: 0 auto;">
            Ask questions about Indian emperors, Mughal & Maurya monuments, ancient codices, and historical events. Acharya AI provides verified, cross-referenced historical answers with voice narration.
        </p>
    </div>

    <!-- Quick Question Chips -->
    <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
        <span style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); align-self: center;">💡 Try Asking:</span>
        <button class="chip-btn" onclick="askPreset('Who built the Taj Mahal and why?')">🕌 Who built Taj Mahal?</button>
        <button class="chip-btn" onclick="askPreset('Tell me about Emperor Akbar and his court')">👑 Emperor Akbar</button>
        <button class="chip-btn" onclick="askPreset('What is the Arthashastra by Chanakya?')">📜 Kautilya's Arthashastra</button>
        <button class="chip-btn" onclick="askPreset('How did Ashoka change after the Kalinga war?')">🕊️ Ashoka & Dhamma</button>
        <button class="chip-btn" onclick="askPreset('Tell me about Nalanda Mahavihara library')">🏛️ Nalanda University</button>
        <button class="chip-btn" onclick="askPreset('Compare Akbar and Ashoka')">⚖️ Akbar vs Ashoka</button>
        <button class="chip-btn" onclick="askPreset('Which monuments can I visit in Agra or Delhi?')">📍 Monuments in Agra & Delhi</button>
    </div>

    <!-- Chat Container -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; display: flex; flex-direction: column; min-height: 520px;">
        
        <!-- Chat Header Strip -->
        <div style="background: linear-gradient(to right, #0f172a, #1e293b); color: #fff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #b45309; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    🧙‍♂️
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 14.5px; letter-spacing: 0.3px;">Acharya Somadeva · Digital Epigraphist</div>
                    <div style="font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #22c55e;"></span>
                        Online · Verified against 13 rulers, 7 monuments, 6 manuscripts
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="clearChat()" class="btn btn-sm" style="background: rgba(255,255,255,0.1); color: #e2e8f0; border: none; font-size: 12px; padding: 4px 10px;">
                    🧹 Clear
                </button>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div id="chat-messages" style="flex: 1; padding: 24px; overflow-y: auto; max-height: 480px; display: flex; flex-direction: column; gap: 18px; background: #fafbfc;">
            
            <!-- Welcome Message -->
            <div class="chat-bubble-ai">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="font-weight: 700; color: var(--primary); font-size: 13.5px;">Acharya AI (धरोहर विद्वान)</span>
                    <span class="badge" style="font-size: 10px; background: var(--accent-gold-light); color: var(--accent-gold);">Epigraphist</span>
                </div>
                <p style="font-size: 14.5px; line-height: 1.6; color: var(--text-body); margin-bottom: 8px;">
                    नमस्ते! Greetings, scholar. I am <strong>Acharya Somadeva</strong>, your historical intelligence companion.
                    Ask me anything about Indian emperors (Akbar, Ashoka, Babur, Shah Jahan, Chanakya), ancient monuments, or historical manuscripts like the <em>Ain-i-Akbari</em> and <em>Arthashastra</em>.
                </p>
                <div style="font-size: 13px; color: var(--text-muted); background: #f1f5f9; padding: 8px 12px; border-radius: 6px; display: inline-block;">
                    ⚡ Tip: You can type in English or Hindi (हिंग्लिश), or click any suggestion chip above!
                </div>
            </div>

        </div>

        <!-- Chat Input Bar -->
        <div style="padding: 16px 20px; background: #ffffff; border-top: 1px solid var(--border-light);">
            <form id="chat-form" onsubmit="handleSend(event)" style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="chat-input" placeholder="Ask about any emperor, monument, battle, or ancient manuscript..." 
                       style="flex: 1; padding: 12px 18px; border: 1.5px solid var(--border-light); border-radius: var(--radius-full); font-size: 14.5px; outline: none; transition: border 0.2s;"
                       onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-light)'" autocomplete="off" />
                
                <button type="button" id="voice-btn" onclick="toggleVoiceInput()" title="Voice Input (Speech-to-Text)"
                        style="width: 44px; height: 44px; border-radius: 50%; border: 1px solid var(--border-light); background: #f8fafc; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    🎙️
                </button>

                <button type="submit" class="btn btn-primary" style="border-radius: var(--radius-full); padding: 11px 24px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <span>Ask AI</span> ➔
                </button>
            </form>
            <div id="voice-indicator" style="display: none; font-size: 12px; color: var(--primary); margin-top: 6px; text-align: center; font-weight: 600;">
                🔴 Listening to your voice... Speak now!
            </div>
        </div>
    </div>
</div>

<style>
.chip-btn {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-full);
    padding: 6px 14px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-body);
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.chip-btn:hover {
    background: var(--primary-light);
    color: var(--primary);
    border-color: var(--primary);
    transform: translateY(-1px);
}
.chat-bubble-ai {
    background: #ffffff;
    border: 1px solid var(--border-light);
    border-left: 4px solid var(--primary);
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    max-width: 88%;
    align-self: flex-start;
}
.chat-bubble-user {
    background: linear-gradient(135deg, var(--primary), #7f1d1d);
    color: #ffffff;
    border-radius: 12px;
    padding: 14px 18px;
    box-shadow: var(--shadow-sm);
    max-width: 80%;
    align-self: flex-end;
    font-size: 14.5px;
    line-height: 1.5;
}
.ai-entity-card {
    display: flex;
    gap: 14px;
    background: #f8fafc;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 12px;
    margin-top: 10px;
    align-items: center;
}
.speech-pulse {
    animation: pulseWave 1.2s infinite;
}
@keyframes pulseWave {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.15); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<script>
// Load entire real archive catalog for instant client-side AI semantic matching
var archiveData = {
    persons: <?= json_encode($allPersons, JSON_UNESCAPED_UNICODE) ?>,
    monuments: <?= json_encode($allMonuments, JSON_UNESCAPED_UNICODE) ?>,
    manuscripts: <?= json_encode($allManuscripts, JSON_UNESCAPED_UNICODE) ?>,
    events: <?= json_encode($allEvents, JSON_UNESCAPED_UNICODE) ?>
};

function askPreset(question) {
    document.getElementById('chat-input').value = question;
    handleSend();
}

function handleSend(e) {
    if (e) e.preventDefault();
    var input = document.getElementById('chat-input');
    var query = input.value.trim();
    if (!query) return;

    // Append User Message
    appendMessage(query, 'user');
    input.value = '';

    // Show AI Thinking Indicator
    var thinkingId = 'thinking-' + Date.now();
    var messagesContainer = document.getElementById('chat-messages');
    var thinkingDiv = document.createElement('div');
    thinkingDiv.id = thinkingId;
    thinkingDiv.className = 'chat-bubble-ai';
    thinkingDiv.innerHTML = '<span style="font-size: 13px; color: var(--text-muted);">Acharya Somadeva is consulting historical scrolls... ⏳</span>';
    messagesContainer.appendChild(thinkingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    setTimeout(function() {
        var replyObj = generateAiResponse(query);
        var tEl = document.getElementById(thinkingId);
        if (tEl) tEl.remove();

        appendAiMessage(replyObj);
    }, 450);
}

function appendMessage(text, sender) {
    var messagesContainer = document.getElementById('chat-messages');
    var div = document.createElement('div');
    div.className = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-ai';
    div.innerText = text;
    messagesContainer.appendChild(div);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function appendAiMessage(reply) {
    var messagesContainer = document.getElementById('chat-messages');
    var div = document.createElement('div');
    div.className = 'chat-bubble-ai';

    var html = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-weight: 700; color: var(--primary); font-size: 13.5px;">Acharya AI (धरोहर विद्वान)</span>
                <span class="badge" style="font-size: 10px; background: #e0f2fe; color: #0284c7;">Historical Synthesis</span>
            </div>
            <button class="btn btn-outline btn-sm" onclick="speakText(this, decodeURIComponent('${encodeURIComponent(reply.speechText)}'))" 
                    style="font-size: 11px; padding: 3px 8px; border-radius: 12px; display: flex; align-items: center; gap: 4px;">
                <span class="speaker-icon">🔊</span> Listen
            </button>
        </div>
        <div style="font-size: 14.5px; line-height: 1.6; color: var(--text-body);">
            ${reply.bodyHtml}
        </div>
    `;

    if (reply.entityCard) {
        html += reply.entityCard;
    }

    if (reply.citation) {
        html += `
            <div style="margin-top: 10px; font-size: 11.5px; color: var(--text-muted); border-top: 1px dashed var(--border-light); padding-top: 6px;">
                📚 <em>Source Verification:</em> ${reply.citation}
            </div>
        `;
    }

    div.innerHTML = html;
    messagesContainer.appendChild(div);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// AI Semantic Knowledge Engine
function generateAiResponse(q) {
    var raw = q.trim();
    var qLower = raw.toLowerCase().replace(/[?!.,;:]/g, ' ');

    // 1. GREETINGS & CASUAL HELLO (English, Hindi, Hinglish)
    var greetings = ['hi', 'hii', 'hiii', 'hello', 'hey', 'namaste', 'namaskar', 'pranam', 'pranaam', 'kaise ho', 'kya hal', 'kya haal', 'good morning', 'good afternoon', 'good evening', 'kem cho', 'salaam', 'adaab'];
    var words = qLower.trim().split(/\s+/);
    var isGreeting = greetings.some(function(g) {
        return words.includes(g) || qLower.trim() === g;
    });

    if (isGreeting) {
        return {
            speechText: "नमस्ते! Greetings! I am Acharya Somadeva, your historical intelligence guide. How may I assist your historical inquiry today?",
            bodyHtml: `
                <p><strong>नमस्ते! Hello & Welcome, scholar! 🙏</strong></p>
                <p style="margin-top: 6px;">मैं <strong>आचार्य सोमदेव (Acharya Somadeva)</strong> हूँ — भारत धरोहर का डिजिटल इतिहासकार व AI शोध सहायक।</p>
                <p style="margin-top: 6px;">आप मुझसे भारतीय इतिहास, सम्राटों, ऐतिहासिक स्मारकों और प्राचीन पाण्डुलिपियों के बारे में कुछ भी पूछ सकते हैं। नीचे दिए गए विषयों पर क्लिक करें या सीधे टाइप करें:</p>
                <div style="margin-top: 14px; display: flex; flex-wrap: wrap; gap: 8px;">
                    <button class="chip-btn" onclick="askPreset('सम्राट अकबर के बारे में बताओ')">👑 अकबर की जीवनी</button>
                    <button class="chip-btn" onclick="askPreset('ताजमहल का इतिहास और निर्माण किसने करवाया?')">🕌 ताजमहल का इतिहास</button>
                    <button class="chip-btn" onclick="askPreset('सम्राट अशोक और कलिंग युद्ध')">🕊️ अशोक व कलिंग युद्ध</button>
                    <button class="chip-btn" onclick="askPreset('चाणक्य का अर्थशास्त्र क्या है?')">📜 चाणक्य का अर्थशास्त्र</button>
                    <button class="chip-btn" onclick="askPreset('राजा बीरबल कौन थे?')">💎 राजा बीरबल</button>
                    <button class="chip-btn" onclick="askPreset('लाल किला किसने बनवाया?')">🚩 लाल किला (Red Fort)</button>
                    <button class="chip-btn" onclick="askPreset('Compare Akbar and Ashoka')">⚖️ Akbar vs Ashoka</button>
                </div>
            `,
            citation: "Acharya Somadeva Epigraphic Dialogue Engine."
        };
    }

    // 2. IDENTITY / HELP / CAPABILITIES INTENT
    if (qLower.includes('who are you') || qLower.includes('kaun ho') || qLower.includes('kya kar sakte') || qLower.includes('help') || qLower.includes('kya kaam') || qLower.includes('about yourself') || qLower.includes('kya hai ye') || qLower.includes('kya hai')) {
        return {
            speechText: "I am Acharya Somadeva, an interactive AI historian and epigraphist built for the Bharat Heritage Archive. I synthesize historical facts across 13 rulers, 7 monuments, and ancient manuscripts with voice narration.",
            bodyHtml: `
                <p><strong>I am Acharya Somadeva (आचार्य सोमदेव)</strong> — an AI historical scholar and digital epigraphist for the <em>Bharat Heritage Archive</em>.</p>
                <p style="margin-top: 6px;"><strong>What I can do for you (मेरी क्षमताएँ):</strong></p>
                <ul style="margin-left: 20px; margin-top: 8px; line-height: 1.6;">
                    <li>👑 <strong>Rulers & Thinkers:</strong> Akbar, Ashoka, Chanakya, Babur, Humayun, Shah Jahan, Birbal, Todar Mal, Harsha, Xuanzang.</li>
                    <li>🏰 <strong>Architectural Monuments:</strong> Taj Mahal, Red Fort, Humayun's Tomb, Fatehpur Sikri, Nalanda Mahavihara.</li>
                    <li>📜 <strong>Manuscript Codices:</strong> Arthashastra, Ain-i-Akbari, Baburnama, Ashokan Rock Edicts.</li>
                    <li>⚖️ <strong>Comparative Matrices:</strong> Side-by-side analysis (e.g. Akbar vs Ashoka).</li>
                    <li>🔊 <strong>Voice Narration:</strong> Click 'Listen' on any response to hear it read out loud!</li>
                </ul>
            `,
            citation: "Bharat Heritage Platform Intelligence Module."
        };
    }

    // 3. COMPARISON INTENT
    if (qLower.includes('compare') || qLower.includes('vs') || qLower.includes('tulna') || (qLower.includes('akbar') && qLower.includes('ashoka'))) {
        return {
            speechText: "Comparing Emperor Akbar and Emperor Ashoka: Akbar ruled the Mughal Empire in the 16th century, promoting religious tolerance through Sulh-i-Kul. Emperor Ashoka ruled the Maurya Empire in the 3rd century BCE, embracing Buddhism and propagating non-violence and Dhamma after the Kalinga War.",
            bodyHtml: `
                <p><strong>Emperor Akbar vs Emperor Ashoka — Two Pillars of Indian History:</strong></p>
                <ul style="margin-left: 20px; margin-top: 8px; line-height: 1.6;">
                    <li><strong>Emperor Ashoka (304–232 BCE):</strong> Third ruler of the Maurya Dynasty. After witnessing the devastation of the Kalinga War (261 BCE), he renounced conquest by sword (Digvijaya) and embraced Dhamma-vijaya (righteous governance), inscribing edicts across the subcontinent on stone pillars and rocks.</li>
                    <li><strong>Emperor Akbar (1542–1605 CE):</strong> Third ruler of the Mughal Empire. Crowned at age 13, he expanded imperial frontiers while instituting the policy of <em>Sulh-i-Kul</em> (Universal Peace), abolishing the jizya tax, and hosting theological dialogues at the Ibadat Khana in Fatehpur Sikri.</li>
                </ul>
                <div style="margin-top: 10px;">
                    <a href="/compare.php?mode=person&p1=6&p2=1" class="btn btn-secondary btn-sm" style="font-size: 12px;">📊 Open Full Side-by-Side Comparison Matrix ➔</a>
                </div>
            `,
            citation: "Historical synthesis cross-referenced with Ashokan Major Rock Edict XIII & Abul-Fazl's Akbarnama."
        };
    }

    // 4. TAJ MAHAL INTENT (Hindi & English)
    if (qLower.includes('taj mahal') || qLower.includes('tajmahal') || qLower.includes('taj') || qLower.includes('ताजमहल') || qLower.includes('mumtaz') || qLower.includes('मुमताज')) {
        var m = archiveData.monuments.find(x => x.name.toLowerCase().includes('taj mahal'));
        return {
            speechText: "The Taj Mahal in Agra was commissioned by the fifth Mughal Emperor Shah Jahan in 1632 in memory of his beloved empress Mumtaz Mahal. Built in pristine white Makrana marble, it stands on the southern bank of the Yamuna River.",
            bodyHtml: `
                <p><strong>ताजमहल (Taj Mahal) — प्रेम व वास्तुकला का अनुपम प्रतीक:</strong></p>
                <p style="margin-top: 6px;">मुगल सम्राट <strong>शाहजहाँ</strong> ने 1632 ईस्वी में अपनी बेगम <strong>मुमताज महल</strong> की स्मृति में यमुना नदी के तट पर आगरा में इसका निर्माण शुरू करवाया था। मुख्य वास्तुकार उस्ताद अहमद लाहौरी के मार्गदर्शन में 20,000 से अधिक शिल्पकारों ने 1653 ईस्वी में इसे पूर्ण किया। यह सफेद मकराना संगमरमर (Makrana marble) से निर्मित यूनेस्को विश्व धरोहर स्थल है।</p>
            `,
            entityCard: m ? `
                <div class="ai-entity-card">
                    <img src="${m.image_url}" alt="Taj Mahal" style="width: 70px; height: 70px; border-radius: 8px; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${m.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${m.location_text} · Built by ${m.built_by} (${m.built_year_text})</div>
                        <a href="/monument.php?id=${m.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Monument Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Abdul Hamid Lahori, Padshahnama (Royal Mughal Court Chronicle)."
        };
    }

    // 5. RED FORT / LAL KILA INTENT
    if (qLower.includes('red fort') || qLower.includes('lal kila') || qLower.includes('lal qila') || qLower.includes('लाल किला')) {
        var m = archiveData.monuments.find(x => x.name.toLowerCase().includes('red fort') || x.name.toLowerCase().includes('lal'));
        return {
            speechText: "The Red Fort or Lal Qila in Old Delhi was constructed by Mughal Emperor Shah Jahan between 1638 and 1648 when he shifted the imperial capital from Agra to Shahjahanabad. Built of massive red sandstone walls, it houses the Diwan-i-Aam and Diwan-i-Khas.",
            bodyHtml: `
                <p><strong>लाल किला (Red Fort / Lal Qila, Delhi):</strong></p>
                <p style="margin-top: 6px;">मुगल सम्राट <strong>शाहजहाँ</strong> ने 1638 से 1648 ईस्वी के बीच अपनी नई राजधानी <em>शाहजहानाबाद (पुरानी दिल्ली)</em> में लाल बलुआ पत्थर से इस ऐतिहासिक दुर्ग का निर्माण करवाया। इसमें दीवान-ए-आम, दीवान-ए-खास और नहर-ए-बहिश्त स्थित हैं। भारत के स्वतंत्रता दिवस पर प्रधानमंत्री यहीं से तिरंगा फहराते हैं।</p>
            `,
            entityCard: m ? `
                <div class="ai-entity-card">
                    <img src="${m.image_url}" alt="Red Fort" style="width: 70px; height: 70px; border-radius: 8px; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${m.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${m.location_text} · Built by ${m.built_by} (${m.built_year_text})</div>
                        <a href="/monument.php?id=${m.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Monument Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Archaeological Survey of India Imperial Fort Gazette."
        };
    }

    // 6. AKBAR INTENT (Hindi & English)
    if (qLower.includes('akbar') || qLower.includes('अकबर') || qLower.includes('jalaluddin') || qLower.includes('sulh-i-kul') || qLower.includes('din-i-ilahi')) {
        var p = archiveData.persons.find(x => x.name.toLowerCase().includes('akbar'));
        return {
            speechText: "Jalal al-Din Muhammad Akbar was the third Mughal Emperor, reigning from 1556 to 1605. Known as Akbar the Great, he introduced revolutionary administrative reforms, the Mansabdari system, and advocated Sulh-i-Kul.",
            bodyHtml: `
                <p><strong>जलालुद्दीन मुहम्मद अकबर (Emperor Akbar the Great, 1542–1605 CE):</strong></p>
                <p style="margin-top: 4px;">अकबर मात्र 13 वर्ष की आयु में 1556 ईस्वी में कलानौर, पंजाब में मुगल सिंहासन पर बैठे। उन्होंने पानीपत के दूसरे युद्ध में विजय प्राप्त कर साम्राज्य का विस्तार किया। अकबर ने <em>सुलह-ए-कुल</em> (सार्वभौमिक शांति) की नीति अपनाई, जजिया कर समाप्त किया, और फतेहपुर सीकरी में इबादतखाना बनवाया। उनके दरबार में बीरबल और राजा टोडरमल सहित नवरत्न उपस्थित थे।</p>
            `,
            entityCard: p ? `
                <div class="ai-entity-card">
                    <img src="${p.portrait_url}" alt="Akbar" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${p.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${p.role} · Lifespan: 1542 – 1605 CE</div>
                        <a href="/person.php?id=${p.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Emperor Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Shaikh Abul-Fazl, Ain-i-Akbari & Akbarnama."
        };
    }

    // 7. BIRBAL INTENT
    if (qLower.includes('birbal') || qLower.includes('बीरबल') || qLower.includes('mahesh das')) {
        var p = archiveData.persons.find(x => x.name.toLowerCase().includes('birbal'));
        return {
            speechText: "Raja Birbal, born Mahesh Das, was a grand vizier, military commander, and courtier of Emperor Akbar. Renowned for his extraordinary wit, wisdom, and folk stories, he was one of Akbar's nine jewels.",
            bodyHtml: `
                <p><strong>राजा बीरबल (Raja Birbal / महेश दास, 1528–1586 CE):</strong></p>
                <p style="margin-top: 4px;">राजा बीरबल (मूल नाम महेश दास) सम्राट अकबर के नवरत्नों (Nine Jewels) में सबसे प्रिय मित्र व वजीर-ए-आजम थे। वे अपनी कुशाग्र बुद्धि, हाजिरजवाबी और न्यायप्रियता के लिए पूरे भारतीय इतिहास और लोककथाओं में विख्यात हैं। उन्होंने अकबर द्वारा शुरू किए गए <em>दीन-ए-इलाही</em> धर्म को भी स्वीकार किया था।</p>
            `,
            entityCard: p ? `
                <div class="ai-entity-card">
                    <img src="${p.portrait_url}" alt="Birbal" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${p.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${p.role} · 1528 – 1586 CE</div>
                        <a href="/person.php?id=${p.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Courtier Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Abul-Fazl, Akbarnama & Folk Historical Anthologies."
        };
    }

    // 8. BABUR & HUMAYUN INTENT
    if (qLower.includes('babur') || qLower.includes('बाबर') || qLower.includes('panipat')) {
        var p = archiveData.persons.find(x => x.name.toLowerCase().includes('babur'));
        return {
            speechText: "Babur was the founder of the Mughal Empire in India. In 1526, he defeated Ibrahim Lodi at the historic First Battle of Panipat and authored the famous autobiography Baburnama.",
            bodyHtml: `
                <p><strong>जहीरुद्दीन मुहम्मद बाबर (Founder of Mughal Empire, 1483–1530 CE):</strong></p>
                <p style="margin-top: 4px;">बाबर ने 1526 ईस्वी में <strong>पानीपत के प्रथम युद्ध</strong> में दिल्ली सल्तनत के इब्राहिम लोदी को पराजित कर भारत में मुगल साम्राज्य की नींव रखी। वे चगताई तुर्की भाषा में अपनी आत्मकथा <em>बाबरनामा (तुजुक-ए-बाबरी)</em> लिखने वाले महान लेखक व प्रकृति प्रेमी थे।</p>
            `,
            entityCard: p ? `
                <div class="ai-entity-card">
                    <img src="${p.portrait_url}" alt="Babur" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${p.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${p.role} · 1483 – 1530 CE</div>
                        <a href="/person.php?id=${p.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Emperor Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Baburnama (Tuzk-i Baburi), Chagatai Imperial Memoirs."
        };
    }

    if (qLower.includes('humayun') || qLower.includes('हुमायूँ')) {
        var p = archiveData.persons.find(x => x.name.toLowerCase().includes('humayun'));
        var m = archiveData.monuments.find(x => x.name.toLowerCase().includes('humayun'));
        return {
            speechText: "Humayun was the second Mughal Emperor, son of Babur and father of Akbar. After losing his empire to Sher Shah Suri, he reclaimed Delhi in 1555. His tomb in Delhi is an architectural marvel.",
            bodyHtml: `
                <p><strong>नसीरुद्दीन मुहम्मद हुमायूँ (Second Mughal Emperor, 1508–1556 CE):</strong></p>
                <p style="margin-top: 4px;">हुमायूँ बाबर के पुत्र और अकबर के पिता थे। शेरशाह सूरी से हारने के बाद 15 वर्ष निर्वासन में बिताकर उन्होंने 1555 में दिल्ली का सिंहासन पुनः प्राप्त किया। दिल्ली में स्थित <strong>हुमायूँ का मकबरा (Humayun's Tomb)</strong> भारत का प्रथम चारबाग शैली का उद्यान मकबरा था जो आगे चलकर ताजमहल का आधार बना।</p>
            `,
            entityCard: p ? `
                <div class="ai-entity-card">
                    <img src="${p.portrait_url}" alt="Humayun" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${p.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${p.role} · 1508 – 1556 CE</div>
                        <a href="/person.php?id=${p.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Emperor Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Gulbadan Begum, Humayun-nama."
        };
    }

    // 9. SHAH JAHAN INTENT
    if (qLower.includes('shah jahan') || qLower.includes('shahjahan') || qLower.includes('शाहजहाँ') || qLower.includes('khurram')) {
        var p = archiveData.persons.find(x => x.name.toLowerCase().includes('shah jahan'));
        return {
            speechText: "Shah Jahan was the fifth Mughal Emperor, celebrated as the Master Builder of the Mughal Empire. He built the Taj Mahal, the Red Fort of Delhi, the Jama Masjid, and commissioned the Peacock Throne.",
            bodyHtml: `
                <p><strong>मिर्ज़ा खुर्रम - शाहजहाँ (Fifth Mughal Emperor, 1592–1666 CE):</strong></p>
                <p style="margin-top: 4px;">शाहजहाँ के शासनकाल को मुगल वास्तुकला का स्वर्णिम युग (Golden Age of Mughal Architecture) कहा जाता है। उन्होंने आगरा में <strong>ताजमहल</strong>, दिल्ली में <strong>लाल किला</strong> और <strong>जामा मस्जिद</strong> का निर्माण करवाया। उन्होंने प्रसिद्ध मयूर सिंहासन (Peacock Throne / तख्त-ए-ताऊस) का भी निर्माण करवाया था।</p>
            `,
            entityCard: p ? `
                <div class="ai-entity-card">
                    <img src="${p.portrait_url}" alt="Shah Jahan" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${p.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${p.role} · 1592 – 1666 CE</div>
                        <a href="/person.php?id=${p.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Emperor Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Inayat Khan, Shah Jahan-nama."
        };
    }

    // 10. ARTHASHASTRA / CHANAKYA INTENT
    if (qLower.includes('arthashastra') || qLower.includes('chanakya') || qLower.includes('kautilya') || qLower.includes('चाणक्य') || qLower.includes('कौटिल्य') || qLower.includes('अर्थशास्त्र')) {
        var doc = archiveData.manuscripts.find(x => x.title.toLowerCase().includes('arthashastra'));
        var p = archiveData.persons.find(x => x.name.toLowerCase().includes('chanakya') || x.name.toLowerCase().includes('kautilya'));
        return {
            speechText: "The Arthashastra is an ancient Indian treatise on statecraft, economic policy, military strategy, and intelligence systems, authored by Kautilya, also known as Chanakya, mentor to Chandragupta Maurya.",
            bodyHtml: `
                <p><strong>कौटिल्य का अर्थशास्त्र (The Arthashastra of Chanakya, c. 300 BCE):</strong></p>
                <p style="margin-top: 4px;">आचार्य <strong>चाणक्य (विष्णुगुप्त)</strong> द्वारा रचित अर्थशास्त्र राजनीति विज्ञान, अर्थशास्त्र, गुप्तचर व्यवस्था और शासन कला का विश्वप्रसिद्ध संस्कृत ग्रन्थ है। इसमें राज्य के सप्तांग सिद्धांत (7 अंग: स्वामी, अमात्य, जनपद, दुर्ग, कोष, दण्ड, मित्र) और विदेश नीति के मण्डल सिद्धांत का विस्तृत वर्णन है।</p>
            `,
            entityCard: doc ? `
                <div class="ai-entity-card">
                    <div style="font-size: 32px;">📜</div>
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${doc.title}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Author: ${doc.author} · Era: ${doc.era} · Script: ${doc.script}</div>
                        <a href="/manuscript.php?id=${doc.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">Read Codex & Translation ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "R. Shamasastry 1905 Grantha-palm discovery & Oriental Research Institute Mysore."
        };
    }

    // 11. ASHOKA INTENT
    if (qLower.includes('ashoka') || qLower.includes('ashok') || qLower.includes('अशोक') || qLower.includes('maurya') || qLower.includes('dhamma') || qLower.includes('kalinga') || qLower.includes('कलिंग')) {
        var p = archiveData.persons.find(x => x.name.toLowerCase().includes('ashoka'));
        return {
            speechText: "Emperor Ashoka ruled the Mauryan Empire from 268 to 232 BCE. Following the bloody conquest of Kalinga, Ashoka experienced deep remorse, embraced Buddhism, and promulgated the principles of non-violence and social welfare.",
            bodyHtml: `
                <p><strong>चक्रवर्ती सम्राट अशोक मौर्य (Devanampriya Priyadarsi, 304–232 BCE):</strong></p>
                <p style="margin-top: 4px;">मौर्य राजवंश के महान सम्राट अशोक ने लगभग पूरे भारतीय उपमहाद्वीप पर शासन किया। 261 ईसा पूर्व के <strong>कलिंग युद्ध</strong> के नरसंहार को देखकर उनका हृदय द्रवित हो उठा। उन्होंने शस्त्र त्यागकर बौद्ध धर्म और <em>धम्म</em> का मार्ग अपनाया और लोक कल्याण, अहिंसा और नैतिक नियमों को शिलालेखों व लौह/पाषाण स्तम्भों पर उत्कीर्ण कराया। सारनाथ का सिंह शीर्ष आज भारत का <strong>राष्ट्रीय प्रतीक</strong> है।</p>
            `,
            entityCard: p ? `
                <div class="ai-entity-card">
                    <img src="${p.portrait_url}" alt="Ashoka" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${p.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${p.role} · 304 – 232 BCE</div>
                        <a href="/person.php?id=${p.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Emperor Dossier ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Archaeological Survey of India & Ashokan Major Inscriptions."
        };
    }

    // 12. NALANDA INTENT
    if (qLower.includes('nalanda') || qLower.includes('नालंदा') || qLower.includes('university') || qLower.includes('library') || qLower.includes('pustakalaya')) {
        var m = archiveData.monuments.find(x => x.name.toLowerCase().includes('nalanda'));
        return {
            speechText: "Nalanda Mahavihara in Bihar was one of the world's first great residential universities, active from the 5th to the 12th century. Its renowned multi-story library, Dharmaganja, housed over nine million manuscripts.",
            bodyHtml: `
                <p><strong>नालंदा महाविहार (Nalanda University, Bihar):</strong></p>
                <p style="margin-top: 4px;">प्राचीन भारत का नालंदा विश्वविद्यालय 5वीं से 12वीं शताब्दी तक विश्व की सर्वश्रेष्ठ उच्च शिक्षा पीठ थी। यहाँ 10,000 से अधिक विद्यार्थी और 2,000 शिक्षक रहते थे। इसका पुस्तकालय <em>धर्मगंज (रत्नसागर, रत्नोदधि, रत्नरंजक)</em> 90 लाख से अधिक पाण्डुलिपियों का भंडार था, जहाँ चीनी यात्री ह्वेनसांग (Xuanzang) ने अध्ययन किया।</p>
            `,
            entityCard: m ? `
                <div class="ai-entity-card">
                    <img src="${m.image_url}" alt="Nalanda" style="width: 70px; height: 70px; border-radius: 8px; object-fit: cover;">
                    <div style="flex:1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${m.name}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${m.location_text} · ${m.era_label}</div>
                        <a href="/monument.php?id=${m.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Monument Details ➔</a>
                    </div>
                </div>
            ` : null,
            citation: "Xuanzang, Great Tang Records on the Western Regions (Da Tang Xiyu Ji)."
        };
    }

    // 13. MANUSCRIPTS / CODICES QUERY
    if (qLower.includes('manuscript') || qLower.includes('granth') || qLower.includes('codex') || qLower.includes('kitab') || qLower.includes('ग्रन्थ') || qLower.includes('पाण्डुलिपि')) {
        return {
            speechText: "The Bharat Heritage Archive preserves six monumental classical manuscripts including the Ain-i-Akbari, Kautilya's Arthashastra, and Ashokan Major Rock Edicts.",
            bodyHtml: `
                <p><strong>संरक्षित प्राचीन पाण्डुलिपियाँ व ग्रन्थ (Preserved Codices):</strong></p>
                <p style="margin-top: 4px;">अभिलेखागार में निम्नलिखित प्रमुख प्राचीन ग्रन्थ संरक्षित हैं जिन्हें आप Deep Zoom और हिंदी अनुवाद के साथ पढ़ सकते हैं:</p>
                <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 8px;">
                    ${archiveData.manuscripts.map(doc => `
                        <div style="display:flex; justify-content:space-between; align-items:center; background:#f8fafc; padding:8px 12px; border-radius:6px; border:1px solid var(--border-light);">
                            <div><strong>${doc.title}</strong> <span style="font-size:12px; color:var(--text-muted);">(${doc.era || doc.language_id})</span></div>
                            <a href="/manuscript.php?id=${doc.id}" class="btn btn-primary btn-sm" style="font-size:11px; padding:2px 8px;">Read Folio ➔</a>
                        </div>
                    `).join('')}
                </div>
            `,
            citation: "National Mission for Manuscripts Catalog Registry."
        };
    }

    // 14. LOCATION / CITY SEARCH (Agra, Delhi, etc.)
    if (qLower.includes('agra') || qLower.includes('delhi') || qLower.includes('bihar') || qLower.includes('आगरा') || qLower.includes('दिल्ली')) {
        var city = (qLower.includes('agra') || qLower.includes('आगरा')) ? 'Agra' : ((qLower.includes('delhi') || qLower.includes('दिल्ली')) ? 'Delhi' : 'Bihar');
        var matchingMons = archiveData.monuments.filter(x => (x.location_text || '').toLowerCase().includes(city.toLowerCase()));
        return {
            speechText: `Found ${matchingMons.length} historical heritage monuments recorded in ${city}. These include ` + matchingMons.map(x => x.name).join(', ') + '.',
            bodyHtml: `
                <p><strong>Heritage Sites in ${city} (${city} के ऐतिहासिक स्मारक):</strong></p>
                <p style="margin-top: 4px;">इस क्षेत्र में अभिलेखागार में ${matchingMons.length} प्रमुख स्मारक दर्ज हैं:</p>
                <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 6px;">
                    ${matchingMons.map(m => `
                        <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-light);">
                            <div>
                                <strong>${m.name}</strong> <span style="font-size: 12px; color: var(--text-muted);">(${m.built_year_text})</span>
                            </div>
                            <a href="/monument.php?id=${m.id}" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 2px 8px;">Explore ➔</a>
                        </div>
                    `).join('')}
                </div>
            `,
            citation: "Bharat Heritage Geospatial Gazetteer."
        };
    }

    // 15. GENERAL SEARCH ACROSS ALL PERSONS & MONUMENTS
    for (var i = 0; i < archiveData.persons.length; i++) {
        var p = archiveData.persons[i];
        if (qLower.includes(p.name.toLowerCase()) || (p.alt_names && qLower.includes(p.alt_names.toLowerCase()))) {
            return {
                speechText: `${p.name} was ${p.role}. Known in history as ${p.alt_names || p.name}.`,
                bodyHtml: `
                    <p><strong>${p.name}:</strong></p>
                    <p style="margin-top: 4px;">${p.bio || p.role}</p>
                `,
                entityCard: `
                    <div class="ai-entity-card">
                        <img src="${p.portrait_url}" alt="${p.name}" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover;">
                        <div style="flex:1;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${p.name}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">${p.role}</div>
                            <a href="/person.php?id=${p.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Emperor Dossier ➔</a>
                        </div>
                    </div>
                `,
                citation: "Archival Biographical Index."
            };
        }
    }

    for (var j = 0; j < archiveData.monuments.length; j++) {
        var m = archiveData.monuments[j];
        if (qLower.includes(m.name.toLowerCase())) {
            return {
                speechText: `${m.name} is a historic monument located in ${m.location_text}, constructed by ${m.built_by} in ${m.built_year_text}.`,
                bodyHtml: `
                    <p><strong>${m.name}:</strong></p>
                    <p style="margin-top: 4px;">${m.summary || m.history}</p>
                `,
                entityCard: `
                    <div class="ai-entity-card">
                        <img src="${m.image_url}" alt="${m.name}" style="width: 70px; height: 70px; border-radius: 8px; object-fit: cover;">
                        <div style="flex:1;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">${m.name}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">${m.location_text} · Built by ${m.built_by}</div>
                            <a href="/monument.php?id=${m.id}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-top: 4px; display: inline-block;">View Monument Dossier ➔</a>
                        </div>
                    </div>
                `,
                citation: "National Monument Architectural Register."
            };
        }
    }

    // 16. Fallback response with helpful links
    return {
        speechText: `Regarding your query: The archive contains verified records on 13 historical figures including Akbar, Ashoka, Babur, and Chanakya, 7 architectural monuments, and 6 primary manuscripts.`,
        bodyHtml: `
            <p>I have queried the historical repository for <em>"${escapeHtml(q)}"</em>.</p>
            <p style="margin-top: 6px;">आप निम्नलिखित में से किसी पर क्लिक करके सीधे जानकारी देख सकते हैं:</p>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px;">
                <button class="chip-btn" onclick="askPreset('सम्राट अकबर के बारे में बताओ')">👑 Akbar</button>
                <button class="chip-btn" onclick="askPreset('ताजमहल का इतिहास')">🕌 Taj Mahal</button>
                <button class="chip-btn" onclick="askPreset('सम्राट अशोक')">🕊️ Ashoka</button>
                <button class="chip-btn" onclick="askPreset('चाणक्य का अर्थशास्त्र')">📜 Chanakya</button>
                <a href="/search.php?q=${encodeURIComponent(q)}" class="btn btn-primary btn-sm" style="font-size: 12px; border-radius: var(--radius-full);">📜 Search Manuscripts for "${escapeHtml(q)}" ➔</a>
            </div>
        `,
        citation: "Bharat Heritage Database Index."
    };
}
        citation: "Bharat Heritage Database Index."
    };
}

function escapeHtml(text) {
    var d = document.createElement('div');
    d.innerText = text;
    return d.innerHTML;
}

// Text to Speech
var currentUtterance = null;
function speakText(btn, text) {
    if (!('speechSynthesis' in window)) {
        alert("Text-to-Speech not supported on this browser.");
        return;
    }
    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
        btn.querySelector('.speaker-icon').innerText = '🔊';
        btn.classList.remove('speech-pulse');
        return;
    }

    currentUtterance = new SpeechSynthesisUtterance(text);
    currentUtterance.rate = 0.95;
    currentUtterance.pitch = 1.0;
    
    btn.querySelector('.speaker-icon').innerText = '⏹️';
    btn.classList.add('speech-pulse');

    currentUtterance.onend = function() {
        btn.querySelector('.speaker-icon').innerText = '🔊';
        btn.classList.remove('speech-pulse');
    };
    currentUtterance.onerror = function() {
        btn.querySelector('.speaker-icon').innerText = '🔊';
        btn.classList.remove('speech-pulse');
    };

    window.speechSynthesis.speak(currentUtterance);
}

// Voice Input (Speech Recognition)
var recognition = null;
function toggleVoiceInput() {
    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        alert("Voice recognition is not supported in this browser. Please try Google Chrome or Edge.");
        return;
    }

    var vBtn = document.getElementById('voice-btn');
    var vInd = document.getElementById('voice-indicator');

    if (recognition) {
        recognition.stop();
        recognition = null;
        vBtn.style.background = '#f8fafc';
        vInd.style.display = 'none';
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = 'en-IN';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    vBtn.style.background = '#fee2e2';
    vBtn.style.borderColor = '#ef4444';
    vInd.style.display = 'block';

    recognition.onresult = function(event) {
        var speechResult = event.results[0][0].transcript;
        document.getElementById('chat-input').value = speechResult;
        vBtn.style.background = '#f8fafc';
        vBtn.style.borderColor = 'var(--border-light)';
        vInd.style.display = 'none';
        recognition = null;
        handleSend();
    };

    recognition.onerror = function() {
        vBtn.style.background = '#f8fafc';
        vBtn.style.borderColor = 'var(--border-light)';
        vInd.style.display = 'none';
        recognition = null;
    };

    recognition.onend = function() {
        vBtn.style.background = '#f8fafc';
        vBtn.style.borderColor = 'var(--border-light)';
        vInd.style.display = 'none';
        recognition = null;
    };

    recognition.start();
}

function clearChat() {
    var messagesContainer = document.getElementById('chat-messages');
    messagesContainer.innerHTML = `
        <div class="chat-bubble-ai">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span style="font-weight: 700; color: var(--primary); font-size: 13.5px;">Acharya AI (धरोहर विद्वान)</span>
                <span class="badge" style="font-size: 10px; background: var(--accent-gold-light); color: var(--accent-gold);">Epigraphist</span>
            </div>
            <p style="font-size: 14.5px; line-height: 1.6; color: var(--text-body);">
                Chat cleared. How may I assist your historical research today?
            </p>
        </div>
    `;
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

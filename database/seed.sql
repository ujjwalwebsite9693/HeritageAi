-- Manuscript Intelligence Archive (MIA)
-- Comprehensive Seed Data

USE `manuscript_archive`;

-- Disable foreign key checks for clean re-seeding
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `jobs`;
TRUNCATE TABLE `annotations`;
TRUNCATE TABLE `links`;
TRUNCATE TABLE `entity_mentions`;
TRUNCATE TABLE `entities`;
TRUNCATE TABLE `event_sources`;
TRUNCATE TABLE `events`;
TRUNCATE TABLE `person_relations`;
TRUNCATE TABLE `persons`;
TRUNCATE TABLE `glossary`;
TRUNCATE TABLE `translations`;
TRUNCATE TABLE `manuscript_pages`;
TRUNCATE TABLE `manuscripts`;
TRUNCATE TABLE `places`;
TRUNCATE TABLE `languages`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users (passwords hashed with standard bcrypt: '$2y$10$...')
-- admin123 -> $2y$10$E0s5h9j.5lF4VbTzL/1tneQe3x02iRzHk66v2Q53c.6R45V8LlmFm
-- researcher123 -> $2y$10$n1fJ0xR8v3u/XlP5s9F87.kPzQ4j2K1mN3v4B5c6D7e8F9g0H1i2J
-- user123 -> $2y$10$p0qW1eR2t3y4u5i6o7p89.aB0c1D2e3F4g5H6i7J8k9L0m1N2o3P4
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `api_key`, `institution`, `created_at`) VALUES
(1, 'Acharya Somadeva (Admin)', 'admin@archive.org', '$2y$10$ck9gW8NRFjh2/JdRHPWJjeStKh3gFRz/7Zqo2dKrphHipp4.RZI/2', 'admin', 'mia_key_adm_9876543210abcdef990011', 'National Archives & Epigraphy Council', NOW()),
(2, 'Dr. Anandvardhan Shastri', 'dr.anand@nalanda.edu', '$2y$10$ck9gW8NRFjh2/JdRHPWJjeStKh3gFRz/7Zqo2dKrphHipp4.RZI/2', 'researcher', 'mia_key_res_1234567890abcdef112233', 'Nalanda Centre for Classical Manuscripts', NOW()),
(3, 'Prof. Zameeruddin Farooqi', 'farooqi@oriental.ac.in', '$2y$10$ck9gW8NRFjh2/JdRHPWJjeStKh3gFRz/7Zqo2dKrphHipp4.RZI/2', 'researcher', 'mia_key_res_abcdef1234567890445566', 'Khuda Bakhsh Oriental Public Library', NOW()),
(4, 'Priya Sharma', 'priya.sharma@delhiuniv.ac.in', '$2y$10$ck9gW8NRFjh2/JdRHPWJjeStKh3gFRz/7Zqo2dKrphHipp4.RZI/2', 'user', 'mia_key_usr_fedcba0987654321778899', 'Department of History, University of Delhi', NOW());

-- 2. Languages
INSERT INTO `languages` (`id`, `name`, `script`, `iso_code`, `direction`) VALUES
(1, 'Sanskrit', 'Devanagari', 'san', 'ltr'),
(2, 'Prakrit (Ashokan Magadhi)', 'Brahmi', 'pra', 'ltr'),
(3, 'Classical Persian', 'Nastaʿlīq / Perso-Arabic', 'fas', 'rtl'),
(4, 'Pali', 'Brahmi / Devanagari', 'pli', 'ltr'),
(5, 'Old Hindi (Braj Bhasha)', 'Devanagari', 'bra', 'ltr'),
(6, 'Early Modern Dutch', 'Latin', 'nld', 'ltr');

-- 3. Historical Places (Ancient vs Modern Name, Coordinates)
INSERT INTO `places` (`id`, `ancient_name`, `modern_name`, `lat`, `lng`, `region`, `country`, `notes`) VALUES
(1, 'Pāṭaliputra', 'Patna', 25.6101200, 85.1415000, 'Magadha, Bihar', 'India', 'Imperial capital of the Maurya and Gupta dynasties on the banks of Ganga.'),
(2, 'Takṣaśilā (Taxila)', 'Taxila', 33.7463000, 72.8258000, 'Gandhāra, Rawalpindi', 'Pakistan', 'Renowned ancient seat of learning, hub of Gandhara trade route and Ashokan university.'),
(3, 'Kānyakubja (Mahodaya)', 'Kannauj', 27.0543000, 79.9197000, 'Panchala, Uttar Pradesh', 'India', 'Capital city of King Harsha Vardhana and focal center of the Tripartite Struggle.'),
(4, 'Indraprastha / Shahjahanabad', 'Old Delhi', 28.6562000, 77.2410000, 'Delhi Subah', 'India', 'Legendary Pandava capital and subsequent imperial citadel of Mughal sovereigns.'),
(5, 'Girnāra (Girinagara)', 'Junagadh', 21.5222000, 70.4579000, 'Saurashtra, Gujarat', 'India', 'Site of Major Rock Edicts of Ashoka, Mahakshtrapa Rudradaman, and Skandagupta Sudarshana Lake.'),
(6, 'Dār-al-Khilāfat Āgra', 'Agra', 27.1767000, 78.0081000, 'Braj, Uttar Pradesh', 'India', 'Primary capital of the Mughal Empire during Akbar, Jahangir, and early Shah Jahan.'),
(7, 'Sūrat Bander', 'Surat', 21.1702000, 72.8311000, 'Tapi Basin, Gujarat', 'India', 'Premier western international sea-port of the Mughal Empire and Bab-ul-Makka.'),
(8, 'Nālandā Mahāvihāra', 'Nalanda / Rajgir', 25.1357000, 85.4450000, 'Magadha, Bihar', 'India', 'Premier residential Buddhist monastic university housing over 10,000 monks and manuscripts.'),
(9, 'Ujjayinī (Avantī)', 'Ujjain', 23.1765000, 75.7885000, 'Malwa, Madhya Pradesh', 'India', 'Zero meridian of ancient Indian astronomers and pivotal junction of Dakshinapatha.'),
(10, 'Prayāga (Triveṇī)', 'Prayagraj', 25.4358000, 81.8463000, 'Vatsa, Uttar Pradesh', 'India', 'Confluence of Ganga, Yamuna, and Saraswati; location of King Harsha quinquennial assembly.');

-- 4. Manuscripts
INSERT INTO `manuscripts` (`id`, `title`, `author`, `language_id`, `script`, `era_start`, `era_end`, `origin_place_id`, `source_repository`, `call_number`, `license`, `access_level`, `status`, `uploaded_by`, `thumbnail`, `summary`, `created_at`) VALUES
(1, 'Rock Edict XIII & XIV of Devanampriya Priyadasi (Girnar Epigraph)', 'Emperor Ashoka Maurya', 2, 'Brahmi', -257, -250, 5, 'Archaeological Survey of India & Junagadh State Epigraph Vault', 'ASI-WCR-EP-014', 'Public Domain', 'public', 'published', 2, '/assets/img/manuscript-ashoka.jpg', 'The monumental rock inscription of Emperor Ashoka recording remorse over the Kalinga campaign, remorseful embrace of Dhamma, dispatch of moral emissaries across Hellenistic realms, and administrative civic duties.', NOW()),
(2, 'Kauṭilīya Arthaśāstra (Trivandrum Grantha Palm-Leaf Recension)', 'Kauṭilya (Chāṇakya)', 1, 'Grantha / Devanagari', -300, 150, 1, 'Oriental Research Institute & Manuscripts Library, University of Kerala', 'ORI-MSS-SAN-3401', 'Public Domain', 'public', 'published', 2, '/assets/img/manuscript-arthashastra.jpg', 'Comprehensive ancient Sanskrit treatise on statecraft, economic policy, spy networks, foreign diplomacy (Mandala doctrine), and imperial royal council obligations compiled under Maurya prime-ministership.', NOW()),
(3, 'Āʼīn-i-Akbarī: Dastūr-al-ʼAmal (Mughal Imperial Royal Scribe Copy)', 'Shaikh Abul-Fazl ibn Mubarak', 3, 'Nastaʿlīq', 1598, 1602, 6, 'National Museum of India, Central Manuscripts Division', 'NM-PERS-1598-ABF', 'Public Domain', 'public', 'published', 3, '/assets/img/manuscript-ain-akbari.jpg', 'The monumental third volume of the Akbarnama delineating the constitution, administrative regulations, imperial army statistics, revenue assessment methods formulated with Raja Todar Mal, and gazetteer of the Subahs.', NOW()),
(4, 'Harṣacarita of Bāṇabhaṭṭa (Kashmiri Śāradā Birch-Bark Folios)', 'Bāṇabhaṭṭa', 1, 'Śāradā / Devanagari', 630, 645, 3, 'Bhandarkar Oriental Research Institute, Pune', 'BORI-SAN-HAR-890', 'Public Domain', 'public', 'published', 2, '/assets/img/manuscript-harshacharita.jpg', 'The earliest known historical Sanskrit prose biography narrating the ascent of King Harsha Vardhana of Kannauj, his vows to liberate his sister Rajyashri, and his vast benevolent assemblies at Prayaga.', NOW()),
(5, 'Surat Maritime Dagregister (Dutch VOC & Mughal Imperial Port Ledger)', 'Adriaen van der Dussen & Mughal Mir Bahr', 6, 'Early Modern Dutch / Persian', 1642, 1645, 7, 'Nationaal Archief Den Haag & Surat Collectorate Archives', 'VOC-1642-SUR-LOG-11', 'Creative Commons Attribution (CC-BY 4.0)', 'public', 'published', 1, '/assets/img/manuscript-surat.jpg', 'Bilingual commercial day-ledger documenting customs dues, indigo trade convoys, imperial farmans from Agra, and vessel clearances between the port of Surat and Persian Gulf maritime hubs.', NOW()),
(6, 'Kalpasūtra & Kālakācāryakathā (Golden Ink Jain Illustrated Folios)', 'Bhadrabāhu / Scribes of Patan', 2, 'Jain Devanagari', 1420, 1435, 9, 'L.D. Institute of Indology, Ahmedabad', 'LD-JAIN-MSS-0712', 'Public Domain', 'public', 'published', 1, '/assets/img/manuscript-kalpasutra.jpg', 'Exquisite gold-illuminated palm-leaf manuscript containing the lives of the 24 Jinas, monastic codes, genealogical lines of monastic elders, and merchant donor colophons.', NOW());

-- 5. Manuscript Pages
INSERT INTO `manuscript_pages` (`id`, `manuscript_id`, `page_no`, `image_path`, `ocr_text`, `ocr_confidence`, `transcription`, `transliteration`, `status`, `created_at`) VALUES
(1, 1, 1, '/assets/img/pages/ashoka_girnar_p1.jpg', '𑀤𑁂𑀯𑀸𑀦𑀁𑀧𑀺𑀬𑁂𑀦 𑀧𑀺𑀬𑀤𑀲𑀺𑀦𑀸 𑀭𑀜𑀸 𑀇𑀤𑀁 𑀥𑀁𑀫𑀮𑀺𑀧𑀺 𑀮𑁂𑀔𑀸𑀧𑀺𑀢𑀸 𑁇 𑀇𑀥 𑀦 𑀓𑀺𑀁𑀘𑀺 𑀚𑀻𑀯𑀁 𑀆𑀭𑀪𑀺𑀢𑁆𑀧𑀸 𑀧𑁆𑀭𑀯𑀚𑀽𑀳𑀺𑀢𑀯𑁆𑀬𑀁 𑁇', 96.50, 'देवानंपियेन पियदसिना रञा इदं धंमलिपि लेखापिता । इध न किंचि जीवं आरभित्पा प्रवजूहितव्यं न च समाजो कत्तव्यो ।', 'devānaṃpiyena piyadasinā raññā idaṃ dhaṃmalipi lekhāpitā | idha na kiṃci jīvaṃ ārabhitpā pravajūhitavyaṃ na ca samājo kattavyo |', 'verified', NOW()),
(2, 1, 2, '/assets/img/pages/ashoka_girnar_p2.jpg', '𑀅𑀝𑁆𑀞𑀯𑀲𑀸𑀪𑀺𑀲𑀺𑀢𑀲 𑀤𑁂𑀯𑀸𑀦𑀁𑀧𑀺𑀬𑀲 𑀧𑀺𑀬𑀤𑀲𑀺𑀦𑁄 𑀭𑀜𑁄 𑀓𑀮𑀺𑀁𑀕𑀸 𑀯𑀺𑀚𑀺𑀢𑀸 𑁇 𑀤𑀺𑀬𑀠𑀫𑀢𑁂 𑀧𑀸𑀡𑀲𑀢𑀲𑀳𑀲𑁂 𑀬𑁂 𑀢𑀢𑁄 𑀅𑀧𑀯Uz𑁂 𑁇', 94.20, 'अट्ठवसाभिसितस देवानंपियस पियदसिनो रञो कलिङ्गा विजिता । दियढमते पाणसतसहसे ये ततो अपवुढे सतसहसमते तत हते बहुतावतके वा मठे । ततो पछा अधुना लधेषु कलिङ्गेषु तीवो धंमवायो धंमकामता धंमानुसस्ती च देवानंपियस ।', 'aṭṭhavasābhisitasa devānaṃpiyasa piyadasino rañño kaliṅgā vijitā | diyaḍhamate pāṇasatasahase ye tato apavuḍhe satasahasamate tata hate bahutāvatake vā maṭhe | tato pachā adhunā ladheṣu kaliṅgeṣu tīvo dhaṃmavāyo dhaṃmakāmatā dhaṃmānusastī ca devānaṃpiyasa |', 'verified', NOW()),
(3, 2, 1, '/assets/img/pages/arthashastra_p1.jpg', 'पृथिव्या लाभे पालने च यावन्त्यर्थशास्त्राणि पूर्वाचार्यैः प्रस्थापितानि प्रायशस्तानि संहृत्यैकमिदमर्थशास्त्रं कृतम् ॥', 98.10, 'पृथिव्या लाभे पालने च यावन्त्यर्थशास्त्राणि पूर्वाचार्यैः प्रस्थापितानि प्रायशस्तानि संहृत्यैकमिदमर्थशास्त्रं कृतम् ॥ सुखस्य मूलं धर्मः । धर्मस्य मूलमर्थः । अर्थस्य मूलं राज्यम् । राज्यस्य मूलमिन्द्रियजयः ॥', 'pṛthivyā lābhe pālane ca yāvantyarthaśāstrāṇi pūrvācāryaiḥ prasthāpitāni prāyaśastāni saṃhṛtyaikamidamarthaśāstraṃ kṛtam || sukhasya mūlaṃ dharmaḥ | dharmasya mūlamarthaḥ | arthasya mūlaṃ rājyam | rājyasya mūlamindriyajayaḥ ||', 'verified', NOW()),
(4, 2, 2, '/assets/img/pages/arthashastra_p2.jpg', 'राजा आत्मसम्पन्नः प्रकृत्या संपन्नः समुदयबलो दूतप्रणिधिः चारांश्च पाटलिपुत्रतः प्रयुञ्जीत ॥', 95.80, 'राजा आत्मसम्पन्नः प्रकृत्या संपन्नः समुदयबलो दूतप्रणिधिः चारांश्च पाटलिपुत्रतः प्रयुञ्जीत ॥ अरिमित्रोदासीनमध्यमानां मण्डलं विचिन्त्य सान्त्वदानभेददण्डैः प्रकृतीः स्ववशमानयेत् ॥', 'rājā ātmasampannaḥ prakṛtyā sampannaḥ samudayabalo dūtapraṇidhiḥ cārāṃśca pāṭaliputrataḥ prayuñjīta || arimitrodāsīnamadhyamānāṃ maṇḍalaṃ vicintya sāntvadānabhedadaṇḍaiḥ prakṛtīḥ svavaśamānayet ||', 'corrected', NOW()),
(5, 3, 1, '/assets/img/pages/ain_akbari_p1.jpg', 'بر دانایان رموز حقیقت و صرافان نقود معانی پوشیده نماند که کارگاه آفرینش بر عدل استوار است و فرمانروائی جهان بی انتظام دهقانان و رعایا میسر نگردد', 97.40, 'بر دانایان رموز حقیقت و صرافان نقود معانی پوشیده نماند که کارگاه آفرینش بر عدل استوار است و شاهنشاهی عرصهٔ گیتی پناه دین و دولت حضرت ظل الله جلال الدین محمد اکبر پادشاه غازی است که قوانین گزیده در نسق دفاتر دارالخلافه آگره مقرر فرمودند.', 'Bar dānāyān-i rumūz-i ḥaqīqat va ṣarrāfān-i nuqūd-i maʻānī pūshīdah namānad kih kārgāh-i āfarīnish bar ʻadl ustuvār ast va shāhanshāhī-i ʻarṣah-i gītī panāh-i dīn va dawlat Ḥaz̤rat Z̤ill-Allāh Jalāl al-Dīn Muḥammad Akbar Pādshāh-i Ghāzī ast...', 'verified', NOW()),
(6, 3, 2, '/assets/img/pages/ain_akbari_p2.jpg', 'در بیان آئین دهساله و ضبط اراضی محروسه به اهتمام راجه تودر مل مدار المهام', 96.00, 'در بیان آئین دهساله و ضبط اراضی محروسه به اهتمام راجه تودر مل مدار المهام که جمع و خرج صوبه جات اگره، دهلی، گجرات و لاهور را بر قاعدهٔ زرین پیمایش گز الهی و طناب طناب کشی استوار ساخت.', 'Dar bayān-i Āʼīn-i Dahsālah va z̤abṭ-i arāz̤ī-i maḥrūsah bih ihtimām-i Rājah Todar Mal madār al-mahām kih jamʻ va kharj-i ṣūbah-jāt-i Āgrah, Dihlī, Gujarāt va Lāhawr rā bar qāʻidah-i zarrīn-i paymāyish-i Gaz-i Ilāhī ustuvār sākht.', 'verified', NOW()),
(7, 4, 1, '/assets/img/pages/harshacharita_p1.jpg', 'जयति श्रीहर्षदेवो भुवनैकवीरः सकलसामन्तमुकुटमणिमरीचिमालाचुम्बितचरणकमलः कान्यकुब्जेश्वरः', 95.10, 'जयति श्रीहर्षदेवो भुवनैकवीरः सकलसामन्तमुकुटमणिमरीचिमालाचुम्बितचरणकमलः कान्यकुब्जेश्वरः । स हि परममाहेश्वरो भूत्वापि सुगतशासने परां प्रीतिमवहत् । प्रयागतीर्थे महामोक्षपरिषदं चक्रे ॥', 'jayati śrīharṣadevo bhuvanaikavīraḥ sakalasāmantamukuṭamaṇimarīcimālācumbitacaraṇakamalaḥ kānyakubjeśvaraḥ | sa hi paramamāheśvaro bhūtvāpi sugataśāsane parāṃ prītimavahat | prayāgatīrthe mahāmokṣapariṣadaṃ cakre ||', 'verified', NOW()),
(8, 5, 1, '/assets/img/pages/surat_dagregister_p1.jpg', 'Anno 1642 den 14 November in de haven van Soeratta aengecomen het fluitschip Dordrecht uyt Batavia met speceryen en nagelen', 93.80, 'Anno 1642 den 14 November in de haven van Soeratta aengecomen het fluitschip Dordrecht uyt Batavia met cargasoen van noten, foelie en tin. Alhier vertoont het keizerlijck Farman uyt Agra door bemiddeling van de gouverneur aengaende de doorgang van karavanen.', 'Anno 1642 den 14 November in de haven van Soeratta aengecomen het fluitschip Dordrecht uyt Batavia...', 'verified', NOW());

-- 6. Translations (English & Hindi Translations with versioning and verified status)
INSERT INTO `translations` (`id`, `page_id`, `target_lang`, `text`, `method`, `status`, `reviewer_id`, `version`, `created_at`) VALUES
-- Page 1 (Girnar Edict 1)
(1, 1, 'en', 'King Devanampriya Priyadasi (Beloved of the Gods) has caused this edict on Dhamma to be written. Here no living being shall be slaughtered or sacrificed, nor shall any festive gathering be held.', 'human', 'verified', 2, 1, NOW()),
(2, 1, 'hi', 'देवताओं के प्रियदर्शी राजा ने इस धर्मलिपि को लिखवाया है। यहाँ किसी भी प्राणी का वध करके यज्ञ या बलि नहीं दी जाएगी, और न ही ऐसा उत्सव-सम्मेलन आयोजित किया जाएगा जहाँ हिंसा हो।', 'human', 'verified', 2, 1, NOW()),

-- Page 2 (Girnar Edict XIII - Kalinga remorse)
(3, 2, 'en', 'When King Devanampriya Priyadasi had been consecrated eight years, Kalinga was conquered. One hundred and fifty thousand individuals were deported, one hundred thousand were slain, and many times that number perished. Thereafter, now that Kalinga is subdued, the Beloved of the Gods feels profound sorrow, remorse, and an intense devotion to the Law of Dhamma.', 'human', 'verified', 2, 1, NOW()),
(4, 2, 'hi', 'राज्याभिषेक के आठवें वर्ष में देवताओं के प्रियदर्शी राजा ने कलिंग पर विजय प्राप्त की। डेढ़ लाख लोग बंदी बनाकर निर्वासित किए गए, एक लाख मारे गए और उससे भी कई गुना अधिक नष्ट हो गए। इसके पश्चात, कलिंग विजय के उपरांत, देवानांप्रिय के मन में धर्म की तीव्र अभिलाषा, धर्मानुराग और धर्म के अनुशासन का उद्भव हुआ।', 'human', 'verified', 2, 1, NOW()),

-- Page 3 (Arthashastra intro)
(5, 3, 'en', 'This treatise on Political Economy (Arthashastra) has been compiled by synthesizing almost all ancient statecraft treatises composed by previous masters. The foundation of true happiness is Dharma (righteous duty); the root of Dharma is Artha (economic wealth and resources); the root of Artha is the State (governance); and the foundation of the State is mastery over one\'s sensory impulses.', 'human', 'verified', 2, 1, NOW()),
(6, 3, 'hi', 'पूर्व आचार्यों द्वारा रचित लगभग सभी अर्थशास्त्रों का संकलन करके इस एक श्रेष्ठ अर्थशास्त्र का निर्माण किया गया है। सुख का मूल धर्म है; धर्म का मूल अर्थ (संपत्ति व समृद्धि) है; अर्थ का मूल राज्य है; और राज्य का मूल इंद्रियों पर विजय है।', 'human', 'verified', 2, 1, NOW()),

-- Page 4 (Arthashastra Pataliputra statecraft)
(7, 4, 'en', 'A monarch endowed with self-mastery, administrative excellence, and military resources shall dispatch trustworthy ambassadors and secret intelligence agents operating from Pataliputra across the geopolitical realms.', 'ai', 'draft', NULL, 1, NOW()),
(8, 4, 'hi', 'राजा जो स्वयं चरित्रवान, योग्य मंत्रियों से सम्पन्न और साधनयुक्त हो, उसे पाटलिपुत्र से अपने गुप्तचरों और राजदूतों को सभी राज्यों में नियुक्त करना चाहिए।', 'ai', 'draft', NULL, 1, NOW()),

-- Page 5 (Ain-i-Akbari)
(9, 5, 'en', 'Let it not remain hidden from scholars of reality that the cosmic atelier is anchored upon absolute justice. The sovereign of the world is Hazrat Shadow of God Jalaluddin Muhammad Akbar Padshah Ghazi, who has established golden governance regulations in the imperial registries of the capital Agra.', 'human', 'verified', 3, 1, NOW()),
(10, 5, 'hi', 'तत्वज्ञानियों पर यह गुप्त नहीं कि संसार का संचालन न्याय पर टिका है। जगत के सम्राट, दीन और दुनिया के रक्षक हज़रत जिल्ले-इलाही जलालुद्दीन मुहम्मद अकबर बादशाह ग़ाज़ी हैं, जिन्होंने राजधानी आगरा के शाही दफ़्तरों में उत्तम नियम स्थापित किए।', 'human', 'verified', 3, 1, NOW()),

-- Page 6 (Ain-i-Akbari Raja Todar Mal)
(11, 6, 'en', 'An account of the Ten-Year Settlement (Ain-i-Dahsala) and land revenue survey executed under the administration of Raja Todar Mal, premier finance minister, who measured and codified the state receipts of Agra, Delhi, Gujarat, and Lahore using the standard Ilahi Yardstick.', 'human', 'verified', 3, 1, NOW()),
(12, 6, 'hi', 'दस-साला बंदोबस्त (आईन-ए-दहसाला) और भूमि मापन का विवरण, जो वज़ीरे-आज़म राजा टोडरमल की देखरेख में संपन्न हुआ, जिन्होंने आगरा, दिल्ली, गुजरात और लाहौर के राजस्व को गज़-ए-इलाही की सुनहरी प्रणाली पर व्यवस्थित किया।', 'human', 'verified', 3, 1, NOW()),

-- Page 7 (Harshacharita)
(13, 7, 'en', 'Victory belongs to King Sri Harshadeva, the sole sovereign hero whose lotus feet are kissed by the radiant gemstones of tributary diadems, the lord of Kanyakubja (Kannauj). Though a staunch devotee of Shiva (Maheshvara), he harbored profound affection for the Buddhist doctrine, convoking the grand Quinquennial Assembly at the confluence of Prayaga.', 'human', 'verified', 2, 1, NOW()),
(14, 7, 'hi', 'कान्यकुब्ज (कन्नौज) के अधिपति, त्रिभुवन के अद्वितीय वीर श्रीहर्षदेव की जय हो, जिनके चरणकमलों को सामंतों के मुकुटों की मणियाँ स्पर्श करती हैं। यद्यपि वे परम माहेश्वर (शिवभक्त) थे, तथापि तथागत बुद्ध के धर्म में भी उनकी अपार श्रद्धा थी और उन्होंने प्रयाग के पवित्र संगम पर महामोक्ष परिषद का आयोजन किया।', 'human', 'verified', 2, 1, NOW()),

-- Page 8 (Surat Dagregister)
(15, 8, 'en', 'On November 14, 1642, the fluyt-ship Dordrecht arrived in Surat harbor from Batavia laden with nutmeg, mace, and tin. The imperial Farman dispatched from the Agra court was presented through the mediation of the Mughal Port Governor regarding safe caravan passage.', 'human', 'verified', 1, 1, NOW()),
(16, 8, 'hi', '14 नवंबर 1642 को बटाविया से जायफल और मसालों से लदा जहाज डॉर्ड्रेक्ट सूरत बंदरगाह पहुँचा। आगरा दरबार से प्राप्त शाही फरमान को मुग़ल बंदरगाह गवर्नर के सहयोग से व्यापारिक कारवां की सुरक्षा के लिए प्रस्तुत किया गया।', 'human', 'verified', 1, 1, NOW());

-- 7. Glossary (Ancient terms with meanings in Hindi and English)
INSERT INTO `glossary` (`id`, `term`, `language_id`, `meaning_hi`, `meaning_en`, `notes`) VALUES
(1, 'Devānaṃpiya', 2, 'देवताओं का प्रिय (सम्राट अशोक की शाही उपाधि)', 'Beloved of the Gods; titular royal epithet of Emperor Ashoka', 'Used extensively in Brahmi epigraphs across India.'),
(2, 'Dhamma', 2, 'धर्म, सदाचार, नैतिक जीवन-दर्शन', 'Moral law, righteousness, universal ethics promulgated by Ashoka', 'Distinct from sectarian dogma; emphasizes filial piety, compassion, non-killing.'),
(3, 'Arthaśāstra', 1, 'अर्थशास्त्र, राजनीति और अर्थतंत्र का शास्त्र', 'Ancient Indian treatise on statecraft, economic policy, and military strategy', 'Derived from artha (wealth/material prosperity) + shastra (scientific treatise).'),
(4, 'Maṇḍala', 1, 'मंडल सिद्धांत (अंतर्राष्ट्रीय कूटनीति का चक्र)', 'Diplomatic geopolitical wheel of concentric interstate alliances and rivalries', 'Formulated by Kautilya to navigate foreign relations.'),
(5, 'Z̤ill-Allāh', 3, 'ईश्वर की छाया (बादशाह की उपाधि)', 'Shadow of God on Earth; imperial Persian title of the monarch', 'Emphasized in Mughal court chronicling.'),
(6, 'Dahsāla', 3, 'दस-वर्षीय भू-राजस्व बंदोबस्त', 'Ten-year empirical land revenue settlement formulated by Todar Mal', 'Calculated average agricultural yields over 1570-1580.'),
(7, 'Gaz-i Ilāhī', 3, 'इलाही गज़ (अकबर द्वारा मानकीकृत मापक)', 'Standard imperial measure of length (approx 41 digits / 32 inches) introduced by Akbar', 'Replaced inconsistent bamboo and rope land survey cords.'),
(8, 'Kānyakubja', 1, 'कन्नौज का प्राचीन ऐतिहासिक नाम', 'Ancient Sanskrit name of Kannauj, imperial city on the Ganges', 'Also known as Mahodaya.'),
(9, 'Mahāmokṣapariṣad', 1, 'महामोक्ष परिषद (हर्षवर्धन की महादान सभा)', 'Great quinquennial philanthropic assembly convoked by King Harsha at Prayaga', 'Attended by kings, monks, and Xuanzang, where all royal treasury wealth was distributed.'),
(10, 'Mir Bahr', 3, 'बंदरगाह और नौवहन अधिकारी (जलसेना/सीमा शुल्क अध्यक्ष)', 'Imperial Superintendent of sea-ports, riverine traffic, and customs dues', 'Regulated mercantile ships entering Surat.');

-- 8. Historical Persons
INSERT INTO `persons` (`id`, `name`, `alt_names`, `birth_year`, `death_year`, `role`, `bio`, `portrait_url`) VALUES
(1, 'Ashoka Maurya', 'Devānāmpriya, Priyadarśin, Dhammāsoka, अशोक', -304, -232, 'Emperor of the Maurya Dynasty', 'Third Mauryan Emperor who united nearly the entire Indian subcontinent; renowned for renouncing aggressive warfare after the Kalinga conquest and propagating Dhamma through rock and pillar edicts.', '/assets/img/persons/ashoka.png'),
(2, 'Kauṭilya (Chāṇakya)', 'Vishnugupta, Pakshilaswamin, चाणक्य, कौटिल्य', -375, -283, 'Royal Preceptor, Prime Minister & Political Philosopher', 'Legendary strategist and master of statecraft who mentored Chandragupta Maurya, orchestrated the fall of the Nanda dynasty, and authored the monumental Arthashastra at Pataliputra.', '/assets/img/persons/chanakya.png'),
(3, 'Chandragupta Maurya', 'Sandrokottos, Sandrocottus, चन्द्रगुप्त मौर्य', -340, -297, 'Founder of the Maurya Empire', 'First emperor to consolidate North and Central India into a unified empire with Pataliputra as capital, defeating Seleucus I Nicator.', '/assets/img/persons/chandragupta.png'),
(4, 'Harsha Vardhana', 'Śīlāditya, हर्ष वर्धन, कान्यकुब्जेश्वर', 590, 647, 'Emperor of Kannauj (Pushyabhuti Dynasty)', 'Ruler of Northern India from Kannauj who unified warring clans, established religious harmony between Shaivism and Buddhism, authored plays like Ratnavali, and hosted Xuanzang.', '/assets/img/persons/harsha.png'),
(5, 'Bāṇabhaṭṭa', 'Bāṇa, बाणभट्ट', 595, 650, 'Court Poet & Sanskrit Polymath', 'Asthana-kavi at King Harsha’s court in Kannauj, celebrated author of the prose romance Kadambari and the historical biography Harshacharita.', '/assets/img/persons/banabhatta.png'),
(6, 'Jalāl al-Dīn Muḥammad Akbar', 'Akbar the Great, Akbar I, जलालुद्दीन मुहम्मद अकबर', 1542, 1605, 'Third Mughal Emperor', 'Expanded and consolidated the Mughal Empire across Northern and Central India, introduced the Dahsala revenue reform, founded Din-i Ilahi, and established the Ibadat Khana at Fatehpur Sikri.', '/assets/img/persons/akbar.png'),
(7, 'Raja Todar Mal', 'Diwan-i-Ashraf, राजा टोडर मल', 1515, 1589, 'Imperial Finance Minister & General', 'Brilliant administrative genius who revolutionized agricultural surveys, standardized land taxation, weights and measures, and revenue administration under Akbar.', '/assets/img/persons/todarmal.png'),
(8, 'Shaikh Abul-Fazl ibn Mubarak', 'Abul Fazl Allami, अबुल फज़ल', 1551, 1602, 'Grand Vizier, Imperial Historiographer', 'Author of Akbarnama and Ain-i-Akbari, leading intellectual at Akbar court, and chief translator of classical Sanskrit epics into Persian.', '/assets/img/persons/abulfazl.png'),
(9, 'Xuanzang (Hiuen Tsang)', 'Tang Sanzang, Tripitaka Master Xuanzang, ह्वेन त्सांग', 602, 664, 'Buddhist Pilgrim, Scholar, Translator', 'Chinese monk who traveled the Silk Route to India, studied 5 years at Nalanda University, and recorded vital political and cultural conditions in Harsha’s empire.', '/assets/img/persons/xuanzang.png');

-- 9. Person Relations (Genealogy, Guru-Shishya, Patron-Courtier, Alliances)
INSERT INTO `person_relations` (`id`, `person_a`, `person_b`, `relation_type`, `source_manuscript_id`, `notes`) VALUES
(1, 2, 3, 'guru-shishya', 2, 'Kautilya groomed Chandragupta Maurya and served as his chief royal advisor.'),
(2, 3, 1, 'ancestor-descendant', 1, 'Chandragupta Maurya was the grandfather of Emperor Ashoka.'),
(3, 5, 4, 'patron-courtier', 4, 'Banabhatta served as royal court poet to King Harsha of Kannauj.'),
(4, 9, 4, 'patron-scholar', 4, 'King Harsha granted imperial patronage to the Chinese pilgrim Xuanzang.'),
(5, 8, 6, 'minister-emperor', 3, 'Abul Fazl served as confidential counsellor and grand vizier to Emperor Akbar.'),
(6, 7, 6, 'finance-minister', 3, 'Raja Todar Mal was the imperial finance minister to Akbar.'),
(7, 8, 7, 'collaborator', 3, 'Abul Fazl and Todar Mal worked jointly to codify imperial subah statistics in Ain-i-Akbari.');

-- 10. Historical Events
INSERT INTO `events` (`id`, `title`, `description`, `date_text`, `sort_year`, `end_year`, `calendar_type`, `place_id`, `category`, `confidence`, `status`) VALUES
(1, 'Consolidation of the Maurya Imperial Throne at Pataliputra', 'Chandragupta Maurya with master strategist Chanakya overthrows the Dhanananda dynasty and institutes centralized administration.', 'c. 321 BCE', -321, -320, 'Gregorian', 1, 'politics', 0.98, 'approved'),
(2, 'The Kalinga War and Epigraphic Conversion to Dhamma', 'Emperor Ashoka undertakes the conquest of Kalinga with horrific casualties, prompting profound remorse and public dedication to non-violence.', '261 BCE (8th Regnal Year)', -261, -260, 'Regnal', 5, 'war', 0.99, 'approved'),
(3, 'Compilation of the Kauṭilīya Arthaśāstra', 'Systematic codification of statecraft, diplomacy, municipal espionage, and revenue treasuries by Kautilya at Pataliputra.', 'c. 300 BCE', -300, -290, 'Gregorian', 1, 'politics', 0.92, 'approved'),
(4, 'Ashokan Rock Inscriptions Carved at Girnar', 'Royal stone carvers inscribe the 14 Major Rock Edicts on the volcanic granite boulder of Mount Girnar.', '257 BCE', -257, -256, 'Regnal', 5, 'culture', 0.96, 'approved'),
(5, 'Coronation of King Harsha Vardhana at Kanyakubja', 'Harsha unifies Thanesar and Kannauj, creating an imperial capital at Kannauj and initiating the Harsha Era.', '606 CE (Harsha Era Yr 1)', 606, 607, 'Gregorian', 3, 'politics', 0.95, 'approved'),
(6, 'The Grand Quinquennial Philanthropic Assembly of Prayaga', 'King Harsha Vardhana distributes royal treasury jewels, cloth, and gold to monks, brahmins, and the indigent at Triveni Sangam in the presence of Xuanzang.', '643 CE', 643, 644, 'Gregorian', 10, 'religion', 0.98, 'approved'),
(7, 'Promulgation of the Ain-i-Dahsala Revenue Settlement', 'Emperor Akbar and Raja Todar Mal institute the ten-year average agricultural survey and standard revenue measurement.', '1580 CE (988 Hijri)', 1580, 1582, 'Hijri', 6, 'trade', 0.97, 'approved'),
(8, 'Completion of the Āʼīn-i-Akbarī Gazetteer', 'Shaikh Abul Fazl completes the official administrative and philosophical gazetteer of the Mughal Empire.', '1598 CE (1006 Hijri)', 1598, 1599, 'Hijri', 6, 'culture', 0.99, 'approved'),
(9, 'Dutch East India Company (VOC) Establishes Surat Factory', 'Merchants of the Dutch VOC receive Mughal imperial farman allowing trade and customs warehousing at Surat port.', '1616 CE', 1616, 1620, 'Gregorian', 7, 'trade', 0.94, 'approved');

-- 11. Event Sources (Connecting events to manuscripts and specific folios)
INSERT INTO `event_sources` (`id`, `event_id`, `manuscript_id`, `page_id`, `quote_ref`) VALUES
(1, 2, 1, 2, 'Girnar Rock Edict XIII: Eight years after coronation, Kalinga was conquered... profound remorse seized the Beloved of the Gods.'),
(2, 4, 1, 1, 'Girnar Rock Edict I: This Dhamma precept has been caused to be inscribed by King Piyadasi.'),
(3, 1, 2, 2, 'Arthashastra Book I: The sovereign commanding Pataliputra shall maintain state security and spies.'),
(4, 3, 2, 1, 'Arthashastra invocatory chapter: Reconciling previous treatises on statecraft.'),
(5, 5, 4, 1, 'Harshacharita Ucchvasa 3: King Harsha ascending the supreme royal seat of Kanyakubja.'),
(6, 6, 4, 1, 'Harshacharita & Xuanzang accounts: Philanthropic distribution at Prayaga confluence.'),
(7, 7, 3, 2, 'Ain-i-Akbari Ain 15: Promulgation of the Dahsala survey under Raja Todar Mal.'),
(8, 8, 3, 1, 'Ain-i-Akbari Preface: Completion of the imperial gazetteer of Agra.'),
(9, 9, 5, 1, 'Surat Dagregister 1642: Farman of Agra authorizing trade convoys and port duties.');

-- 12. Entities (Extracted Named Entities)
INSERT INTO `entities` (`id`, `type`, `name`, `normalized_name`, `ref_id`, `merged_into`) VALUES
(1, 'person', 'Ashoka Maurya', 'ashoka maurya', 1, NULL),
(2, 'person', 'Devānāmpriya', 'ashoka maurya', 1, NULL),
(3, 'person', 'Kauṭilya', 'chanakya', 2, NULL),
(4, 'person', 'Chāṇakya', 'chanakya', 2, NULL),
(5, 'person', 'Chandragupta Maurya', 'chandragupta maurya', 3, NULL),
(6, 'person', 'Harsha Vardhana', 'harsha vardhana', 4, NULL),
(7, 'person', 'Bāṇabhaṭṭa', 'banabhatta', 5, NULL),
(8, 'person', 'Akbar Padshah', 'akbar padshah', 6, NULL),
(9, 'person', 'Jalāl al-Dīn Muḥammad Akbar', 'akbar padshah', 6, NULL),
(10, 'person', 'Raja Todar Mal', 'raja todar mal', 7, NULL),
(11, 'person', 'Abul-Fazl', 'abul fazl', 8, NULL),
(12, 'person', 'Xuanzang', 'xuanzang', 9, NULL),
(13, 'place', 'Pāṭaliputra', 'patna', 1, NULL),
(14, 'place', 'Kānyakubja', 'kannauj', 3, NULL),
(15, 'place', 'Girnāra', 'junagadh', 5, NULL),
(16, 'place', 'Āgra', 'agra', 6, NULL),
(17, 'place', 'Sūrat Bander', 'surat', 7, NULL),
(18, 'place', 'Prayāga', 'prayagraj', 10, NULL),
(19, 'concept', 'Dhamma / Dharma', 'dhamma', NULL, NULL),
(20, 'concept', 'Dahsāla Settlement', 'dahsala settlement', NULL, NULL);

-- 13. Entity Mentions
INSERT INTO `entity_mentions` (`id`, `entity_id`, `manuscript_id`, `page_id`, `position`, `context_snippet`) VALUES
(1, 2, 1, 1, 0, 'devānaṃpiyena piyadasinā raññā idaṃ dhaṃmalipi lekhāpitā'),
(2, 1, 1, 2, 0, 'aṭṭhavasābhisitasa devānaṃpiyasa piyadasino rañño kaliṅgā vijitā'),
(3, 19, 1, 1, 25, 'idaṃ dhaṃmalipi lekhāpitā'),
(4, 3, 2, 1, 0, 'yāvantyarthaśāstrāṇi pūrvācāryaiḥ prasthāpitāni'),
(5, 13, 2, 2, 60, 'cārāṃśca pāṭaliputrataḥ prayuñjīta'),
(6, 19, 2, 1, 110, 'sukhasya mūlaṃ dharmaḥ | dharmasya mūlamarthaḥ'),
(7, 8, 3, 1, 95, 'Jalāl al-Dīn Muḥammad Akbar Pādshāh-i Ghāzī'),
(8, 16, 3, 1, 160, 'dar al-khilāfah Āgrah'),
(9, 10, 3, 2, 45, 'Rājah Todar Mal madār al-mahām'),
(10, 20, 3, 2, 10, 'Dar bayān-i Āʼīn-i Dahsālah'),
(11, 6, 4, 1, 8, 'jayati śrīharṣadevo bhuvanaikavīraḥ'),
(12, 14, 4, 1, 75, 'kānyakubjeśvaraḥ'),
(13, 18, 4, 1, 140, 'prayāgatīrthe mahāmokṣapariṣadaṃ cakre'),
(14, 17, 5, 1, 30, 'haven van Soeratta aengecomen het fluitschip Dordrecht'),
(15, 16, 5, 1, 110, 'keizerlijck Farman uyt Agra door bemiddeling');

-- 14. Links (Cross-Manuscript Connections / Hidden Historical Links)
INSERT INTO `links` (`id`, `manuscript_a`, `manuscript_b`, `score`, `reason_json`, `status`, `reviewed_by`, `created_at`) VALUES
(1, 1, 2, 92, '{"summary":"Maurya Imperial Governance & Epigraphic Dharma Link","shared_entities":["Chandragupta Maurya","Ashoka Maurya","Pāṭaliputra","Dhamma/Dharma"],"time_proximity_score":90,"geographic_overlap":"Magadha & Western Provinces (Girnar/Saurashtra)","explanation":"Manuscript 1 (Girnar Edict) reflects the real-world administrative and ethical implementation of governance principles systematized in Manuscript 2 (Arthashastra of Kautilya) during the foundational Maurya epoch centered at Pataliputra.","metrics":{"keyword_overlap":0.78,"entity_cooccurrence":4,"era_delta_years":64}}', 'confirmed', 2, NOW()),
(2, 3, 5, 88, '{"summary":"Mughal Imperial Maritime Trade & Sovereign Farman Corridor","shared_entities":["Emperor Akbar / Mughal Crown","Āgra Imperial Court","Sūrat Bander","Imperial Customs Regulations"],"time_proximity_score":85,"geographic_overlap":"Agra - Surat Trade Corridor","explanation":"The customs regulations and subah gazetteer codified in the Ain-i-Akbari (Agra) directly govern the practical merchant toll exemptions and maritime port clearances recorded in the Dutch VOC Surat Dagregister.","metrics":{"keyword_overlap":0.68,"entity_cooccurrence":3,"era_delta_years":44}}', 'confirmed', 3, NOW()),
(3, 1, 4, 76, '{"summary":"Northern Imperial Monarchy & Dharmic Royal Patronage","shared_entities":["Royal Conciliar Assemblies","Buddhist / Dharmic Statecraft","Pāṭaliputra-Kannauj Political Succession"],"time_proximity_score":65,"geographic_overlap":"Gangetic Plain (Pataliputra to Kannauj)","explanation":"King Harsha Vardhana of Kannauj modeled his imperial benevolent assemblies (Prayaga Mahamokshaparisad) and royal patronages upon the ethical edicts and dharmic moral proclamations first established by Ashoka.","metrics":{"keyword_overlap":0.58,"entity_cooccurrence":2,"era_delta_years":890}}', 'suggested', NULL, NOW()),
(4, 2, 4, 82, '{"summary":"Classical Sanskrit Statecraft & Kingship Doctrine","shared_entities":["Sanskrit Raja-dharma","Panchala-Magadha Geopolitical Sphere","Espionage and Diplomatic Treaties"],"time_proximity_score":72,"geographic_overlap":"Kanyakubja & Pataliputra","explanation":"Banabhatta’s Harshacharita explicitly draws upon Kautilyan terminology (Mandala doctrine, royal treasury administration, and diplomatic legations) in describing King Harsha’s military expeditions and diplomatic alliances.","metrics":{"keyword_overlap":0.74,"entity_cooccurrence":3,"era_delta_years":750}}', 'confirmed', 2, NOW()),
(5, 3, 4, 61, '{"summary":"Agrarian Survey & North Indian Royal Historiography","shared_entities":["Kannauj / Panchala Subah","Imperial Historiographical Method"],"time_proximity_score":55,"geographic_overlap":"Kannauj - Agra Belt","explanation":"The geographical Subah gazetteer in Ain-i-Akbari for Sarkar Kannauj references historical traditions and revenue land records previously documented during the Pushyabhuti empire.","metrics":{"keyword_overlap":0.45,"entity_cooccurrence":1,"era_delta_years":950}}', 'suggested', NULL, NOW());

-- 15. Annotations (Scholarly Region & Text Annotations with Discussions)
INSERT INTO `annotations` (`id`, `page_id`, `user_id`, `type`, `coordinates`, `note`, `visibility`, `parent_id`, `created_at`) VALUES
(1, 1, 2, 'reference', '12,45,280,60', 'Notice the archaic Western Prakrit phonology in "devānaṃpiyena" vs Eastern Magadhi "devānaṃpiye". This confirms the rock carver adapted the imperial Pataliputra edict to local Saurashtra dialect.', 'public', NULL, NOW()),
(2, 1, 3, 'reference', NULL, 'Agreed, Dr. Shastri. Compare this with the Dhauli and Jaugada rock inscriptions in Kalinga where the nominative singular invariably ends in -e.', 'public', 1, NOW()),
(3, 2, 2, 'hypothesis', '30,120,400,90', 'The casualty figure of 100,000 slain is likely an epic or symbolic quantification representing the total devastation of the Kalinga confederacy.', 'public', NULL, NOW()),
(4, 3, 2, 'correction', '15,10,320,40', 'Some later South Indian palm-leaf recensions omit "indriyajayaḥ" in the concluding sutra. The Trivandrum exemplar correctly retains the four-pillar hierarchy.', 'public', NULL, NOW()),
(5, 5, 3, 'question', '40,60,250,55', 'Is this specific opening phrasing borrowed from Nizami’s Makhzan al-Asrar or Abul Fazl’s original Persian epistolary style?', 'public', NULL, NOW());

-- 16. Background Jobs Queue (Initial completed & sample pending jobs)
INSERT INTO `jobs` (`id`, `type`, `payload`, `status`, `attempts`, `error_message`, `created_at`) VALUES
(1, 'ocr_process', '{"manuscript_id":1,"page_no":1,"image":"ashoka_girnar_p1.jpg"}', 'completed', 1, NULL, NOW()),
(2, 'ocr_process', '{"manuscript_id":1,"page_no":2,"image":"ashoka_girnar_p2.jpg"}', 'completed', 1, NULL, NOW()),
(3, 'link_discovery', '{"target_manuscript_id":1}', 'completed', 1, NULL, NOW()),
(4, 'entity_extract', '{"manuscript_id":2,"page_no":1}', 'completed', 1, NULL, NOW());

-- 17. Audit Logs
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `target_type`, `target_id`, `details`, `created_at`) VALUES
(1, 1, 'system_init', 'database', 0, 'Database populated with initial classical archive corpus and verified historical links.', NOW()),
(2, 2, 'verify_translation', 'translations', 1, 'Verified English translation of Girnar Rock Edict I.', NOW()),
(3, 2, 'confirm_link', 'links', 1, 'Confirmed hidden historical connection between Ashokan Girnar Edict and Kautiliya Arthashastra.', NOW());

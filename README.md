# Manuscript Intelligence Archive (MIA)

> **A production-ready digital humanities platform for ancient manuscript epigraphy, multi-script transcription, side-by-side translation, historical GIS cartography, chronological timelines, and automated hidden link discovery.**

Built in exact accordance with the **Manuscript Archive Blueprint**:
- **Backend:** PHP 8.x (MVC architecture, PDO prepared statements, CSRF protection, secure session management, background jobs queue)
- **Database:** MySQL 8.x (InnoDB, `utf8mb4_unicode_ci`, FULLTEXT indexes across metadata, transcriptions, and translations)
- **Frontend:** HTML5, CSS3 (Editorial archival design system, Google Fonts Cinzel & Noto Sans Devanagari, Responsive Flexbox/Grid), Vanilla JavaScript
- **Libraries (Free / Open-Source):**
  - **Leaflet.js & OpenStreetMap:** Historical GIS mapping, ancient vs modern settlements, marching & trade routes, era time-slider
  - **vis-timeline:** Zoomable horizontal timeline with Vikram Samvat, Śaka, Hijri, and Gregorian calendar normalizations
  - **vis-network:** Interactive knowledge graph for cross-manuscript link discovery and genealogical relationship trees
  - **OpenSeadragon:** Deep-zoom high-resolution manuscript facsimile viewer with page navigation

---

## 🌟 Key Features

### 1. Rare Documents Searchable Digital Archive
- **Multi-format Ingestion:** Upload scans in JPG, PNG, TIFF, or PDF with comprehensive codicological metadata (title, author, script, era, origin center, repository vault, call number, copyright license).
- **Intelligent Search:** MySQL FULLTEXT indexing across titles, authors, transcriptions, and translations, with phonetic Soundex and Levenshtein typo tolerance.
- **Deep Zoom Viewer:** Inspect high-resolution digitized folios with pan, zoom, and fullscreen capabilities via OpenSeadragon.

### 2. Manuscripts to Events Timeline
- **Event Extraction:** Chronological milestones cataloged with exact dates, approximate ranges ("c. 1200 CE"), and regnal years.
- **Multi-Calendar Normalization:** Automatic conversion between Gregorian, Vikram Samvat ($VS = CE + 57$), Śaka Era ($Śaka = CE - 78$), and Hijri ($AH$) calendars to a unified integer sort year.
- **Interactive vis-timeline:** Zoomable horizontal timeline with category filters (politics, war, religion, trade, culture) and citation links back to source folios.

### 3. Ancient Text to Modern Language Translation & Glossary
- **Side-by-Side Synchronized View:** 3-column synchronized inspection displaying original script, Romanized IAST transliteration, and modern English / Hindi translations.
- **Translation Engine:** External AI translation integration (Gemini API via cURL with `.env` secret) + database caching to avoid redundant API costs.
- **Verification Workflow:** Draft machine translation $\rightarrow$ peer editorial review $\rightarrow$ "Verified" certification with version history.
- **Interactive Glossary:** Hover tooltips for ancient terms (e.g. *Devānaṃpiya*, *Dhamma*, *Arthaśāstra*, *Gaz-i Ilāhī*) with Hindi and English definitions.

### 4. Historical Places GIS Cartography (Leaflet.js)
- **Ancient vs Modern Toponymy:** Geographical coordinates mapping ancient centers (Pāṭaliputra $\rightarrow$ Patna, Takṣaśilā $\rightarrow$ Taxila, Kānyakubja $\rightarrow$ Kannauj, Ujjayinī $\rightarrow$ Ujjain, Girnāra $\rightarrow$ Junagadh).
- **Historical Arterial Routes:** Polylines rendering the Northern Uttarāpatha Highway, Mughal Agra-Surat Trade Corridor, and Ashokan Epigraphic routes.
- **Dossier Drawer:** Click any site marker to view connected manuscripts, historical events, and regional records.

### 5. Hidden Historical Link Discovery Engine *(Flagship Feature)*
- **Multi-Factor Correlation Scoring (0–100%):**
  - **Named Entity Co-occurrence (45%):** Phonetic Soundex and Levenshtein matching across persons, places, and concepts.
  - **Temporal Proximity (25%):** Year intervals weighted based on historical dynastic epochs.
  - **Geographic Proximity (20%):** Proximity of provenance centers and imperial territories.
  - **Textual Overlap (10%):** Token-level Jaccard similarity.
- **Scholarly Explanation:** Generates JSON explanation detailing why documents are connected (shared actors, same battle/treaty, overlapping administration).
- **Researcher Review Queue:** Accept, reject, or comment on candidate links. Confirmed links are dynamically integrated into the knowledge base.
- **Connection Graph:** Physics-simulated vis-network visualization showing clusters, central nodes, and isolated documents.

### 6. OCR / HTR Workspace & Entity Resolution
- **Split-Screen Workspace:** High-resolution scan on the left, line-by-line editorial transcription editor on the right with OCR confidence scoring.
- **Entity Resolution & Deduplication:** Algorithmic detection of alternate spellings (e.g., *Akbar / Akbarr / अकबर / اکبر*) with a side-by-side merge interface.

### 7. Public REST API & Academic Export
- **JSON REST API:** Documented endpoints for `/api/manuscripts`, `/api/events`, `/api/places`, `/api/persons`, `/api/links`, `/api/graph`.
- **API Key Authentication & Rate Limiting:** Managed via user profiles with `X-API-KEY` headers.
- **Multi-Format Export:**
  - Events as **CSV** (`/api/export/events-csv/0`)
  - Places and routes as **GeoJSON** (`/api/export/places-geojson/0`)
  - Academic citations in **APA 7th, MLA 9th, and Chicago 17th** formats.

---

## 📂 Project Directory Structure

```
/manuscript-archive (D:\New folder)
├── /public                     # Web root
│   ├── index.php               # Front controller & router
│   ├── .htaccess               # Apache rewrite rules
│   ├── /assets
│   │   ├── /css/style.css      # Archival design system
│   │   ├── /js/app.js          # Interactive client scripts
│   │   └── /img                # Manuscript folios & portraits
│   └── /api
│       └── index.php           # Direct REST API entry
├── /app
│   ├── /controllers            # MVC controllers (Home, Archive, Timeline, Map, Graph, etc.)
│   ├── /models                 # Models (Manuscript, Event, Place, Person, Link, Annotation)
│   ├── /services               # SearchService, OcrService, TranslationService, LinkDiscoveryService, EntityService, ExportService
│   ├── /views                  # Clean responsive views & layouts
│   └── /helpers                # Auth, Csrf, Calendar, Response
├── /config                     # app.php, db.php
├── /storage                    # originals, derivatives, uploads
├── /cron                       # worker.php (queue runner), link_scan.php (batch discovery)
├── /database                   # schema.sql, seed.sql, migrate.php, generate_assets.php
├── /docs                       # ARCHITECTURE.md, API_REFERENCE.md, DEPLOYMENT_GUIDE.md
├── /tests                      # Automated test suite (test_flow.php)
└── .env                        # Environment secrets & database credentials
```

---

## 🚀 Quick Start Guide

### 1. Database Setup
Ensure MySQL 8.x is running, then run the automated migration:
```bash
php database/migrate.php
```

### 2. Generate Facsimile Image Assets
Generate high-resolution sample folios, stamps, and seals:
```bash
php database/generate_assets.php
```

### 3. Run Automated Test Suite
Verify that all 15 core architectural assertions pass:
```bash
php tests/test_flow.php
```

### 4. Start the Web Server
Launch the development server:
```bash
php -S 127.0.0.1:8000 -t public public/index.php
```
Open your browser and navigate to **[http://127.0.0.1:8000](http://127.0.0.1:8000)**.

---

## 👥 Default Seeded Accounts

| Role | Email | Password | Permissions |
|---|---|---|---|
| **Administrator** | `admin@archive.org` | `admin123` | Full access, user management, queue worker, system settings |
| **Researcher** | `dr.anand@nalanda.edu` | `researcher123` | Upload scans, transcription editor, link review queue, entity merge |
| **Researcher** | `farooqi@oriental.ac.in` | `researcher123` | Classical Persian recensions, translation verification |
| **User** | `priya.sharma@delhiuniv.ac.in` | `user123` | Annotations, discussions, personal API key generation |

---

## ⚙️ Background Jobs & Cron Execution

Process pending OCR and translation queue jobs via CLI:
```bash
php cron/worker.php
```

Trigger an ad-hoc corpus-wide hidden link discovery scan:
```bash
php cron/link_scan.php
```


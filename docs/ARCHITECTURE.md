# Manuscript Intelligence Archive (MIA) — System Architecture

## 1. Architectural Overview
Manuscript Intelligence Archive (MIA) is a production-grade digital humanities and computational epigraphy platform designed to ingest high-resolution ancient manuscript scans, transcribe and transliterate text, provide side-by-side synchronized modern translations, map historical locations via GIS, construct chronological timelines across diverse ancient calendars, and algorithmically discover unnoticed historical connections across disparate codices.

```
Browser (HTML5, CSS3, Vanilla JS)
 │ Leaflet.js (GIS Map)
 │ vis-timeline (Horizontal Timeline)
 │ vis-network (Connection Graph & Genealogy)
 │ OpenSeadragon (Deep Zoom Facsimile Viewer)
 ▼
PHP 8.x MVC Backend
 ├── Public Front Controller (public/index.php)
 ├── Authentication & Role-Based Access Control (visitor, user, researcher, admin)
 ├── Service Layer
 │    ├── SearchService (MySQL 8 FULLTEXT + Soundex / Levenshtein Typo Tolerance)
 │    ├── LinkDiscoveryService (Entity Extraction, TF-IDF, Temporal/Spatial Scoring)
 │    ├── TranslationService (Gemini API cURL, DB Caching, Glossary Hover Engine)
 │    ├── OcrService (Palaeographic Text & HTR Pipeline)
 │    ├── EntityService (Fuzzy Resolution & Duplicate Merge)
 │    └── ExportService (APA/MLA/Chicago Citations, CSV, GeoJSON)
 └── Database Layer (MySQL 8.x InnoDB, UTF8mb4, Prepared Statements)
```

## 2. Unnoticed Historical Link Discovery Engine
The platform's flagship feature evaluates relationships between any two manuscripts $M_A$ and $M_B$ through a four-stage composite scoring algorithm (0–100%):
1. **Named Entity Co-occurrence ($45\%$ Weight):**
   Matches tagged entities (persons, places, organizations, concepts) between documents using phonetic equivalence (`soundex()`) and Levenshtein token similarity.
2. **Temporal Proximity ($25\%$ Weight):**
   Evaluates the difference in normalized start years $|\text{Era}_A - \text{Era}_B|$:
   - $\le 50\text{ years} \rightarrow 95\text{ pts}$
   - $\le 150\text{ years} \rightarrow 80\text{ pts}$
   - $\le 300\text{ years} \rightarrow 65\text{ pts}$
   - $\le 600\text{ years} \rightarrow 45\text{ pts}$
3. **Geographic Realm Overlap ($20\%$ Weight):**
   Calculates proximity of origin centers (e.g. shared capital city, or shared historical geopolitical region like Magadha, Gandhara, or Braj).
4. **Lexical & Topical Overlap ($10\%$ Weight):**
   Token-level Jaccard similarity across summaries and transcriptions filtering out multilingual stop-words.

## 3. Background Jobs & Queue System
Resource-intensive pipelines (Palaeographic OCR decipherment, external AI translation API calls, and corpus-wide link discovery) are decoupled from HTTP request cycles:
- Tasks are inserted into the `jobs` database table with JSON payloads.
- The CLI worker (`cron/worker.php`) or the web worker endpoint (`/cron/worker`) processes pending jobs in FIFO batches with retry tracking and error recording.

## 4. Security & Hardening
- **SQL Injection Prevention:** 100% prepared statements via PDO with emulation disabled (`PDO::ATTR_EMULATE_PREPARES => false`).
- **CSRF Protection:** Cryptographically secure random tokens (`Csrf::validate()`) enforced on all state-changing POST/PUT requests.
- **XSS Defense:** Full output sanitization with `htmlspecialchars()` using UTF-8 flags.
- **Access Control:** Role-Based Access Control (`Auth::requireRole()`) protecting editorial, administrative, and review endpoints.
- **Password Security:** One-way salted hashing via native PHP `password_hash()` (Bcrypt).
- **HTTP Security Headers:** `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`.


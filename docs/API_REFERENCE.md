# Manuscript Intelligence Archive — REST API Reference

All requests accept standard query parameters or JSON bodies. Authenticate with header `X-API-KEY: <your_key>` or query string `?api_key=<your_key>`.

---

## 1. Manuscripts

### `GET /api/manuscripts`
Search and filter manuscripts.
- **Parameters:**
  - `q` (string): Keyword or full-text query
  - `lang` (int): Language ID filter
  - `place` (int): Origin place ID filter
  - `era_from` (int): Normalized year start
  - `era_to` (int): Normalized year end
  - `limit` (int): Max records (default: 20)
  - `offset` (int): Pagination offset

### `GET /api/manuscripts/{id}`
Returns complete codicological metadata, all folios, transcriptions, translations, and annotations for the specified codex.

### `GET /api/manuscripts/{id}/pages`
Returns list of digitized pages/folios for manuscript `{id}`.

### `POST /api/manuscripts`
Upload new manuscript (requires `researcher` or `admin` role).

---

## 2. Pages, Transcriptions & Translations

### `PUT /api/pages/{id}/transcription`
Update the epigraphical transcription for page `{id}`.
- **Payload:**
```json
{
  "transcription": "देवानंपियेन पियदसिना रञा..."
}
```

### `POST /api/pages/{id}/translate`
Generate or retrieve cached AI translation for page `{id}`.
- **Payload:**
```json
{
  "target_lang": "en"
}
```

### `PUT /api/translations/{id}/verify`
Certify a draft translation as verified.
- **Payload:**
```json
{
  "text": "Corrected translation...",
  "status": "verified"
}
```

---

## 3. Events & Chronology

### `GET /api/events`
Query cataloged historical events.
- **Parameters:**
  - `category` (string): `war`, `treaty`, `trade`, `religion`, `politics`, `culture`, `science`
  - `place` (int): Origin place ID
  - `from` (int): Minimum sort year
  - `to` (int): Maximum sort year

### `POST /api/events`
Create a new chronological milestone.

---

## 4. Historical Places (GIS)

### `GET /api/places`
Retrieve list of ancient sites with geographic coordinates (`lat`, `lng`), modern names, and linked codices.

### `GET /api/places/{id}`
Retrieve complete historical dossier, events, and manuscripts originating from this location.

---

## 5. Connections Graph & Link Discovery

### `GET /api/graph`
Returns nodes and edges for network graph visualization.
- **Parameters:**
  - `root` (int): Focus node ID
  - `depth` (int): Traversal depth (default: 2)

### `GET /api/links`
Returns candidate and confirmed cross-manuscript connections with similarity metrics and explanation JSON.

### `PUT /api/links/{id}/review`
Review a link status (`confirmed` or `rejected`).

---

## 6. Entity Resolution

### `GET /api/entities/duplicates`
Returns algorithmic candidate duplicate entity pairs with similarity scores.

### `POST /api/entities/merge`
Merge duplicate entity B into canonical entity A.

---

## 7. Data Exports

- `GET /api/export/events-csv/0` — Download CSV of approved chronological events.
- `GET /api/export/places-geojson/0` — Download GeoJSON feature collection of historical places.
- `GET /api/export/citations/{manuscript_id}` — Generate formatted APA, MLA, and Chicago citations.


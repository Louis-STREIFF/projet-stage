
Airtable Form Plugin
====================

A lightweight custom WordPress plugin to connect to an Airtable database, manage artist records, and dynamically display them with search and filter capabilities.

🔧 Features
-----------
- Connects to Airtable using API keys stored in WordPress settings.
- AJAX-powered artist search with location, format, keyword, and sort filters.
- Custom artist profile pages generated dynamically using slugs.
- Custom admin settings page to store your API credentials.
- Clean and modular shortcode architecture.
- Fully styled components with CSS and JS assets.

⚙️ Shortcodes
-------------
[search]
    Displays the main search form with:
    - Google Maps autocomplete for location.
    - Multiple checkbox formats.
    - Text input for bio keywords.
    - Dropdown for sorting.

[search_results]
    Used to display the filtered artist results below the search form (AJAX-based, works with [search]).

[add_artist_form]
    Shows a form to submit a new artist to the "Waiting" table in Airtable.

[artist_profile]
    Used on the dynamic artist page template to show:
    - Artist photo
    - Name and location
    - Format tags
    - Biography
    - Back & contact buttons

[artist_by_format slug="format-name"]
    Dynamically lists all public artists offering the specified format.
    Example:
    [artist_by_format slug="spectacle"]
    Will list all artists with the "Spectacle" format.

🛠 Setup
--------
1. Upload the plugin folder into /wp-content/plugins/airtable-form.
2. Activate the plugin via the WordPress dashboard.
3. Go to Settings → API Mon Plugin and enter:
   - Airtable API Key
   - Base ID
   - Table name
   - Google Maps API Key

📁 Folder structure
-------------------
airtable-form/
│
├── Add/                    → Form to submit artists
├── Search/                 → Search form and JS/AJAX logic
├── Shortcodes/             → All custom shortcode logic
├── Templates/              → Display templates (single artist, results)
├── airtable.php            → Airtable API communication
├── extension.php           → Main plugin file
├── style.css               → Styling (loaded conditionally)
└── .env                    → [Git-ignored] API keys (for local dev)

🧪 Dev notes
------------
- All shortcodes are organized in /Shortcodes/.
- Artist data is fetched with filters directly from Airtable using getArtistsFromAirtable().
- .env is excluded from Git. Store local API credentials there for security.

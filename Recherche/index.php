<?php

require plugin_dir_path(__FILE__) . '../config.php';
require_once plugin_dir_path(__FILE__) . '../airtable.php';

$artists = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName);
?>

<div class="artist-search-container">
    <h1>Artist Filter</h1>

    <form action="<?php echo site_url('/resultat-recherche/'); ?>" method="GET">
    <div class="form-group">
            <label for="lieu">Lieu :</label>
            <input type="text" id="lieu" name="lieu" value="<?php echo esc_attr($_GET['lieu'] ?? ''); ?>" autocomplete="off">
            <input type="hidden" id="lat" name="lat">
            <input type="hidden" id="lng" name="lng">
        </div>

        <div class="form-group">
            <label for="tolerance">Tolérance (km) :</label>
            <input type="range" id="tolerance" name="tolerance" min="1" max="100" step="1" value="1">
            <span id="toleranceValue">1</span> km
        </div>

        <div class="form-group">
            <label for="format">Format :</label>
            <select id="format" name="selectedFormats[]">
                <option value="" disabled selected>Select a format</option>
                <?php foreach (array_unique(call_user_func(function() use ($artists) {
                    $formats = [];
                    foreach ($artists as $rec) {
                        if (!empty($rec['fields']['Type']) && is_array($rec['fields']['Type'])) {
                            $formats = array_merge($formats, $rec['fields']['Type']);
                        }
                    }
                    return $formats;
                })) as $fmt): ?>
                    <option value="<?php echo esc_attr($fmt); ?>"><?php echo esc_html($fmt); ?></option>
                <?php endforeach; ?>
            </select>
            <div id="selected-formats"></div>
            <input type="hidden" id="selectedFormatsInput" name="selectedFormats">
        </div>

        <div class="form-group">
            <label for="bio">Keywords in bio :</label>
            <input type="text" id="bio" name="bio" value="<?php echo esc_attr($_GET['bio'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Rechercher</button>
        </div>
    </form>

</div>

<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js($MapAPIKey); ?>&libraries=places&callback=initAutocomplete">
</script>


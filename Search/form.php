<?php

require plugin_dir_path(__FILE__) . '../config.php';
require_once plugin_dir_path(__FILE__) . '../airtable.php';

$artists = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName);
?>

<h1>Nos Artistes</h1>
<div class="artist-search-container">
    <form action="<?php echo site_url('/resultat-recherche/'); ?>" method="GET" id="search-form">
        <div class="form-fields">
            <div class="form-group">
                <label for="lieu">Localisation :</label>
                <input type="text" id="lieu" name="lieu"
                       value="<?php echo esc_attr($_GET['lieu'] ?? ''); ?>"
                       autocomplete="off"
                       class="input-shadow">
                <input type="hidden" id="lat" name="lat">
                <input type="hidden" id="lng" name="lng">
            </div>

<!--            <div class="form-group">-->
<!--                <label for="tolerance">Distance :</label>-->
<!--                <input type="range" id="tolerance" name="tolerance"-->
<!--                       min="1" max="100" step="1" value="1"-->
<!--                       class="input-shadow">-->
<!--                <div class="distance-value">-->
<!--                    <span id="toleranceValue">1</span> <span>km</span>-->
<!--                </div>-->
<!--            </div>-->

            <div class="form-group">
                <label for="format">Format :</label>
                <select id="format" name="selectedFormats[]" class="input-shadow">
                    <option value="" disabled selected>Selectionnez un format</option>
                    <?php
                    $allProductServiceIds = [];
                    foreach ($artists as $rec) {
                        if (!empty($rec['fields']['Services_Type']) && is_array($rec['fields']['Services_Type'])) {
                            $allProductServiceIds = array_merge($allProductServiceIds, $rec['fields']['Services_Type']);
                        }
                    }
                    $allProductServiceIds = array_unique($allProductServiceIds);
                    $formatNames = getProductServiceNames($AirtableAPIKey, $BaseID, $allProductServiceIds);
                    foreach ($formatNames as $name): ?>
                        <option value="<?php echo esc_attr($name); ?>"><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>

                </select>
                <div id="selected-formats"></div>
                <input type="hidden" id="selectedFormatsInput" name="selectedFormats">
            </div>

            <div class="form-group">
                <label for="bio">Cherchez un nom ou un mot clé:</label>
                <input type="text" id="bio" name="bio"
                       value="<?php echo esc_attr($_GET['bio'] ?? ''); ?>"
                       class="input-shadow">
            </div>
        </div>
    </form>
</div>
<button type="submit" class="button" form="search-form">Rechercher</button>


<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js($MapAPIKey); ?>&libraries=places&callback=initAutocomplete">
</script>



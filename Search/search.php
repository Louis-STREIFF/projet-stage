<?php
if (!defined('ABSPATH')) {
    exit;
}

require plugin_dir_path(__FILE__) . '../config.php';
require_once plugin_dir_path(__FILE__) . '../airtable.php';

$latitude        = (!empty($_GET['lat'])) ? floatval($_GET['lat']) : null;
$longitude       = (!empty($_GET['lng'])) ? floatval($_GET['lng']) : null;
//$tolerance       = isset($_GET['tolerance']) ? floatval($_GET['tolerance']) : 1;
$tolerance       = 100; /* Environ 100km de tolérance */
$bioKeywords     = isset($_GET['bio']) ? sanitize_text_field($_GET['bio']) : '';
$selectedFormats = isset($_GET['selectedFormats']) ? array_map('sanitize_text_field', explode(',', $_GET['selectedFormats'])) : [];

$filters = [];

if ($latitude !== null && $longitude !== null) {
    $earthRadius = 6371;
    $latTolerance = $tolerance / $earthRadius;
    $lngTolerance = $tolerance / ($earthRadius * cos(deg2rad($latitude)));

    $latMin = $latitude - rad2deg($latTolerance);
    $latMax = $latitude + rad2deg($latTolerance);
    $lngMin = $longitude - rad2deg($lngTolerance);
    $lngMax = $longitude + rad2deg($lngTolerance);

    $filters[] = "AND(
        VALUE(LEFT({GPS_Coordinates}, FIND(',', {GPS_Coordinates})-1)) >= $latMin,
        VALUE(LEFT({GPS_Coordinates}, FIND(',', {GPS_Coordinates})-1)) <= $latMax,
        VALUE(TRIM(RIGHT({GPS_Coordinates}, LEN({GPS_Coordinates}) - FIND(',', {GPS_Coordinates})))) >= $lngMin,
        VALUE(TRIM(RIGHT({GPS_Coordinates}, LEN({GPS_Coordinates}) - FIND(',', {GPS_Coordinates})))) <= $lngMax
    )";
}

if (!empty($selectedFormats)) {
    $formatConditions = [];
    foreach ($selectedFormats as $format) {
        $f = strtolower($format);
        $formatConditions[] = "IF(FIND(LOWER('$f'), LOWER({Type})), TRUE(), FALSE())";
    }
    $filters[] = 'AND(' . implode(',', $formatConditions) . ')';
}

if (!empty($bioKeywords)) {
    $bio = strtolower($bioKeywords);
    $filters[] = "IF(FIND(LOWER('$bio'), LOWER({Artist_Biography})), TRUE(), FALSE())";
}

$finalFilter = '';
if (count($filters) > 1) {
    $finalFilter = 'AND(' . implode(',', $filters) . ')';
} elseif (count($filters) === 1) {
    $finalFilter = $filters[0];
}

$artists = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName, $finalFilter);
?>

<div class="artists-list">
    <?php if (!empty($artists)) : ?>
        <?php foreach ($artists as $record) :
            $fields = $record['fields'];
            $firstName = esc_html($fields['First_Name'] ?? '');
            $lastName = esc_html($fields['Last_Name'] ?? '');
            $bio = esc_html($fields['Artist_Biography'] ?? '');
            $imgUrl = isset($fields['Cover_Picture'][0]['url']) ? esc_url($fields['Cover_Picture'][0]['url']) : '';
            $formats = isset($fields['Type']) && is_array($fields['Type']) ? $fields['Type'] : [];

            $artistSlug = sanitize_title($firstName . '-' . $lastName);

            $custom_page = get_page_by_path($artistSlug);
            $artistLink = $custom_page ? get_permalink($custom_page) : site_url('/artist/' . $artistSlug);
            ?>
            <div class="profile">
                <?php if ($imgUrl) : ?>
                    <img src="<?php echo $imgUrl; ?>" alt="Photo of <?php echo "$firstName $lastName"; ?>">
                <?php endif; ?>
                <h3>
                    <a href="<?php echo esc_url($artistLink); ?>">
                        <?php echo esc_html("$firstName $lastName"); ?>
                    </a>
                </h3>
                <p><?php echo $bio; ?></p>
                <?php if (!empty($formats)) : ?>
                    <div class="artist-formats">
                        <strong>Formats:</strong>
                        <div class="selected-formats-list">
                            <?php foreach ($formats as $format) :
                                $slug = sanitize_title($format);
                                $url  = site_url('/services/' . $slug);
                                ?>
                                <a class="selected-format"
                                   href="<?php echo esc_url($url); ?>"
                                   target="_blank">
                                    <?php echo esc_html($format); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <?php endforeach; ?>
    <?php else : ?>
        <p>No artists found for your search.</p>
    <?php endif; ?>
</div>



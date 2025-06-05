<?php
require plugin_dir_path(dirname(__FILE__)) . 'config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'airtable.php';

$slug = get_query_var('artist_slug');
$existing_page = get_page_by_path($slug, OBJECT, 'page');

if (
    $existing_page &&
    untrailingslashit($_SERVER['REQUEST_URI']) !== untrailingslashit(parse_url(get_permalink($existing_page), PHP_URL_PATH))
) {
    wp_redirect(get_permalink($existing_page), 301);
    exit;
}

$artists = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName);

$selected_artist = null;
foreach ($artists as $artist) {
    $fields = $artist['fields'];
    $firstName = esc_html($fields['First_Name'] ?? '');
    $lastName = esc_html($fields['Last_Name'] ?? '');
    $artistSlug = sanitize_title($firstName . '-' . $lastName);

    if ($slug === $artistSlug) {
        $selected_artist = $fields;
        break;
    }
}
?>

<div class="artist-profile-layout">

    <a href="<?php echo esc_url(home_url('/resultat-recherche/')); ?>" class="back-link">← Retour aux résultats</a>

    <div class="artist-header">
        <?php if (!empty($selected_artist['Cover_Picture'][0]['url'])): ?>
            <img class="artist-photo" src="<?php echo esc_url($selected_artist['Cover_Picture'][0]['url']); ?>" alt="">
        <?php endif; ?>

        <div class="artist-info">
            <div class="artist-name-block">
                <h1><?php echo esc_html($selected_artist['First_Name'] . ' ' . $selected_artist['Last_Name']); ?></h1>

                <?php if (!empty($selected_artist['Location_Residence'])): ?>
                    <p class="location">📍 <?php echo esc_html($selected_artist['Location_Residence']); ?></p>
                <?php endif; ?>
            </div>

            <?php
            $formatField = $selected_artist['Services_Type'] ?? $selected_artist['Type'] ?? [];
            if (!empty($formatField)) {
                $formatNames = getProductServiceNames($AirtableAPIKey, $BaseID, $formatField);
            }
            ?>

            <?php if (!empty($formatNames)): ?>
                <div class="format-tags">
                    <?php foreach ($formatNames as $format): ?>
                        <span class="tag"><?php echo esc_html($format); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($selected_artist['Artist_Biography'])): ?>
        <div class="bio-box">
            <h2>Biographie</h2>
            <p><?php echo nl2br(esc_html($selected_artist['Artist_Biography'])); ?></p>
        </div>
    <?php endif; ?>

</div>


<?php

require plugin_dir_path(dirname(__FILE__)) . 'config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'airtable.php';

$slug = get_query_var('artist_slug');

$existing_page = get_page_by_path('artist/' . $slug, OBJECT, 'page');

if (
    $existing_page &&
    trim($_SERVER['REQUEST_URI'], '/') !== trim(parse_url(get_permalink($existing_page), PHP_URL_PATH), '/')
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

get_header();
?>

<div class="artist-profile-container">
    <?php if ($selected_artist): ?>
        <div class="profile">
            <?php if (!empty($selected_artist['Cover_Picture'][0]['url'])): ?>
                <img src="<?php echo esc_url($selected_artist['Cover_Picture'][0]['url']); ?>" alt="Photo de <?php echo esc_attr($selected_artist['First_Name'] . ' ' . $selected_artist['Last_Name']); ?>">
            <?php endif; ?>

            <h3><?php echo esc_html($selected_artist['First_Name'] . ' ' . $selected_artist['Last_Name']); ?></h3>

            <?php if (!empty($selected_artist['Location_Residence'])): ?>
                <div class="artist-location">
                    <strong>Localisation :</strong>
                    <span><?php echo esc_html($selected_artist['Location_Residence']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($selected_artist['Type']) && is_array($selected_artist['Type'])): ?>
                <div class="artist-formats">
                    <strong>Formats :</strong>
                    <div class="selected-formats-list">
                        <?php foreach ($selected_artist['Type'] as $format):
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


            <p><?php echo nl2br(esc_html($selected_artist['Artist_Biography'] ?? '')); ?></p>

            <div style="margin-top: 20px;">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="selected-format">← Retour à l'accueil</a>
            </div>
        </div>
    <?php else: ?>
        <div class="profile">
            <h3>Artiste introuvable</h3>
            <p>Aucun artiste correspondant n'a été trouvé.</p>
            <div style="margin-top: 20px;">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="selected-format">← Retour à l'accueil</a>
            </div>
        </div>
    <?php endif; ?>
</div>

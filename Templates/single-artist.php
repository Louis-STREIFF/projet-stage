<?php

require plugin_dir_path(dirname(__FILE__)) . 'config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'airtable.php';


$slug = get_query_var('artist_slug');

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

<div class="artist-page-container">
    <?php if ($selected_artist): ?>
        <div class="profile">
            <?php if (!empty($selected_artist['Cover_Picture'][0]['url'])): ?>
                <img src="<?php echo esc_url($selected_artist['Cover_Picture'][0]['url']); ?>" alt="Photo de <?php echo esc_attr($selected_artist['First_Name'] . ' ' . $selected_artist['Last_Name']); ?>">
            <?php endif; ?>

            <h3><?php echo esc_html($selected_artist['First_Name'] . ' ' . $selected_artist['Last_Name']); ?></h3>
            <p><?php echo nl2br(esc_html($selected_artist['Artist_Biography'] ?? '')); ?></p>

            <div style="margin-top: 20px;">
                <a href="<?php echo home_url('/'); ?>" class="selected-format">← Retour à l'accueil</a>
            </div>
        </div>
    <?php else: ?>
        <div class="profile">
            <h3>Artiste non trouvé</h3>
            <p>Désolé, nous n'avons pas trouvé cet artiste.</p>

            <div style="margin-top: 20px;">
                <a href="<?php echo home_url('/'); ?>" class="selected-format">← Retour à l'accueil</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
?>

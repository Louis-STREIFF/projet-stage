<?php
?>

<div class="artists-list">
    <?php if (!empty($artists)) : ?>
        <?php foreach ($artists as $record) :
            $fields = $record['fields'] ?? [];
            $publicName = esc_html($fields['Public_Name'] ?? '');
            $firstName = esc_html($fields['First_Name'] ?? '');
            $lastName = esc_html($fields['Last_Name'] ?? '');
            $bio = esc_html($fields['Short_Biography'] ?? '');
            $imgUrl = isset($fields['Cover_Picture'][0]['url']) ? esc_url($fields['Cover_Picture'][0]['url']) : '';

            // Récupérer les formats (nom) depuis les IDs stockés dans Services_Type
            $formats = [];
            if (!empty($fields['Services_Type']) && is_array($fields['Services_Type'])) {
                $formats = getProductServiceNames($apiKey, $baseID, $fields['Services_Type']);
            }

            // Construire le slug artiste pour lien
            $artistSlug = sanitize_title($firstName . '-' . $lastName);
            $customPage = get_page_by_path($artistSlug);
            $artistLink = $customPage ? get_permalink($customPage) : site_url('/artiste/' . $artistSlug);
            ?>
            <div class="profile">
                <?php if ($imgUrl) : ?>
                    <img src="<?php echo $imgUrl; ?>" alt="Photo de <?php echo "$firstName $lastName"; ?>">
                <?php endif; ?>
                <h3>
                    <a href="<?php echo esc_url($artistLink); ?>">
                        <?php echo $publicName; ?>
                    </a>
                </h3>
                <p><?php echo $bio; ?></p>
                <?php if (!empty($formats)) : ?>
                    <div class="artist-formats">
                        <strong>Formats :</strong>
                        <div class="selected-formats-list">
                            <?php foreach ($formats as $format) :
                                $slug = sanitize_title($format);
                                $url = site_url('/' . $slug);
                                ?>
                                <a class="selected-format" href="<?php echo esc_url($url); ?>" target="_blank">
                                    <?php echo esc_html($format); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Aucun format sélectionné.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Aucun artiste trouvé pour ce format.</p>
    <?php endif; ?>
</div>

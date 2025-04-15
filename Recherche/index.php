<?php

include __DIR__ . '/../config.php';
require_once __DIR__ . '/../airtable.php';

$artists = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Artist Filter</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MapAPIKey; ?>&libraries=places&callback=initAutocomplete" async defer></script>
    <script src="script.js" defer></script>
</head>
<body>

<h1>Artist Filter</h1>

<form method="GET" action="search.php" id="searchForm" onsubmit="handleFormSubmit(event)">
    <div class="form-group">
        <label for="lieu">Lieu :</label>
        <input type="text" id="lieu" name="lieu" value="<?php echo htmlspecialchars($_GET['lieu'] ?? ''); ?>">
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
        <select id="format" name="formats[]">
            <option value="" disabled selected>Select a format</option>
            <?php
            $uniqueFormats = [];
            foreach ($artists as $record) {
                if (isset($record['fields']['Type']) && is_array($record['fields']['Type'])) {
                    foreach ($record['fields']['Type'] as $format) {
                        if (!in_array($format, $uniqueFormats)) {
                            $uniqueFormats[] = $format;
                        }
                    }
                }
            }
            foreach ($uniqueFormats as $format) {
                $safeFormat = htmlspecialchars($format);
                echo "<option value=\"$safeFormat\">$safeFormat</option>";
            }
            ?>
        </select>
        <div id="selected-formats"></div>
        <input type="hidden" id="selectedFormatsInput" name="selectedFormats">
    </div>

    <div class="form-group">
        <label for="bio">Keywords in bio :</label>
        <input type="text" id="bio" name="bio" value="<?php echo htmlspecialchars($_GET['bio'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <a href="../Ajout/add_artists.php" class="btn">Or you want to add an artist ?</a>
    </div>
</form>

<div id="all-artistes">
    <h2>All Artists</h2>
    <?php if (!empty($artists)): ?>
        <div class="artistes-list">
            <?php foreach ($artists as $record): ?>
                <?php
                $fields = is_array($record) ? ($record['fields'] ?? []) : [];
                $firstname = htmlspecialchars($fields['First_Name'] ?? 'No Firstname');
                $lastname = htmlspecialchars($fields['Last_Name'] ?? 'No Lastname');
                $websiteurl = htmlspecialchars($fields['Website_URL'] ?? '');
                $imageUrl = htmlspecialchars($fields['Cover_Picture'][0]['url'] ?? '');
                $bio = htmlspecialchars($fields['Artist_Biography'] ?? 'No bio');
                ?>
                <div class="profile">
                    <?php if (!empty($imageUrl)): ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Image de <?= $firstname ?> <?= $lastname ?>" class="artist-photo">
                    <?php else: ?>
                        <p>No photo.</p>
                    <?php endif; ?>
                    <h3><?= $firstname ?> <?= $lastname ?></h3>
                    <p><a href="https://<?= $websiteurl ?>" target="_blank"><?= $websiteurl ?></a></p>
                    <p><?= $bio ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No artist found.</p>
    <?php endif; ?>
</div>

<script>
    const toleranceSlider = document.getElementById('tolerance');
    const toleranceValue = document.getElementById('toleranceValue');
    toleranceValue.textContent = toleranceSlider.value;
    toleranceSlider.addEventListener('input', function() {
        toleranceValue.textContent = toleranceSlider.value;
    });
</script>

</body>
</html>
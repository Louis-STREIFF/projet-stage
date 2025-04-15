<?php
require __DIR__ .'/../config.php';
require_once __DIR__ .'/../airtable.php';

$latitude = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$longitude = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$selectedFormats = isset($_GET['selectedFormats']) ? explode(',', $_GET['selectedFormats']) : [];
$bioKeywords = isset($_GET['bio']) ? strtolower($_GET['bio']) : '';
$tri = $_GET['tri'] ?? '';
$tolerance = isset($_GET['tolerance']) ? floatval($_GET['tolerance']) : 1;

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
        VALUE(LEFT({GPS_Coordinates}, FIND(',', {GPS_Coordinates}) - 1)) >= $latMin,
        VALUE(LEFT({GPS_Coordinates}, FIND(',', {GPS_Coordinates}) - 1)) <= $latMax,
        VALUE(TRIM(RIGHT({GPS_Coordinates}, LEN({GPS_Coordinates}) - FIND(',', {GPS_Coordinates})))) >= $lngMin,
        VALUE(TRIM(RIGHT({GPS_Coordinates}, LEN({GPS_Coordinates}) - FIND(',', {GPS_Coordinates})))) <= $lngMax
    )";
}

if (!empty($selectedFormats)) {
    $formatConditions = array_map(function ($f) {
        return "FIND(LOWER('$f'), LOWER({Type})) > 0";
    }, $selectedFormats);
    $filters[] = "AND(" . implode(",", $formatConditions) . ")";
}

if (!empty($bioKeywords)) {
    $filters[] = "FIND(LOWER('$bioKeywords'), LOWER({Artist_Biography})) > 0";
}

if (count($filters) > 1) {
    $finalFilter = "AND(" . implode(",", $filters) . ")";
} else {
    $finalFilter = $filters[0] ?? '';
}

$artists = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName, $finalFilter);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Results</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<h1>Results</h1>

<?php if (!empty($artists)): ?>
    <div class="artistes-list">
        <?php foreach ($artists as $record): ?>
            <?php
            $fields = $record['fields'];
            $firstname = htmlspecialchars($fields['First_Name'] ?? 'No First Name');
            $lastname = htmlspecialchars($fields['Last_Name'] ?? 'No Last Name');
            $bio = htmlspecialchars($fields['Artist_Biography'] ?? 'No Bio');
            $imageUrl = htmlspecialchars($fields['Cover_Picture'][0]['url'] ?? '');
            ?>
            <div class="profile">
                <?php if (!empty($imageUrl)): ?>
                    <img src="<?= $imageUrl ?>" alt="Image de <?= $firstname ?> <?= $lastname ?>">
                <?php endif; ?>
                <h3>
                    <a href="artist.php?id=<?= htmlspecialchars($record['id']) ?>">
                        <?= $firstname ?> <?= $lastname ?>
                    </a>
                </h3>
                <p><?= $bio ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No artist found.</p>
<?php endif; ?>
</body>
</html>
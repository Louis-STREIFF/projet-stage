<?php
require_once 'config.php';
require_once 'airtable.php';

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
        VALUE(LEFT({Coordonnées}, FIND(',', {Coordonnées}) - 1)) >= $latMin,
        VALUE(LEFT({Coordonnées}, FIND(',', {Coordonnées}) - 1)) <= $latMax,
        VALUE(TRIM(RIGHT({Coordonnées}, LEN({Coordonnées}) - FIND(',', {Coordonnées})))) >= $lngMin,
        VALUE(TRIM(RIGHT({Coordonnées}, LEN({Coordonnées}) - FIND(',', {Coordonnées})))) <= $lngMax
    )";
}

if (!empty($selectedFormats)) {
    $formatConditions = array_map(function ($f) {
        return "FIND(LOWER('$f'), LOWER({Format})) > 0";
    }, $selectedFormats);
    $filters[] = "AND(" . implode(",", $formatConditions) . ")";
}

if (!empty($bioKeywords)) {
    $filters[] = "FIND(LOWER('$bioKeywords'), LOWER({Bio})) > 0";
}

if (count($filters) > 1) {
    $finalFilter = "AND(" . implode(",", $filters) . ")";
} else {
    $finalFilter = $filters[0] ?? '';
}

$artistes = getArtistesFromAirtable($AirtableAPIKey, $BaseID, $TableName, $finalFilter);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de la recherche</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Résultats de la recherche</h1>

<?php if (!empty($artistes)): ?>
    <div class="artistes-list">
        <?php foreach ($artistes as $record): ?>
            <?php
            $fields = $record['fields'];
            $prenom = htmlspecialchars($fields['Prenom'] ?? 'Prénom non défini');
            $nom = htmlspecialchars($fields['Nom'] ?? 'Nom non défini');
            $bio = htmlspecialchars($fields['Bio'] ?? 'Bio non définie');
            $imageUrl = htmlspecialchars($fields['Photo'][0]['url'] ?? '');
            ?>
            <div class="profile">
                <?php if (!empty($imageUrl)): ?>
                    <img src="<?= $imageUrl ?>" alt="Image de <?= $prenom ?> <?= $nom ?>">
                <?php endif; ?>
                <h3>
                    <a href="artiste.php?id=<?= htmlspecialchars($record['id']) ?>">
                        <?= $prenom ?> <?= $nom ?>
                    </a>
                </h3>
                <p><?= $bio ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Aucun artiste trouvé.</p>
<?php endif; ?>
</body>
</html>
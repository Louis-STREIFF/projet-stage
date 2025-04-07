<?php
require_once 'airtable.php';

$latitude = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$longitude = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$selectedFormats = isset($_GET['selectedFormats']) ? explode(',', $_GET['selectedFormats']) : [];
$bioKeywords = isset($_GET['bio']) ? strtolower($_GET['bio']) : '';
$tri = $_GET['tri'] ?? '';

$filters = [];

if ($latitude !== null && $longitude !== null) {
    $tolerance = 1;
    $latMin = $latitude - $tolerance;
    $latMax = $latitude + $tolerance;
    $lngMin = $longitude - $tolerance;
    $lngMax = $longitude + $tolerance;

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

$artistes = getArtistesFromAirtable($finalFilter, $tri);

if ($tri === 'Nom prenom') {
    usort($artistes, function ($a, $b) {
        return strcmp($a['fields']['Nom'] ?? '', $b['fields']['Nom'] ?? '');
    });
} elseif ($tri === 'createdTime') {
    usort($artistes, function ($a, $b) {
        return strtotime($a['createdTime']) - strtotime($b['createdTime']);
    });
}
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
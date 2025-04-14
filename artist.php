<?php
require_once 'airtable.php';

$id = $_GET['id'] ?? null;
$artiste = null;

if ($id) {
    $artiste = getArtistsById($id);
}

if (!$artiste) {
    echo "<p>Artiste introuvable.</p>";
    exit;
}

$fields = $artiste['fields'];
$prenom = htmlspecialchars($fields['Prenom'] ?? 'Prénom non défini');
$nom = htmlspecialchars($fields['Nom'] ?? 'Nom non défini');
$bio = htmlspecialchars($fields['Bio complète'] ?? 'Bio non définie');
$imageUrl = htmlspecialchars($fields['Photo'][0]['url'] ?? '');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $prenom ?> <?= $nom ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="profile">
    <?php if (!empty($imageUrl)): ?>
        <img src="<?= $imageUrl ?>" alt="Image de <?= $prenom ?> <?= $nom ?>">
    <?php endif; ?>
    <h3><?= $prenom ?> <?= $nom ?></h3>
    <p><?= $bio ?></p>
</div>
</body>
</html>

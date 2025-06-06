<?php
require_once 'airtable.php';
$artistes = getArtistesFromAirtable();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Filtrer et Trier les Artistes</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDJf5q9usMkT0XpmXfQejiu30ZcHW4p9Jw&libraries=places&callback=initAutocomplete" async defer></script>
    <script src="script.js" defer></script>
</head>
<body>

<h1>Filtrer et Trier les Artistes</h1>

<form method="GET" action="search.php" id="searchForm" onsubmit="handleFormSubmit(event)">
    <div class="form-group">
        <label for="lieu">Lieu :</label>
        <input type="text" id="lieu" name="lieu" value="<?php echo htmlspecialchars($_GET['lieu'] ?? ''); ?>">
        <input type="hidden" id="lat" name="lat">
        <input type="hidden" id="lng" name="lng">

    </div>
    <div class="form-group">
        <label for="format">Format :</label>
        <select id="format">
            <option value="">Sélectionner un format</option>
            <option value="spectacle">Spectacle</option>
            <option value="déambulatoire">Déambulatoire</option>
            <option value="team building">Team building</option>
            <option value="conférence">Conférence</option>
            <option value="plénière">Plénière</option>
            <option value="séminaires">Séminaires</option>
            <option value="conventions">Conventions</option>
        </select>
        <div id="selected-formats"></div>
        <input type="hidden" id="selectedFormatsInput" name="selectedFormats">
    </div>
    <div class="form-group">
        <label for="bio">Mots dans la bio :</label>
        <input type="text" id="bio" name="bio" value="<?php echo htmlspecialchars($_GET['bio'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="tri">Trier par :</label>
        <select id="tri" name="tri">
            <option value="" <?php echo empty($_GET['tri']) ? 'selected' : ''; ?>>Aucun</option>
            <option value="Nom prenom" <?php echo ($_GET['tri'] ?? '') == 'Nom prenom' ? 'selected' : ''; ?>>Nom</option>
            <option value="createdTime" <?php echo ($_GET['tri'] ?? '') == 'createdTime' ? 'selected' : ''; ?>>Date de création</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit">Filtrer et Trier</button>
    </div>
</form>

<div id="all-artistes">
    <h2>Tous les Artistes</h2>
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
                    <h3><?= $prenom ?> <?= $nom ?></h3>
                    <p><?= $bio ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Aucun artiste trouvé.</p>
    <?php endif; ?>
</div>

</body>
</html>
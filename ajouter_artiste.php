<?php
require 'config.php';
require_once 'airtable.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom   = $_POST['prenom'] ?? '';
    $nom      = $_POST['nom'] ?? '';
    $bio      = $_POST['bio'] ?? '';
    $photoUrl = $_POST['photo_url'] ?? '';
    $adresse  = $_POST['lieu'] ?? '';
    $latitude = $_POST['lat'] ?? null;
    $longitude = $_POST['lng'] ?? null;

    $formats = [];
    if (!empty($_POST['selectedFormats'])) {
        $decoded = json_decode($_POST['selectedFormats'], true);
        if (is_array($decoded)) {
            $formats = $decoded;
        }
    }
    $validFormats = ['spectacles', 'déambulatoires', 'team building', 'conférences', 'plénières', 'séminaires', 'conventions'];
    $formatArray = array_filter($formats, function($format) use ($validFormats) {
        return in_array(strtolower($format), $validFormats);
    });
    if (empty($formatArray)) {
        $formatArray = ['spectacles'];
    }
    $formatArray = array_values($formatArray);

    $coordinates = null;
    if ($latitude !== null && $longitude !== null) {
        $coordinates = floatval($latitude) . ', ' . floatval($longitude);
    }

    $data = [
        'fields' => [
            'Prenom'      => $prenom,
            'Nom'         => $nom,
            'Bio'         => $bio,
            'Format'      => $formatArray,
            'Adresse'     => $adresse,
            'Coordonnées' => $coordinates,
        ]
    ];

    $url = "https://api.airtable.com/v0/$BaseID/Attente";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $AirtableAPIKey,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Erreur cURL : " . curl_error($ch);
    } else {
        echo "Réponse de l'API : " . $response;
    }
    curl_close($ch);
}

$artistes = getArtistesFromAirtable($AirtableAPIKey, $BaseID, $TableName);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'Ajout d'Artiste</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MapAPIKey; ?>&libraries=places&callback=initAutocomplete" async defer></script>
    <script defer>
        document.addEventListener('DOMContentLoaded', function () {

            const selectElement = document.getElementById('format');
            const selectedFormatsContainer = document.getElementById('selected-formats');
            const hiddenInput = document.getElementById('selectedFormatsInput');
            const selectedFormats = [];

            selectElement.addEventListener('change', function () {
                const format = this.value;
                if (format && !selectedFormats.includes(format)) {
                    selectedFormats.push(format);
                    addFormatTag(format);
                    updateSelectedFormatsInput();
                }
                this.selectedIndex = 0;
            });

            function addFormatTag(format) {
                const formatElement = document.createElement('div');
                formatElement.className = 'selected-format';
                formatElement.textContent = format;
                formatElement.style.cssText = 'display: inline-block; margin: 4px; padding: 6px 10px; background-color: #eee; border-radius: 20px; cursor: pointer;';
                formatElement.addEventListener('click', function () {
                    selectedFormatsContainer.removeChild(formatElement);
                    const index = selectedFormats.indexOf(format);
                    if (index !== -1) selectedFormats.splice(index, 1);
                    updateSelectedFormatsInput();
                });
                selectedFormatsContainer.appendChild(formatElement);
            }

            function updateSelectedFormatsInput() {
                hiddenInput.value = JSON.stringify(selectedFormats);
            }

            window.initAutocomplete = function () {
                const autocomplete = new google.maps.places.Autocomplete(document.getElementById('lieu'));
                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace();
                    if (!place.geometry) {
                        console.log("Aucune géométrie disponible pour le lieu saisi.");
                        return;
                    }
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();
                    document.getElementById('lat').value = lat;
                    document.getElementById('lng').value = lng;
                });
            };
            window.onload = function () {
                initAutocomplete();
            };
        });
    </script>
</head>
<body>

<h1>Formulaire d'Ajout d'Artiste</h1>

<form method="POST" action="ajouter_artiste.php">
    <div class="form-group">
        <label for="lieu">Lieu :</label>
        <input type="text" id="lieu" name="lieu" value="<?php echo htmlspecialchars($_GET['lieu'] ?? ''); ?>" required>
        <input type="hidden" id="lat" name="lat">
        <input type="hidden" id="lng" name="lng">
    </div>

    <div class="form-group">
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required>
    </div>

    <div class="form-group">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required>
    </div>

    <div class="form-group">
        <label for="bio">Bio :</label>
        <textarea id="bio" name="bio"></textarea>
    </div>

    <div class="form-group">
        <label for="format">Format :</label>
        <select id="format" name="formats[]">
            <option value="" disabled selected>Choisissez un format</option>
            <?php
            $uniqueFormats = [];
            foreach ($artistes as $record) {
                if (isset($record['fields']['Format']) && is_array($record['fields']['Format'])) {
                    foreach ($record['fields']['Format'] as $format) {
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
        <button type="submit">Ajouter l'Artiste</button>
    </div>

</form>

</body>
</html>

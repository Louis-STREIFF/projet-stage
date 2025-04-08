<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'airtable.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom   = $_POST['prenom'] ?? '';
    $nom      = $_POST['nom'] ?? '';
    $bio      = $_POST['bio'] ?? '';
    $photoUrl = $_POST['photo_url'] ?? '';
    $adresse  = $_POST['lieu'] ?? '';
    $latitude = $_POST['lat'] ?? null;
    $longitude= $_POST['lng'] ?? null;

    $formats = [];
    if (!empty($_POST['selectedFormats'])) {
        $decoded = json_decode($_POST['selectedFormats'], true);
        if (is_array($decoded)) {
            $formats = $decoded;
        }
    }

    $validFormats = ['spectacles', 'déambulatoires', 'team building', 'conférences', 'plénières', 'séminaires', 'conventions'];

    $formatArray = array_filter($formats, function ($format) use ($validFormats) {
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

    echo '<pre>';
    var_dump($data);
    echo '</pre>';

    $url = "https://api.airtable.com/v0/appeSB6VXdImy374l/Attente";
    $apiKey = "patVEdnsGMYLXNIiY.7d694dfde54904d35d5d22b3994bbaa86c6d5e3490e5e1eeb991715a5856cfba";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
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
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire avec Google Maps Autocomplete</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDJf5q9usMkT0XpmXfQejiu30ZcHW4p9Jw&libraries=places&callback=initAutocomplete" async defer></script>
    <script src="script.js" defer></script>
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
        <select id="format">
            <option value="">Sélectionner un format</option>
            <option value="spectacles">Spectacle</option>
            <option value="déambulatoires">Déambulatoire</option>
            <option value="team building">Team building</option>
            <option value="conférences">Conférence</option>
            <option value="plénières">Plénière</option>
            <option value="séminaires">Séminaires</option>
            <option value="conventions">Conventions</option>
        </select>

        <div id="selected-formats" style="margin-top: 10px;"></div>

        <input type="hidden" id="selectedFormatsInput" name="selectedFormats">
    </div>


    <div class="form-group">
        <button type="submit">Ajouter l'Artiste</button>
    </div>

</form>
<script>
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

            formatElement.style.display = 'inline-block';
            formatElement.style.margin = '4px';
            formatElement.style.padding = '6px 10px';
            formatElement.style.backgroundColor = '#eee';
            formatElement.style.borderRadius = '20px';
            formatElement.style.cursor = 'pointer';

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
            console.log("Formats sélectionnés :", hiddenInput.value);
        }
    });
</script>

</body>
</html>


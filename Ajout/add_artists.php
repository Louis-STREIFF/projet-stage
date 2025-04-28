<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/../airtable.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom   = $_POST['firstname'] ?? '';
    $nom      = $_POST['lastname'] ?? '';
    $bio      = $_POST['bio'] ?? '';
    $photoUrl = $_POST['photo_url'] ?? '';
    $adress  = $_POST['lieu'] ?? '';
    $latitude = $_POST['lat'] ?? null;
    $longitude = $_POST['lng'] ?? null;
    $birthday = $_POST['date'] ?? null;

    $formats = [];
    if (!empty($_POST['selectedFormats'])) {
        $decoded = json_decode($_POST['selectedFormats'], true);
        if (is_array($decoded)) {
            $formats = $decoded;
        }
    }
    $formatArray = array_values($formats);

    $coordinates = null;
    if ($latitude !== null && $longitude !== null) {
        $coordinates = floatval($latitude) . ', ' . floatval($longitude);
    }

    $data = [
        'fields' => [
            'First_Name'      => $prenom,
            'Last_Name'         => $nom,
            'Artist_Biography'         => $bio,
            'Location_Residence'     => $adress,
            'GPS_Coordinates' => $coordinates,
            'Birthday' => $birthday,
        ]
    ];

    $url = "https://api.airtable.com/v0/$BaseID/Waiting";
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
        echo "réponse de l'API : " . $response;
    }
    curl_close($ch);
}

$artistes = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Add Artist Form</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MapAPIKey; ?>&libraries=places&callback=initAutocomplete" async defer></script>
    <script src="script.js" defer></script>

<body>

<h1>Enter your details</h1>

<form method="POST" action="add_artists.php">
    <div class="form-group">
        <label for="lieu">Place :</label>
        <input type="text" id="lieu" name="lieu" value="<?php echo htmlspecialchars($_GET['lieu'] ?? ''); ?>" required>
        <input type="hidden" id="lat" name="lat">
        <input type="hidden" id="lng" name="lng">
    </div>

    <div class="form-group">
        <label for="firstname">Firstname :</label>
        <input type="text" id="firstname" name="firstname" required>
    </div>

    <div class="form-group">
        <label for="lastname">Lastname :</label>
        <input type="text" id="lastname" name="lastname" required>
    </div>

    <div class="form-group">
        <label for="bio">Bio :</label>
        <textarea id="bio" name="bio"></textarea>
    </div>

    <div class="form-group">
        <label for="date">Type your birthday :</label>
        <input type="date" id="date" name="date">
    </div>

    <div class="form-group">
        <label for="format">Formats :</label>
        <select id="format" name="formats[]">
            <option value="" disabled selected>Choose your formats</option>
            <?php
            $uniqueFormats = [];
            foreach ($artistes as $record) {
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
        <button type="submit">Add Artist</button>
    </div>

</form>

</body>
</html>
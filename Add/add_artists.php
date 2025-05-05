<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/../airtable.php';

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_artist_form'])) {
    $prenom   = sanitize_text_field($_POST['firstname'] ?? '');
    $nom      = sanitize_text_field($_POST['lastname'] ?? '');
    $bio      = sanitize_textarea_field($_POST['bio'] ?? '');
    $photoUrl = esc_url_raw($_POST['photo_url'] ?? '');
    $adress   = sanitize_text_field($_POST['lieu'] ?? '');
    $latitude = $_POST['lat'] ?? null;
    $longitude = $_POST['lng'] ?? null;
    $birthday = $_POST['date'] ?? null;

    // Récupération des IDs des formats sélectionnés
    $formats = [];
    if (!empty($_POST['selectedFormats'])) {
        $decoded = json_decode(stripslashes($_POST['selectedFormats']), true);
        if (is_array($decoded)) {
            $formats = $decoded; // Ces formats sont maintenant des IDs
        }
    }

    $coordinates = null;
    if ($latitude !== null && $longitude !== null) {
        $coordinates = floatval($latitude) . ', ' . floatval($longitude);
    }

    $data = [
        'fields' => [
            'First_Name'         => $prenom,
            'Last_Name'          => $nom,
            'Artist_Biography'   => $bio,
            'Location_Residence' => $adress,
            'GPS_Coordinates'    => $coordinates,
            'Type_Link'          => $formats, // IDs attendus par Airtable
        ]
    ];

    if (!empty($birthday)) {
        $data['fields']['Birthday'] = $birthday;
    }

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
        echo "<div class='error'>Erreur cURL : " . curl_error($ch) . "</div>";
    }
    curl_close($ch);
}

// Récupère les types depuis la table "Type"
$types = getArtistsFromAirtable($AirtableAPIKey, $BaseID, 'Type'); // Table 'Type'
$formatOptions = [];
foreach ($types as $record) {
    if (!empty($record['fields']['Name'])) {
        $formatOptions[$record['id']] = $record['fields']['Name'];
    }
}
?>

<form method="POST">
    <input type="hidden" name="add_artist_form" value="1">

    <div class="form-group">
        <label for="lieu">Place :</label>
        <input type="text" id="lieu" name="lieu" required>
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
        <label for="date">Birthday :</label>
        <input type="date" id="date" name="date">
    </div>

    <div class="form-group">
        <label for="format">Formats :</label>
        <select id="format">
            <option value="" disabled selected>Choose your formats</option>
            <?php foreach ($formatOptions as $id => $name) : ?>
                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
            <?php endforeach; ?>
        </select>
        <div id="selected-formats"></div>
        <input type="hidden" id="selectedFormatsInput" name="selectedFormats">
    </div>

    <div class="form-group">
        <button type="submit">Add Artist</button>
    </div>
</form>

<?php if (!empty($response)) : ?>
    <div class="api-response" style="margin-top: 20px; padding: 10px; background-color: #f0f0f0;">
        <strong>Réponse de l'API Airtable :</strong>
        <pre><?php echo esc_html($response); ?></pre>
    </div>
<?php endif; ?>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($MapAPIKey); ?>&libraries=places&callback=initAutocomplete" async defer></script>
<script src="<?php echo plugin_dir_url(__FILE__) . 'script.js'; ?>" defer></script>

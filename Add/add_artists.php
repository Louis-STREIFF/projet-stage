<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/../airtable.php';

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_artist_form'])) {
    $prenom    = sanitize_text_field($_POST['firstname'] ?? '');
    $nom       = sanitize_text_field($_POST['lastname'] ?? '');
    $bio       = sanitize_textarea_field($_POST['bio'] ?? '');
    $photoUrl  = esc_url_raw($_POST['photo_url'] ?? '');
    $adress    = sanitize_text_field($_POST['lieu'] ?? '');
    $latitude  = $_POST['lat'] ?? null;
    $longitude = $_POST['lng'] ?? null;
    $birthday  = $_POST['date'] ?? null;
    $phone     = $_POST['phone'] ?? null;

    $formats   = $_POST['selectedFormats'] ?? [];

    if (!empty($phone)) {
        $cleaned = preg_replace('/[\s.\-()]+/', '', $phone);
        if (strpos($cleaned, '+') === 0) {
            $phone = $cleaned;
        } elseif (preg_match('/^0[1-9]\d{8}$/', $cleaned)) {
            $phone = '+33' . substr($cleaned, 1);
        } else {
            $phone = $cleaned;
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
            'Type_Link'          => is_array($formats) ? array_values($formats) : [],
            'Phone_Number'       => $phone,
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

$formatRecords = getArtistsFromAirtable($AirtableAPIKey, $BaseID, 'Products_Services');
$formatOptions = [];
foreach ($formatRecords as $record) {
    if (!empty($record['fields']['Type'])) {
        $formatOptions[] = [
            'id'    => $record['id'],
            'label' => $record['fields']['Type'],
        ];
    }
}
?>

<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'style.css'; ?>">

<form method="POST">
    <input type="hidden" name="add_artist_form" value="1">

    <div class="form-group">
        <label for="firstname">Prénom :</label>
        <input type="text" id="firstname" name="firstname" required>
    </div>

    <div class="form-group">
        <label for="lastname">Nom :</label>
        <input type="text" id="lastname" name="lastname" required>
    </div>

    <div class="form-group">
        <label for="lieu">Adresse :</label>
        <input type="text" id="lieu" name="lieu" class="full-width-input" required>
        <input type="hidden" id="lat" name="lat">
        <input type="hidden" id="lng" name="lng">
    </div>

    <div class="form-group">
        <label for="date">Date de naissance :</label>
        <input type="date" id="date" name="date">
    </div>

    <div class="form-group">
        <label for="phone">Numéro de téléphone :</label>
        <input type="text" id="phone" name="phone" placeholder="06 12 34 56 78">
    </div>

    <div class="form-group">
        <label for="format">Vos formats :</label>
        <select id="format" class="input-shadow">
            <option value="" disabled selected>Sélectionnez un format</option>
            <?php foreach ($formatOptions as $fmt): ?>
                <option
                        value="<?php echo esc_attr($fmt['id']); ?>"
                        data-label="<?php echo esc_attr($fmt['label']); ?>"
                >
                    <?php echo esc_html($fmt['label']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div id="selected-formats"></div>
        <div id="formats-inputs"></div>
    </div>

    <div class="form-group">
        <label for="bio">Vos attentes :</label>
        <textarea id="bio" name="bio"></textarea>
    </div>

    <div class="form-group full-width">
        <button type="submit">Ajouter l'artiste</button>
    </div>
</form>

<?php if (!empty($response)) : ?>
    <div class="api-response" style="margin-top:20px;padding:10px;background:#f0f0f0;">
        <strong>Réponse API :</strong>
        <pre><?php echo esc_html($response); ?></pre>
    </div>
<?php endif; ?>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($MapAPIKey); ?>&libraries=places&callback=initAutocomplete" async defer></script>
<script src="<?php echo plugin_dir_url(__FILE__) . 'script.js'; ?>" defer></script>

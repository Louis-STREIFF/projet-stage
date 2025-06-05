<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/../airtable.php';

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_artist_form'])) {
    $prenom    = sanitize_text_field($_POST['firstname'] ?? '');
    $nom       = sanitize_text_field($_POST['lastname'] ?? '');
    $bio       = sanitize_textarea_field($_POST['bio'] ?? '');
//    $photoUrl  = esc_url_raw($_POST['photo_url'] ?? '');
    $adress    = sanitize_text_field($_POST['lieu'] ?? '');
    $latitude  = $_POST['lat'] ?? null;
    $longitude = $_POST['lng'] ?? null;
    $birthday  = $_POST['date'] ?? null;
    $phone     = $_POST['phone'] ?? null;
//    $formats   = $_POST['selectedFormats'] ?? [];
    $email     = sanitize_email($_POST['email'] ?? '');
    $website   = $_POST['website'] ?? '';

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
            'Phone_Number'       => $phone,
            'Mail' => $email,
            'Status' => "waiting",
            'Website_URL' => $website,
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


$artists = getArtistsFromAirtable($AirtableAPIKey, $BaseID, $TableName);
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
        <div class="input-with-icon">
        <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
              <path fill="#808080" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0
              9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
            </svg>
        </span>
            <input type="text" id="lieu" name="lieu" placeholder="Indiquez un lieu" required>
            <input type="hidden" id="lat" name="lat">
            <input type="hidden" id="lng" name="lng">
        </div>
    </div>

    <div class="form-group">
        <label for="date">Date de naissance :</label>
        <input type="date" id="date" name="date" required>
    </div>

    <div class="form-group phone-field">
        <label for="phone">Numéro de téléphone :</label>
        <input type="text" id="phone" name="phone" placeholder="06 12 34 56 78" required>
    </div>

    <div class="form-group">
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" placeholder="exemple@domaine.com" required>
    </div>

<!--    <div class="form-group">-->
<!--        <label for="format">Vos formats :</label>-->
<!--        <select id="format" class="input-shadow">-->
<!--            <option value="" disabled selected>Sélectionnez un format</option>-->
<!--            --><?php //foreach (array_unique(call_user_func(function() use ($artists) {
//                $formats = [];
//                foreach ($artists as $rec) {
//                    if (!empty($rec['fields']['Type']) && is_array($rec['fields']['Type'])) {
//                        $formats = array_merge($formats, $rec['fields']['Type']);
//                    }
//                }
//                return $formats;
//            })) as $fmt): ?>
<!--                <option value="--><?php //echo esc_attr($fmt); ?><!--" data-label="--><?php //echo esc_attr($fmt); ?><!--">-->
<!--                    --><?php //echo esc_html($fmt); ?>
<!--                </option>-->
<!--            --><?php //endforeach; ?>
<!--        </select>-->
<!--        <div id="selected-formats" style="margin-top: 10px;"></div>-->
<!--        <div id="formats-inputs"></div>-->
<!--    </div>-->
<!--    -->
    <div class="form-group">
        <label for="website">Lien vers votre site :</label>
        <input type="text" id="website" name="website" required>
    </div>

    <div class="form-group">
        <label for="bio">Vos attentes :</label>
        <input type="text" id="bio" name="bio" required>
    </div>

    <div class="form-group full-width">
        <button type="submit">Ajouter l'artiste</button>
    </div>

</form>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($MapAPIKey); ?>&libraries=places&callback=initAutocomplete" async defer></script>
<script src="<?php echo plugin_dir_url(__FILE__) . 'script.js'; ?>" defer></script>

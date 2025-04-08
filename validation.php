<?php
$apiKey = "patVEdnsGMYLXNIiY.7d694dfde54904d35d5d22b3994bbaa86c6d5e3490e5e1eeb991715a5856cfba"; // Remplacez par votre clé API
$baseId = "appeSB6VXdImy374l";

$tableAttenteUrl = "https://api.airtable.com/v0/$baseId/Attente";
$tableArtistesUrl = "https://api.airtable.com/v0/$baseId/Artistes";

$options = [
    "http" => [
        "header" => "Authorization: Bearer $apiKey"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($tableAttenteUrl, false, $context);
$data = json_decode($response, true);

foreach ($data['records'] as $record) {
    if (isset($record['fields']['Validé']) && $record['fields']['Validé'] === true) {
        $fields = [
            'Prenom' => $record['fields']['Prenom'],
            'Nom' => $record['fields']['Nom'],
            'Bio' => $record['fields']['Bio'],
            'Format' => $record['fields']['Format'],
            'Adresse' => $record['fields']['Adresse'],
            'Coordonnées' => $record['fields']['Coordonnées']
        ];

        $dataToInsert = [
            "fields" => $fields
        ];

        $ch = curl_init($tableArtistesUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToInsert));

        $insertResponse = curl_exec($ch);
        curl_close($ch);

        $insertData = json_decode($insertResponse, true);
        if (isset($insertData['id'])) {
            $deleteUrl = $tableAttenteUrl . '/' . $record['id'];
            $ch = curl_init($deleteUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $apiKey"
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_exec($ch);
            curl_close($ch);
        } else {
            echo "Erreur d'insertion pour l'enregistrement ID: " . $record['id'];
        }
    }
}

echo "Transfert terminé.";
?>

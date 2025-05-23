<?php
require __DIR__ . "/../config.php";

$tableAttenteUrl = "https://api.airtable.com/v0/$BaseID/Waiting";
$PrincipalsTableUrl = "https://api.airtable.com/v0/$BaseID/$TableName";

$options = [
    "http" => [
        "header" => "Authorization: Bearer $AirtableAPIKey",
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($tableAttenteUrl, false, $context);
$data = json_decode($response, true);

foreach ($data['records'] as $record) {
    if (isset($record['fields']['Validation']) && $record['fields']['Validation'] === true) {
        $fields = [
            'First_Name' => $record['fields']['First_Name'],
            'Last_Name' => $record['fields']['Last_Name'],
            'Artist_Biography' => $record['fields']['Artist_Biography'],
            'Location_Residence' => $record['fields']['Location_Residence'],
            'GPS_Coordinates' => $record['fields']['GPS_Coordinates'],
        ];

        $dataToInsert = [
            "fields" => $fields
        ];

        $ch = curl_init($PrincipalsTableUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $AirtableAPIKey",
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
                "Authorization: Bearer $AirtableAPIKey",
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_exec($ch);
            curl_close($ch);
        } else {
            echo "Error : ID: " . $record['id'];
        }
    }
}

echo "Transfert end.";
?>

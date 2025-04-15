<?php
require 'config.php';

function getArtistsFromAirtable($AirtableAPIKey, $baseID, $tableName, $filter = '') {
    $url = "https://api.airtable.com/v0/$baseID/$tableName";
    if ($filter) {
        $url .= "?filterByFormula=" . urlencode($filter);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $AirtableAPIKey,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        echo "Erreur cURL : " . curl_error($ch);
        curl_close($ch);
        return [];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    return isset($data['records']) ? $data['records'] : [];
}

function getArtistsById($apiKey, $baseId, $tableName, $recordId) {
    $url = "https://api.airtable.com/v0/$baseId/$tableName/$recordId";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    return $data;
}

?>
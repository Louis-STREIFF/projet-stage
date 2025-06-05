<?php
require 'config.php';

function getArtistsFromAirtable($AirtableAPIKey, $baseID, $tableName, $filter = '') {
    $cache_key = 'airtable_artists_' . md5($baseID . $tableName . $filter);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

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
        error_log("Erreur cURL : " . curl_error($ch));
        curl_close($ch);
        return [];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    $records = isset($data['records']) ? $data['records'] : [];
    set_transient($cache_key, $records, 5 * MINUTE_IN_SECONDS);

    return $records;
}

function getProductServiceNames($AirtableAPIKey, $baseID, $productServiceIds) {
    if (empty($productServiceIds)) return [];

    $cache_key = 'airtable_services_' . md5(implode(',', $productServiceIds));
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $names = [];

    foreach ($productServiceIds as $serviceId) {
        $url = "https://api.airtable.com/v0/$baseID/Products_Services/$serviceId";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $AirtableAPIKey,
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['fields']['Name'])) {
            $names[] = $data['fields']['Name'];
        }
    }
    set_transient($cache_key, $names, 10 * MINUTE_IN_SECONDS);

    return $names;
}
?>

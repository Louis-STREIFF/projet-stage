<?php
function getArtistesFromAirtable($filter = '', $sort = null) {
    $apiKey = 'patVEdnsGMYLXNIiY.7d694dfde54904d35d5d22b3994bbaa86c6d5e3490e5e1eeb991715a5856cfba';
    $baseId = 'appeSB6VXdImy374l';
    $tableName = 'Artistes';

    $params = [];

    if ($filter) {
        $params['filterByFormula'] = $filter;
    }

    if ($sort) {
        $params['sort[0][field]'] = $sort['field'];
        $params['sort[0][direction]'] = $sort['direction'];
    }

    $query = http_build_query($params);
    $url = "https://api.airtable.com/v0/$baseId/$tableName?$query";

    $headers = [
        "Authorization: Bearer $apiKey"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['records'] ?? [];
}
function getArtisteById($id) {
    $apiKey = 'patVEdnsGMYLXNIiY.7d694dfde54904d35d5d22b3994bbaa86c6d5e3490e5e1eeb991715a5856cfba';
    $baseId = 'appeSB6VXdImy374l';
    $table = 'Artistes';

    $url = "https://api.airtable.com/v0/$baseId/$table/$id";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

?>

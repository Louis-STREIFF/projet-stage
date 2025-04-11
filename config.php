<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$MapAPIKey = $_ENV['MAP_API_KEY'];
$AirtableAPIKey = $_ENV['AIRTABLE_API_KEY'];
$BaseID = $_ENV['BASE_ID'];
$TableName = $_ENV['TABLE_NAME'];

?>


<?php

require_once ('TwitterAPIExchange.php');
require 'vendor/autoload.php';

$connection = new MongoDB\Client("mongodb://localhost:27017");
$collection = $connection->projetBigData->twitter;

$settings = ['oauth_access_token' => "885051297967296514-V00NM7hJVpwix1XN8xF6yDwSwthnEX8",
    'oauth_access_token_secret' => "vZlQgCcZFTai0QxUf5leTfyFaQgouRvTn5PI35LBv7sFq",
    'consumer_key' => "sdSBZyyxB9uQhOWjknvfcehMm",
    'consumer_secret' => "ce1soBCeEyG7pXzpnx5VXHrNOQ7gZsQ6JwZ3Je7Zj4T5VIQvAy"];

$url = "https://api.twitter.com/1.1/search/tweets.json";
$getField = "?q=terrorisme&count=50&lang=fr&result_type=popular";
$requestMethod = "GET";

$twitter = new TwitterAPIExchange($settings);
$twitterResponseEncode = $twitter->setGetfield($getField)->buildOauth($url, $requestMethod)->performRequest();
$twitterResponseDecode = json_decode($twitter->setGetfield($getField)->buildOauth($url, $requestMethod)->performRequest(),true);

foreach ($twitterResponseDecode as $tweets)
{
    foreach ($tweets as $tweet)
    {
        echo '<pre>';
        var_dump($tweet);
        echo '</pre>';    }
}

die();

//Utiliser un foreach
$collection->insertOne(["{firstname: 'Valentin'}"]);

//$collection->insertMany(["[{firstname: 'Valentin'}]","[{firstname: 'Guillaume'}]"]);

echo "<pre>";
print_r($twitterResponseDecode);
echo "</pre>";
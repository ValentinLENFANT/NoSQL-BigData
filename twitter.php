<?php

require_once ('TwitterAPIExchange.php');
require_once ('token_api.php');
require 'vendor/autoload.php';

$connection = new MongoDB\Client("mongodb://localhost:27017");
$collection = $connection->projetBigData->twitter;

$settings = ['oauth_access_token' => $oauth_access_token,
    'oauth_access_token_secret' => $oauth_access_token_secret,
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret];

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
        echo '</pre>';

        $url = $tweet['entities']['urls'][0]['url'];
        $source = explode(">", $tweet['source'])[1];
        $source = explode("<", $source)[0];

        $collection->insertOne(["{createdAt: '$tweet[created_at]', text: '$tweet[text]', url: '$url', source: '$source'"]);
    }
}
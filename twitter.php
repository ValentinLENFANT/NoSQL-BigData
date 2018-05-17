<?php

require_once('TwitterAPIExchange.php');
require_once('token_api.php');
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
$twitterResponseDecode = json_decode($twitter->setGetfield($getField)->buildOauth($url, $requestMethod)->performRequest(), true);

foreach ($twitterResponseDecode as $tweets) {
    foreach ($tweets as $tweet) {

        //URL raccourcie du tweet récupéré
        @$url = $tweet['entities']['urls'][0]['url'];
        //Source de laquelle le tweet a été posté. Par exemple : Twitter for Iphone, Twitter for Android, etc.
        @$source = explode(">", $tweet['source'])[1];
        @$source = explode("<", $source)[0];
        @$user = $tweet['user'];

        @$collection->insertOne(["{createdAt: '$tweet[created_at]', text: '$tweet[text]', url: '$url', source: '$source', username: '$user[name]'"]);
    }
}

$tweetsFromDatabase = $collection->find();

echo '<table style="border-collapse: collapse;">';
echo '<thead>
            <tr>
                <th>Rédigé le :</th>
                <th>Par :</th>
                <th>Sur un :</th>
                <th>Contenu du tweet :</th>
                <th>Accessible à :</th>
            </tr>
      </thead>';
echo '<tbody>';
foreach ($tweetsFromDatabase as $tweetFromDatabase) {
    $tweetFromDatabase = json_decode(json_encode($tweetFromDatabase), true);
    $tweet = explode('\'', $tweetFromDatabase[0]);

    echo '<tr>';
    // Date de rédaction du tweet
    echo '<td style="border: 1px solid black;">' .$tweet[1]. '</td>';
    // Auteur du tweet
    echo '<td style="border: 1px solid black;">' .$tweet[9]. '</td>';
    // Device utilisé pour rédiger le tweet
    echo '<td style="border: 1px solid black;">' .$tweet[7]. '</td>';
    // Contenu du tweet
    echo '<td style="border: 1px solid black;">' .$tweet[3]. '</td>';
    // URL du tweet
    echo '<td style="border: 1px solid black;">' .$tweet[5]. '</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
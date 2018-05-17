<body align="center">
<img style="margin-top: 150px;" src="twitter-logo.png" height="200px" align="center">
<h1 align="center"><a href="form.html">Chercher un nouveau mot clé</a></h1>

<?php

require_once('TwitterAPIExchange.php');
require_once('token_api.php');
require 'vendor/autoload.php';

(string)$collectionToUse = $_POST['motcle'];
$connection = new MongoDB\Client("mongodb://localhost:27017");
$collection = $connection->projetBigData->$collectionToUse;

$settings = ['oauth_access_token' => $oauth_access_token,
    'oauth_access_token_secret' => $oauth_access_token_secret,
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret];

$url = "https://api.twitter.com/1.1/search/tweets.json";
$getField = "?q=".$collectionToUse."&count=50&lang=fr&result_type=popular";
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

        if(@$tweet['created_at'] != "" && @$tweet['created_at'] != " " && @$tweet['created_at'] != "0" && @$tweet['created_at'] != "t")
        {
            @$collection->insertOne(["{createdAt: '$tweet[created_at]', text: '$tweet[text]', url: '$url', source: '$source', username: '$user[name]'"]);
        }
    }
}

$tweetsFromDatabase = $collection->find();

echo '<table style="border-collapse: collapse;" align="center">';
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

$twitterWebClient = 0;
$twitterForIphone = 0;
$twitterMediaStudio = 0;

foreach ($tweetsFromDatabase as $tweetFromDatabase) {
    $tweetFromDatabase = json_decode(json_encode($tweetFromDatabase), true);

    $explodeTweets = explode(': \'', $tweetFromDatabase[0]);
    $tweet = [];
    foreach ($explodeTweets as $explodeTweet)
    {
        $tweet[] = explode('\',', $explodeTweet);
    }

    echo '<tr>';
    // Date de rédaction du tweet
    echo '<td style="border: 1px solid black;">' . $tweet[1][0] . '</td>';
    // Auteur du tweet
    echo '<td style="border: 1px solid black;">' . $tweet[5][0] . '</td>';

    if (substr($tweet[4][0], 0) == "Twitter Web Client") {
        $twitterWebClient++;
    } else if (substr($tweet[4][0], 0) == "Twitter for iPhone") {
        $twitterForIphone++;
    } else if (substr($tweet[4][0], 0) == "Media Studio") {
        $twitterMediaStudio++;
    }
    // Device utilisé pour rédiger le tweet
    echo '<td style="border: 1px solid black;">' . $tweet[4][0] . '</td>';
    // Contenu du tweet
    echo '<td style="border: 1px solid black;">' . $tweet[2][0] . '</td>';
    // URL du tweet
    echo '<td style="border: 1px solid black;">' . $tweet[3][0] . '</td>';
    echo '</tr>';
}

$eachSource = [$twitterWebClient, $twitterForIphone, $twitterMediaStudio];

echo '</tbody>';
echo '</table>';

?>

<script src="amcharts.js" type="text/javascript"></script>
<script src="pie.js" type="text/javascript"></script>

<script>
    var chart;
    var legend;

    var chartData = [
        {
            "devices": "Web Client",
            "value": "<?php echo $eachSource[0];?>"
        },
        {
            "devices": "Iphone",
            "value": "<?php echo $eachSource[1];?>"
        },
        {
            "devices": "Media Studio",
            "value": "<?php echo $eachSource[2];?>"
        }
    ];

    AmCharts.ready(function () {
        // PIE CHART
        chart = new AmCharts.AmPieChart();
        chart.dataProvider = chartData;
        chart.titleField = "devices";
        chart.valueField = "value";
        chart.outlineColor = "#FFFFFF";
        chart.outlineAlpha = 0.8;
        chart.outlineThickness = 2;
        chart.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
        // this makes the chart 3D
        chart.depth3D = 15;
        chart.angle = 30;

        // WRITE
        chart.write("chartdiv");
    });
</script>

<div id="chartdiv" style="width: 100%; height: 400px;"></div>

<footer align="center">Copyright @2018 Guillaume JEAN, Aurélien BERANGER, Valentin LENFANT</footer>
</body>
<?php
// Post a tweet on behalf of the user.
	
require "../include/oauthvalues.php";
session_start();

// obtain authorization to post
if (!isset($_SESSION["accessToken"])) {
    header("Location: /authorize.php");
    exit;
}

// send the tweet to Twitter
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $apiResourceUrl = "http://api.twitter.com/1/statuses/update.json";
    $nonce = md5(mt_rand());
    $oauthTimestamp = time();
    $accessToken = $_SESSION["accessToken"];
    $accessTokenSecret = $_SESSION["accessTokenSecret"];
    $tweetText = trim($_POST["tweet"]);

    // prepare the signature string
    $sigBase = "POST&" . rawurlencode($apiResourceUrl) . "&"
        . rawurlencode("oauth_consumer_key=" . rawurlencode($consumerKey)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . $oauthTimestamp
        . "&oauth_token=" . rawurlencode($accessToken)
        . "&oauth_version=" . rawurlencode($oauthVersion)
        . "&status=" . rawurlencode($tweetText));

    // prepare the signature key
    $sigKey = rawurlencode($consumerSecret) . "&" . rawurlencode($accessTokenSecret);
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true)); 

    // prepare the POST request
    $authHeader = "OAuth oauth_consumer_key=" . rawurlencode($consumerKey) . ","
        . "oauth_nonce=" . rawurlencode($nonce) . ","
        . "oauth_signature_method=" . rawurlencode($oauthSignatureMethod) . ","
        . "oauth_signature=" . rawurlencode($oauthSig) . ","
        . "oauth_timestamp=". rawurlencode($oauthTimestamp) . ","
        . "oauth_token=" . rawurlencode($accessToken) . ","
        . "oauth_version=" . rawurlencode($oauthVersion); 

    $httpPostDataUrl = "status=" . $tweetText; 

    $context = stream_context_create(array("http" => array(
        "method" => "POST",
        "header" => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: " . $authHeader . "\r\n",
        "content" => $httpPostDataUrl)));

    // post the tweet to twitter
    $result = file_get_contents($apiResourceUrl, false, $context);

    print_r(json_decode($result));
}
// show the form
else {
?>
<html>
 <form action="/index.php" method="post">
  <textarea name="tweet" rows="3" cols="50"></textarea>
  <br/>
  <input type="submit" value="Send"/>
 </form>
</html>
<?php
}

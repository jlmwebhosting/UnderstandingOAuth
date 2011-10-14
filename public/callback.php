<?php
// Continue the authorization process by requesting the Access Credentials
// from Twitter.

require "../include/oauthvalues.php";
session_start();

$nonce = md5(mt_rand());
$oauthTimestamp = time();
$oauthVerifier = $_GET["oauth_verifier"]; 

// prepare the signature string
$sigBase = "GET&" . rawurlencode($accessTokenUrl) . "&"
    . rawurlencode("oauth_consumer_key=" . rawurlencode($consumerKey)
    . "&oauth_nonce=" . rawurlencode($nonce)
    . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
    . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
    . "&oauth_token=" . rawurlencode($_SESSION["requestToken"])
    . "&oauth_verifier=" . rawurlencode($oauthVerifier)
    . "&oauth_version=" . rawurlencode($oauthVersion));

// prepare the signature key
$sigKey = $consumerSecret . "&";
$oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

// send the signed request and receive Twitter's response
$requestUrl = $accessTokenUrl . "?"
    . "oauth_consumer_key=" . rawurlencode($consumerKey)
    . "&oauth_nonce=" . rawurlencode($nonce)
    . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
    . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
    . "&oauth_token=" . rawurlencode($_SESSION["requestToken"])
    . "&oauth_verifier=" . rawurlencode($oauthVerifier)
    . "&oauth_version=". rawurlencode($oauthVersion)
    . "&oauth_signature=" . rawurlencode($oauthSig); 
$response = file_get_contents($requestUrl);

// extract and preserve the Authorization Credentials and user information
parse_str($response, $values);
$_SESSION["accessToken"] = $values["oauth_token"];
$_SESSION["accessTokenSecret"] = $values["oauth_token_secret"];
$_SESSION["twitterUserId"] = $values["user_id"];
$_SESSION["twitterUsername"] = $values["screen_name"];

// redirect the user to the application's form
header("Location: /index.php");

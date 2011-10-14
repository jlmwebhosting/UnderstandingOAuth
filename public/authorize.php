<?php
// Initiate the authorization process by sending a signed request to Twitter
// for a Request Token and then redirect the user to Twitter to authorize the
// application.

require "../include/oauthvalues.php";
session_start();

$oauthTimestamp = time();
$nonce = md5(mt_rand());

// prepare the signature string
$sigBase = "GET&" . rawurlencode($requestTokenUrl) . "&"
    . rawurlencode("oauth_consumer_key=" . rawurlencode($consumerKey)
    . "&oauth_nonce=" . rawurlencode($nonce)
    . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
    . "&oauth_timestamp=" . $oauthTimestamp
    . "&oauth_version=" . $oauthVersion);

// prepare the signature key
$sigKey = $consumerSecret . "&";
$oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

// send the signed request and receive Twitter's response
$requestUrl = $requestTokenUrl . "?"
    . "oauth_consumer_key=" . rawurlencode($consumerKey)
    . "&oauth_nonce=" . rawurlencode($nonce)
    . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
    . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
    . "&oauth_version=" . rawurlencode($oauthVersion)
    . "&oauth_signature=" . rawurlencode($oauthSig); 
$response = file_get_contents($requestUrl);

// extract and preserve the request tokens
parse_str($response, $values);
$_SESSION["requestToken"] = $values["oauth_token"];
$_SESSION["requestTokenSecret"] = $values["oauth_token_secret"];

// send the user to Twitter to grant authorization
$redirectUrl = $authorizeUrl . "?oauth_token=" . $_SESSION["requestToken"];
header("Location: " . $redirectUrl);

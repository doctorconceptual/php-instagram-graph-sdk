
<h1>PHP SDK for Instagram Graph API.</h1>

This is a basic SDK for using Instagram Graph API. I basically wrote this SDK when I found that Instagram is going to shutdown their legacy API and encourage to use "Instagram Basic Display API" instead. 

Copyright & License
-------------------
PHP SDK for Instagram Graph API is
Copyright (c) 2020 Muhammad Talal if not otherwise stated. The code
is distributed under the terms of the MIT License. For the full license
text see the LICENSE file.

Versions & Requirements
-----------------------
1.0.0, PHP >=5.4.1

Installation
------------
The preferred installation method is via composer. You can add the library
as a dependency via:

`$ composer require doctorconceptual/php-instagram-graph-sdk`

Usage
-----

<h3>Creating an Instance</h3>

```
<?php

use Instagram\Instagram;

// when you create an app for Instagram Basic Display API, 
// you can get client_id, client_secret & redirect_uri from there
// Here is how you can get started:
// https://developers.facebook.com/docs/instagram-basic-display-api/getting-started

$igAPI = new Instagram($clientID, $clientSecret, redirectURI);
```

<h3>Fetching a token</h3>

```
$authURL = $igAPI->get_authorize_url();
header("Location: {$authURL}");

// after authorization, it should return to the same URL
$code = $_GET['code'];
$token = $igAPI->get_access_token($code);

// Now that a short-lived token is retrieved, you can
// exchange it with a long-live token
$igAPI->set_access_token($token->access_token);
$token = $this->instagram->get_long_lived_token();
// $token now has a long-lived token which has a life of 60 days
// per Instagram API documentation
```

<h3>Get an Instagram User</h3>

```
$igAPI = new Instagram($clientID, $clientSecret);
$igAPI->set_access_token($access_token); // set the previously fetched token
$instagramUserID = 123456; // a valid Instagram user id, this is returned along with the token when fetching token
$user = get_user($instagramUserID);
```

The other endpoints could be used in a similar fashion

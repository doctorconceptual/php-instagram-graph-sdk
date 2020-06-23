<?php

/**
 * This is a basic SDK for using Instagram Graph API.
 * I basically wrote this SDK when I found that Instagram is going to shutdown their legacy API
 * and encourage to use "Instagram Basic Display API" instead.
 */

namespace Instagram;

class Instagram {

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const TOKEN_EXPIRED_CODE = 190;

	public $client_id;
	public $client_secret;
	public $redirect_uri;
	public $scope;
	public $oauth_verifier;
	public $access_token;

	public function __construct($client_id, $client_secret, $redirect_uri = null)
	{
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->redirect_uri = $redirect_uri;
	}

	/**
	 * Set client ID
	 *
	 * @param string $client_id
	 * @return void
	 */
	public function set_client_id($client_id)
	{
		$this->client_id = $client_id;
	}

	/**
	 * Set client secret
	 *
	 * @param string $client_secret
	 * @return void
	 */
	public function set_client_secret($client_secret)
	{
		$this->client_secret = $client_secret;
	}

	/**
	 * Set redirect URI
	 *
	 * @param string $redirect_uri
	 * @return void
	 */
	public function set_redirect_uri($redirect_uri)
	{
		$this->redirect_uri = $redirect_uri;
	}

	/**
	 * Set scope - generally used when requesting token
	 *
	 * @param string $scope
	 * @return void
	 */
	public function set_scope($scope)
	{
		$this->scope = $scope;
	}

	/**
	 * Set access token - access token is used for subsequent calls to API
	 *
	 * @param string $access_token
	 * @return void
	 */
	public function set_access_token($access_token)
	{
		$this->access_token = $access_token;
	}

	/**
	 * Get URL for authorization
	 * This URL is required when starting the auth process
	 *
	 * @return string
	 */
	public function get_authorize_url()
	{
		$params = array(
			'client_id' => $this->client_id,
			'scope' => $this->scope,
			'response_type' => 'code',
			'redirect_uri' => $this->redirect_uri,
		);

		// Authentication request
		$authorize_url = 'https://api.instagram.com/oauth/authorize';
		return "{$authorize_url}?" . http_build_query($params);
	}

	/**
	 * Request and get access token from code
	 *
	 * @param string $code
	 * @return stdClass object
	 */
	public function get_access_token($code)
	{
		if (!$code)
			return false;

		$params = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => $this->redirect_uri,
		);

		$access_token_url = 'https://api.instagram.com/oauth/access_token';

		// Access Token request
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $access_token_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		$response = curl_exec($curl);
		curl_close($curl);
		$token = json_decode($response);

		if (!$token)
			return false;

		return $token;
	}

	/**
	 * checks from Instagram response if there is a token expire error
	 *
	 * @param stdClass $res
	 * @return boolean
	 */
	public function is_token_expire_error($res)
	{
		if (isset($res->error) && $res->error->code == self::TOKEN_EXPIRED_CODE)
			return true;

		return false;
	}

	/**
	 * Get a long-lived Instagram token
	 * Need to pass a short-lived token as access_token
	 *
	 * @return stdClass object
	 */
	public function get_long_lived_token()
	{
		return $this->fetch(
			'https://graph.instagram.com/access_token',
			['grant_type' => 'ig_exchange_token', 'client_secret' => $this->client_secret]
		);
	}

	/**
	 * Get a refresh token
	 * Instagram token normally has a life of 60 days
	 * This requires to fetch a refresh token before the token expires
	 * Refresh token requires to pass a long-lived token as access_token
	 *
	 * @return stdClass object
	 */
	public function get_refresh_token()
	{
		return $this->fetch(
			'https://graph.instagram.com/refresh_access_token',
			['grant_type' => 'ig_refresh_token']
		);
	}

	/**
	 * Get an Instagram user profile.
	 *
	 * @param string $ig_user_id
	 * @return stdClass object
	 */
	public function get_user($ig_user_id)
	{
		return $this->fetch(
			sprintf('https://graph.instagram.com/%s', $ig_user_id),
			['fields' => 'id,username,account_type,ig_id,media_count']
		);
	}

	/**
	 * Get a collection of media on an Instagram user.
	 *
	 * @param string $ig_user_id
	 * @param string $fields (comma separated list of fields)
	 * @return stdClass object
	 */
	public function get_user_media($ig_user_id, $fields = null)
	{
		$params = [];
		if ($fields)
			$params = ['fields' => $fields];

		return $this->fetch(
			sprintf('https://graph.instagram.com/%s/media', $ig_user_id), 
			$params
		);
	}

	/**
	 * Get fields and edges on an image, video, or album.
	 *
	 * @param string $ig_media_id
	 * @param string $fields (comma separated list of fields)
	 * @return array
	 */
	public function get_media($ig_media_id, $fields = null)
	{
		$params = [];
		if ($fields)
			$params = ['fields' => $fields];

		return $this->fetch(
			sprintf('https://graph.instagram.com/%s', $ig_media_id), 
			$params
		);
	}

	/**
	 * The fetch function
	 *
	 * @param string $resource_url
	 * @param array $params
	 * @param string $method GET|POST
	 * @return stdClass object
	 */
	public function fetch($resource_url, $params = [], $method = self::METHOD_GET)
	{
		$params['access_token'] = $this->access_token;
		$curl = curl_init();

		if ($method == self::METHOD_POST)
		{
			curl_setopt($curl, CURLOPT_URL, $resource_url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		}
		else
		{
			$url = sprintf("%s?%s", $resource_url, http_build_query($params));
			curl_setopt($curl, CURLOPT_URL, $url);
		}

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($curl);
		curl_close($curl);

		if (!is_null(json_decode($res, TRUE)))
			$res = json_decode($res);

		return $res;
	}

}

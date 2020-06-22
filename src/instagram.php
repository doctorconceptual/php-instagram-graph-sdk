<?php

// TODO: take this class to git & composer.

class Instagram {

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

	public function set_client_id($client_id)
	{
		$this->client_id = $client_id;
	}

	public function set_client_secret($client_secret)
	{
		$this->client_secret = $client_secret;
	}

	public function set_redirect_uri($redirect_uri)
	{
		$this->redirect_uri = $redirect_uri;
	}

	public function set_scope($scope)
	{
		$this->scope = $scope;
	}

	public function authorize_url()
	{
		return 'https://api.instagram.com/oauth/authorize';
	}

	public function access_token_url()
	{
		return 'https://api.instagram.com/oauth/access_token';
	}

	public function set_oauth_verifier($oauth_verifier)
	{
		$this->oauth_verifier = $oauth_verifier;
	}

	public function set_access_token($access_token)
	{
		$this->access_token = $access_token;
	}

	public function get_authorize_url()
	{
		$params = array(
			'client_id' => $this->client_id,
			'scope' => $this->scope,
			'response_type' => 'code',
			'redirect_uri' => $this->redirect_uri,
		);

		// Authentication request
		$authorize_url = $this->authorize_url();
		return "{$authorize_url}?" . http_build_query($params);
	}

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

		// Access Token request
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->access_token_url());
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

	public function get_user()
	{
		return $this->get(
			'https://graph.instagram.com/me',
			['fields' => 'id,username']
		);
	}

	public function get_user_media($ig_user_id, $fields = null)
	{
		$params = [];
		if ($fields)
			$params = ['fields' => $fields];

		return $this->get(sprintf('https://graph.instagram.com/%s/media', $ig_user_id), $params);
	}

	/**
	 * Get fields and edges on an image, video, or album.
	 *
	 * @param string $ig_media_id
	 * @param string $fields (comma separated fields)
	 * @return array
	 */
	public function get_media($ig_media_id, $fields = null)
	{
		$params = [];
		if ($fields)
			$params = ['fields' => $fields];

		return $this->get(sprintf('https://graph.instagram.com/%s', $ig_media_id), $params);
	}

	public function get($resource_url, $params = [])
	{
		$params['access_token'] = $this->access_token;
		$url = sprintf("%s?%s", $resource_url, http_build_query($params));
		$res = file_get_contents($url);
		if ($res)
			return json_decode($res);

		if (is_null(json_decode($res, TRUE)))
			$res = json_decode($res);

		return $res;
	}

}

<?php
namespace CodeQ\GoogleDocs\Services;

/*
 * This file is part of the CodeQ.GoogleDocs package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use CodeQ\GoogleDocs\Exception\AuthenticationRequiredException;

/**
 *
 * @Flow\Scope("singleton")
 */
class Authentication
{
	/**
	 * @Flow\InjectConfiguration(path="authentication", package="CodeQ.GoogleDocs")
	 * @var array
	 */
	protected $googleDocsSettings;

	function loadSettings(){
		$this->redirectUri = $this->googleDocsSettings['redirectUri'];

		$this->app_name = $this->googleDocsSettings['appName'];
		$this->scopes = implode(' ', array(\Google_Service_Drive::DRIVE));
		$this->client_secret_file = $_SERVER['DOCUMENT_ROOT'].'/'.$this->googleDocsSettings['clientSecretFilePath'];
		$this->accessTokenFile = $_SERVER['DOCUMENT_ROOT'].'/'.$this->googleDocsSettings['accessTokenFilePath'];
		return $this->validateClientSecretFile();
	}

	function configureClient(){


		$this->client = new \Google_Client();
		$this->client->setApplicationName($this->app_name);
		$this->client->setAuthConfigFile($this->client_secret_file);
		$this->client->setAccessType("offline");
		$this->client->setApprovalPrompt("force");
		$this->client->setScopes($this->scopes);
		$this->client->setRedirectUri($this->redirectUri);

		return $this->client;
	}

	function validateClientSecretFile(){
		if(!file_exists($this->client_secret_file)){
			return 404;
		}else{
			$content = json_decode(file_get_contents($this->client_secret_file), true);
			if(array_key_exists('web', $content)){
				if(array_key_exists('client_id', $content['web']) && array_key_exists('client_secret', $content['web'])){
					return 200;
				}
				return 500;
			}
			else{
				return 500;
			}
		}
	}

	function authenticateClient($code = null){
		$accessToken = $this->client->authenticate($code);
		file_put_contents($this->accessTokenFile, json_encode($accessToken));
		$this->client->setAccessToken($accessToken);
		return true;
	}

	function isClientAuthenticated(){
		if(file_exists($this->accessTokenFile)){
			$accessToken = json_decode(file_get_contents($this->accessTokenFile), true);
			if($this->client->isAccessTokenExpired()){
				$this->client->revokeToken();
				if($this->client->getRefreshToken() == null && isset($accessToken['refresh_token'])){
					$refreshToken = $accessToken['refresh_token'];
				}else{
					$refreshToken = $this->client->getRefreshToken();
				}
				if($refreshToken != NULL){
					$this->client->refreshToken($refreshToken);
					file_put_contents($this->accessTokenFile, json_encode(array_merge($this->client->getAccessToken(), $accessToken)));
				}
				return $this->client->getAccessToken() != NULL ? true : false;
			}
		}else{
			return false;
		}
	}
}

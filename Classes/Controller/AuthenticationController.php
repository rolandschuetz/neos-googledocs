<?php
namespace CodeQ\GoogleDocs\Controller;

use Neos\Flow\Annotations as Flow;

use CodeQ\GoogleDocs\Exception\MissingConfigurationException;
use CodeQ\GoogleDocs\Exception\AuthenticationRequiredException;

class AuthenticationController extends \Neos\Flow\Mvc\Controller\ActionController{

	/**
	 * @Flow\InjectConfiguration(path="authentication", package="CodeQ.GoogleDocs")
	 * @var array
	 */
	protected $googleDocsSettings;

	/**
	 * @Flow\Inject
	 * @var \CodeQ\GoogleDocs\Services\Authentication
	 */
	protected $authentication;

	/**
	 * @Flow\Inject
	 * @var \Neos\Flow\I18n\Translator
	 */
	protected $translator;

	public function indexAction(){

		try{
			$requiredSettings = [
				'clientSecretFilePath',
				'accessTokenFilePath',
				'appName',
				'redirectUri'
			];

			foreach($requiredSettings as $setting){
				if($this->googleDocsSettings[$setting] == NULL){
					throw new MissingConfigurationException(
						$this->translator->translateById(
							'msg.missing.settings',
							array($setting),
							null,
							null,
							'Main',
							'CodeQ.GoogleDocs'), 1415783206);
				}
			}

			if($this->request->getHttpRequest()->getArgument('logout') == 'true'){
				$filePath = $_SERVER['DOCUMENT_ROOT'] . '/'. $this->googleDocsSettings['accessTokenFilePath'];
				if(file_exists($filePath))
					unlink($filePath);
			}
			session_start();
			$config = $this->authentication->loadSettings();
			if($config == 200){
				$client = $this->authentication->configureClient();

				if($this->authentication->isClientAuthenticated()){

				}elseif(
					$this->request->getHttpRequest()->getArgument('code')
					&& (
						!isset($_SESSION['code'])
						|| $_SESSION['code']!=$this->request->getHttpRequest()->getArgument('code')
					)
				){
					$_SESSION['code'] = $this->request->getHttpRequest()->getArgument('code');
					$this->authentication->authenticateClient($this->request->getHttpRequest()->getArgument('code'));
				}else{
					$authUrl = $client->createAuthUrl();
					$this->view->assign('authUrl', $authUrl);
				}
			}elseif($config == 404){
				throw new MissingConfigurationException(
					$this->translator->translateById(
						'msg.missing.clientSecret',
						array($this->googleDocsSettings['clientSecretFilePath']),
						null,
						null,
						'Main',
						'CodeQ.GoogleDocs'), 1415783206);
			}elseif($config == 500){
				throw new MissingConfigurationException(
					$this->translator->translateById(
						'msg.malformed.clientSecret',
						array(),
						null,
						null,
						'Main',
						'CodeQ.GoogleDocs'), 1415783206);
			}

		} catch(MissingConfigurationException $exception) {
			$this->addFlashMessage('%1$s', 'Missing configuration', \Neos\Error\Messages\Message::SEVERITY_ERROR, ['message' => $exception->getMessage(), 1415783206]);
		}


	}

}

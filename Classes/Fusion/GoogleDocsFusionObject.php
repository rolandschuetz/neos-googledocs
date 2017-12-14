<?php
namespace CodeQ\GoogleDocs\Fusion;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use CodeQ\GoogleDocs\Exception\MissingConfigurationException;
use CodeQ\GoogleDocs\Exception\AuthenticationRequiredException;

class GoogleDocsFusionObject extends AbstractFusionObject {

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

	/**
	 * @return string
	 */
	public function evaluate() {
		try{
			$this->authentication->loadSettings();
			$client = $this->authentication->configureClient();
			$client_secret_file = $_SERVER['DOCUMENT_ROOT'].'/'.$this->googleDocsSettings['clientSecretFilePath'];
			$accessTokenFile = $_SERVER['DOCUMENT_ROOT'].'/'.$this->googleDocsSettings['accessTokenFilePath'];
			if($this->tsValue('fileId') == ''){
				throw new MissingConfigurationException($this->translator->translateById('msg.missing.fileId',array(),null,null,'Main','CodeQ.GoogleDocs'), 1415783205);
			}

			if($this->authentication->isClientAuthenticated()){
				$drive = new \Google_Service_Drive($client);
				$fileId = $this->tsValue('fileId');
				$response = $drive->files->export($fileId, 'text/html', array('alt' => 'media'));
				$html = new \DOMDocument();
				$html->loadHTML($response);
				$body = $html->getElementsByTagName('body')->item(0);
				$mock = new \DOMDocument;
				foreach ($body->childNodes as $child){
				    $mock->appendChild($mock->importNode($child, true));
				}
				$content = $mock->saveHTML();
				return $content;
			}else{
				throw new AuthenticationRequiredException($this->translator->translateById('msg.error.authentication',array(),null,null,'Main','CodeQ.GoogleDocs'), 1415783206);
			}
			return '';
		}
		catch(AuthenticationRequiredException $exception){

			$message = "<div style='color:red'><h1>".
						$this->translator->translateById(
						'msg.error.authenticate.heading',
						array(),
						null,
						null ,
						'Main',
						'CodeQ.GoogleDocs').
						"</h1><p>".$exception->getMessage()."</p></div>";
				return $message;
		}
		catch(MissingConfigurationException $exception){
			$message = "<div style='color:red'><h1>".
						$this->translator->translateById(
						'msg.missing.configuration',
						array(),
						null,
						null ,
						'Main',
						'CodeQ.GoogleDocs').
						"</h1><p>".$exception->getMessage()."</p></div>";
			return $message;
		}

	}

}

?>

<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Aws\Sdk;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function checkInAction()
    {
        return new ViewModel();
    }
    
    public function ajaxCheckInAction() 
    {
        /**
         * Return value will be a JSON encoded string
         * Using JsonModel() for ajax returns
         * 
         * @var \Zend\View\Model\JsonModel $response
         */
        $response = new JsonModel();
        
        /*
         * Return nothing if it is not an AJAX request
         */
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $response;
        }
        
        /**
         * 
         * @var string $phone
         */
        $phone = (string) $this->params()->fromPost('phone', false);
        
        /**
         * Return error if phone is not given
         */
        if (!$phone) {
            $response->setVariables([
                'success'   => false,
                'message'   => 'Please provide your phone number' 
            ]);
            return $response;
        }
        
        /**
         * Skipping model processing here and 
         * assuming we found the customer by the phone number
         * and returning the expected salutation.
         */
        $customerId = 123;
        $customerName = 'Suat';
        
        $response->setVariables([
            'success' => true,
            'customerId' => $customerId,
            'message' => sprintf('Welcome, %s', $customerName),
        ]);
        return $response;
    }
    
    public function ajaxPollyAction()
    {
        $response = $this->getResponse();
    
        /*
         * Return nothing if it is not an Ajax request
         */
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $response;
        }
        
        $id = $this->params()->fromPost('id', false);
        
        if ($id) {
            
            /**
             * Skipping model processing here and
             * assuming we found the customer by the provided id
             */
            $customerName = 'Suat';
            
            /**
             * AWS IAM credentials
             * 
             * @var array $config
             */
            $config = [
                'version'     => 'latest',
                'region'      => 'us-east-1',
                'credentials' => [
                    'key'    => '***IAMKEY***',
                    'secret' => '***IAMSECRET***',
            ]];
            
            /**
             * Create AWS SDK by provinding the $config for authentication
             * 
             * @var \Aws\Sdk $sdk
             */
            $sdk = new Sdk($config);
            $pollyClient = $sdk->createPolly();
    
            /**
             * Preparing required parameters in this array
             * OutputFormat for desired file type
             * Text to Speech 
             * VoiceId one of the 47 lifelike voices in Polly
             * 
             * @var array $args
             */
            $args = [
                'OutputFormat' => 'mp3',
                'Text' => sprintf('Welcome, %s!', $customerName) ,
                'VoiceId' => 'Joanna',
            ];
    
            /**
             * Polly client's synthesizeSpeech method
             * returns the array contains AudioStream 
             * that will be used as the audio file binary content
             * 
             */
            $pollyResponse = $pollyClient->synthesizeSpeech($args);
            $audioContent = $pollyResponse['AudioStream'];
    
            /**
             * Return audio content by setting necessary headers
             */
            $response->setContent($audioContent);
            $response
                ->getHeaders()
                ->addHeaderLine('Content-Transfer-Encoding', 'chunked')
                ->addHeaderLine('Content-Type', 'audio/mpeg');
             
            return $response;
        }
    }
    
}

<?php
/**
 * @copyright Federico Nicolás Motta
 * @author Federico Nicolás Motta <fedemotta@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @package yii2-appnexusclient
 * @version 1.0.3
 */

namespace fedemotta\appnexusapiclient;
use yii\base\Component;
use yii\base\ErrorException;
use F3\AppNexusClient;

/**
 * Yii2 component wrapping the AppNexus client API library for easy 
 * configuration
 *
 * @author Federico Nicolás Motta <fedemotta@gmail.com>
 * @since 1.0
 */
class Appnexusapiclient extends Component{
    
    // AppNexus API hosts to connect
    const HOST_TESTING = 'http://api-console.client-testing.adnxs.net/';
    const HOST_PRODUCTION = 'http://api.appnexus.com/';
    
    // Default storage type
    const DEFAULT_STORAGE_TYPE = 'Array';
    
    // Default request limit
    const DEFAULT_REQUEST_LIMIT_QUANTITY = 100;
    const DEFAULT_REQUEST_LIMIT_SECONDS = 60;
    const DEFAULT_REQUEST_LIMIT_MESSAGE = 'You have exceeded your request limit of %d per %d seconds for this member, please wait and try again or contact AppNexus for higher limits';
    
    /*
     * @var string specifies the AppNexus API username
     */
    public $username = null;
    /*
     * @var string specifies the AppNexus API password
     */
    public $password = null;
    /*
     * @var string specifies the AppNexus API host
     */
    public $host = self::HOST_TESTING;
    
    /*
     * @var array specifies the available storage types
     */
    public $available_storage_type = ['Array','Apc','Memcached'];
    
    /*
     * @var string specifies the storage type to use
     */
    public $storage_type = self::DEFAULT_STORAGE_TYPE;
    
    /*
     * @var array specifies the storage type settings. The values in the array 
     * are mapped to constructor arguments positionally.
     *
     * Examples:
     * //Array
     * $storage_type_settings = [];
     * //Apc: 
     * $storage_type_settings = ['prefix_', 0];
     * //Memcached
     * $storage_type_settings = [$memcached_object,'prefix_'];
     */
    public $storage_type_settings = [];
    
    
    /*
     * @var int specifies the quantity of the request limit
     */
    public $request_limit_quantity = self::DEFAULT_REQUEST_LIMIT_QUANTITY;
    
    /*
     * @var int specifies the seconds per quantity of the request limit
     */
    public $request_limit_seconds = self::DEFAULT_REQUEST_LIMIT_SECONDS;
    
    /*
     * @var string specifies the message of the request limit
     */
    public $request_limit_message = self::DEFAULT_REQUEST_LIMIT_MESSAGE;
    
    /**
     * @var AppNexusClient API instance
     */
    protected $_appnexusapiclient;
    
    /**
     * Initializes (if needed) and fetches the AppNexusClient API instance
     * @return AppNexusClient instance
     */
    public function getApi()
    {
        if (empty($this->_appnexusapiclient) || !$this->_appnexusapiclient instanceof AppNexusClient\AppNexusClient) {
            $this->setApi();
        }
        return $this->_appnexusapiclient;
    }
    /**
     * Sets the appNexusClient API instance
     */
    public function setApi()
    {
        $this->_appnexusapiclient = new AppNexusClient\AppNexusClient(
            $this->username,
            $this->password,
            $this->host,
            $this->getStorage()
        );
    }
    /**
     * Gets the token storage object
     * @return object token storage
     */
    private function getStorage(){
        switch ($this->storage_type) {
            case 'Apc':
                $storage = new \ReflectionClass('\F3\AppNexusClient\ApcTokenStorage');
                break;
            case 'Memcached':
                $storage = new \ReflectionClass('\F3\AppNexusClient\MemcachedTokenStorage');
                break;
            case 'Array':
            default:
                $storage = new \ReflectionClass('\F3\AppNexusClient\ArrayTokenStorage');
        }
        return $storage->newInstanceArgs($this->storage_type_settings);
    }
    
    /**
     * Make the request checking for limit and raising error
     * @param object $http_method
     * @param string $url
     * @param array $post
     * @param array $headers
     * @return $response
     * @throws ErrorException
     */
    private function make_request($http_method, $url, array $post = array(), array $headers = array()){
        try {
            $response = $this->getApi()->call($http_method, $url, $post, $headers);
        } catch (\F3\AppNexusClient\ServerException $response) {
            // check for the request limit error
            if ($response->getMessage() === sprintf($this->request_limit_message, $this->request_limit_quantity, $this->request_limit_seconds)){
                
                // wait for the time limit seconds
                sleep($this->request_limit_seconds);
                
                // try again the same request
                try {
                    $response = $this->getApi()->call($http_method, $url, $post, $headers);
                }catch (\F3\AppNexusClient\ServerException $response) {
                    throw new ErrorException($response->getMessage());
                }
            }else{
                throw new ErrorException($response->getMessage());
            }
        }
        return $response;

    }
        
    /**
     * Do GET HTTP call
     *
     * @param string $url
     * @param array $headers
     *
     * @return object response
     */    
    public function get($url, array $headers = array())
    {
        return $this->make_request(AppNexusClient\HttpMethod::GET,$url, array(), $headers);
    }
    
    /**
     * Do POST HTTP call
     *
     * @param string $url
     * @param array $post
     * @param array $headers
     *
     * @return object response
     */    
    
    public function post($url, array $post = array(), array $headers = array())
    {
        return $this->make_request(AppNexusClient\HttpMethod::POST, $url, $post, $headers);
    }
    /**
     * Do PUT HTTP call
     *
     * @param string $url
     * @param array $post
     * @param array $headers
     *
     * @return object response
     */    
    
    public function put($url, array $post = array(), array $headers = array())
    {
        return $this->make_request(AppNexusClient\HttpMethod::PUT, $url, $post, $headers);
    }
    
    /**
     * Do DELETE HTTP call
     *
     * @param string $url
     * @param array $post
     * @param array $headers
     *
     * @return object response
     */    
    public function delete($url, array $post = array(), array $headers = array())
    {
        return $this->make_request(AppNexusClient\HttpMethod::DELETE, $url, $post, $headers);
    } 
}
<?php
/**
 * @copyright Federico Nicolás Motta
 * @author Federico Nicolás Motta <fedemotta@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @package yii2-appnexusclient
 * @version 1.0.2
 */

namespace fedemotta\appnexusapiclient;
use yii\base\Component;
use F3\AppNexusClient;

/**
 * Yii2 component wrapping the AppNexus client API library for easy 
 * configuration
 *
 * @author Federico Nicolás Motta <fedemotta@gmail.com>
 * @since 1.0
 */
class Appnexusapiclient extends Component{
    
    //AppNexus API hosts to connect
    const HOST_TESTING = 'http://api-console.client-testing.adnxs.net/';
    const HOST_PRODUCTION = 'http://api.appnexus.com/';
    
    //default storage type
    const DEFAULT_STORAGE_TYPE = 'Array';
    
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
    /*
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
     * Do GET HTTP call
     *
     * @param string $url
     * @param array $headers
     *
     * @return object response
     */    
    public function get($url, array $headers = array())
    {
        return $this->getApi()->call(AppNexusClient\HttpMethod::GET,$url, array(), $headers);
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
        return $this->getApi()->call(AppNexusClient\HttpMethod::POST, $url, $post, $headers);
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
        return $this->getApi()->call(AppNexusClient\HttpMethod::PUT, $url, $post, $headers);
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
        return $this->getApi()->call(AppNexusClient\HttpMethod::DELETE, $url, $post, $headers);
    } 
}
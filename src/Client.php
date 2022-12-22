<?php

namespace Integrity\Trolley;

use GuzzleHttp\Client as Guzzle;
use Integrity\Trolley\Header;
use Integrity\Trolley\Auth\AuthInterface;
use Integrity\Trolley\Http\AbstractController;

/**
 * The entrypiont for the client
 */
abstract class Client {

    /** @var string The API Base URL */
    private $baseUri;
    /** @var string the API Key */
    private $auth;
    /** @var array An array of controllers for the client to use */
    protected $controllers = [];


    /**
     * @param string $baseUri The API Base URI.
     * @param string $auth The API key.  
     */
    public function __construct($baseUri, Headers $headers = null)
    {
        // create a new guzzle http client
        $guzzle = new Guzzle([
            'base_uri' => $baseUri,
            'headers' => $headers() //invoke object as function
        ]);

        // initialize the object
        $this->init($guzzle);
    }

    /**
     * Create instances of all of the Controllers
     */
    private function init(Guzzle $guzzle)
    {
        foreach ($this->controllers as $controller) {

            // create an instance of the controller
            $controller = new $controller($guzzle);

            $name = $controller->getName();

            /** add the controller to a property on the class */
            $this->{$name} = $controller; 
        }
    }

    /**
     * Call client controllers magically
     */
    public function __call($name, $arguments)
    {

        if(!property_exists($this, $name)) {
            throw new \Exception('Endpoint not defined.');
        } 

        return $this->$name;
    }
}
<?php

namespace Integrity\Trolley\Http;

use GuzzleHttp\Client;
use Integrity\Trolley\Http\ControllerInterface;
use Integrity\Trolley\Collections\Collection;
use Integrity\Trolley\Entities\EntityInterface;
use Integrity\Trolley\Http\Exceptions\DefaultMethodException;

/**
 * @method Collection index()
 * @method bool store(EntityInterface $entity)
 * @method EntityInterface show($id)
 * @method bool update($id, EntityInterface $entity)
 * @method bool destroy($id)
 */

 /**
  * Will load the associated entity automatically based on naming convetion (if it exists),
  * otherwise the user may specify the location of the entity. This is also important if the classname 
  * is not easilty converted automatically.
  */
abstract class AbstractController implements ControllerInterface
{

    const ENTITY_NAMESPACE = '\Integrity\ScribbleAI\Entities';
    
    const ALLOWED_METHODS = [
        'index',
        'store',
        'show',
        'update',
        'destroy'
    ];

    /** @var Client */
    private $client;

    protected $name = null;

    /** NO LEADING SLASH */
    protected $endpointUri = null;

    /** @var array The methods a user is allowed to invoke */
    protected $methods = [];

    /** user may define if controller name not parseable */
    protected $entity = null;

    /** when class initiated, then controller entity will be set */
    private $controllerEntity;

    public function __construct(Client $client) {
        $this->client = $client;

        /**
         * Set the name of the entity associated with this controller.  If none is passed as a parameter in the
         * constructor, we'll set it with `setEntity`
         */
        //$entityName !== null && $this->entityName = $entityName;

        /** Set the `EntityInterface` associated with this constructor.
         * if the user has not explicitly defined one
         */
        $this->setEntity();

    }

    public function getName() {

        if(is_null($this->name)) {
            throw new \Exception('Controller name not defined');
        }

        return $this->name;
    }

    /**
     * Reflects this class in order to derive the shortname of this class
     *
     * @return string The shortname of this class
     */
    private function getClassName(): string
    {
        /** @var ReflectionClass $reflectionClass A reflection of this class ($this) */
        $reflectionClass = new \ReflectionClass($this);

        /** Return the short name of the class */
        return $reflectionClass->getShortName();
    }

    /**
     * Get an instance of the entity associated with this controller.  If an `entityName` is set in the constructor, it
     * will use that.  Otherwise, it will assume the entity name is the singular name of the controller name.
     * For example, a controller with name `ProductsController` will use an entry named `Product` which should exist in
     * the `Integrity\Fishbowl\Entities\Products` namespace.
     *
     * @return string An instance of the entity associated with this controller
     */
    private function setEntity(): void
    {
        /**
         * Check if the entityName property is NOT set.
         * If it is not, derive the name from this class name
         */
        if (!$this->entity) {
            /**
             * Get a pural version of the entity name from the name of the controller class.
             */

            /**
             * @var string $plural The plural version of the entity name.  It's the Controller class name minus the word
             * "Controller" (e.g. "CustomersController" becomes "Customers").  This will be the plural of the noun we
             * want to name the entity class.
             */
            $plural = str_replace('Controller', '', $this->getClassName());

            /**
             * First, we'll set the singular variable to the plural, for the cases where singular and plural are the
             * same (e.g. "deer").  Then we'll check to see if the last letter of the plural is an "s".  If it is, we'll
             * remove it.  If it isn't, we'll leave it alone.  This will give us the singular version of the noun.
             */

            /** @var string $singular The singular version of the entity name, derived from plural */
            $singular = (substr($plural, -1) == 's') ? substr($plural, 0, -1) : $plural;

            /** Set the entity name derived from the controller class name */
            //$controllerEntity = self::ENTITY_NAMESPACE . '\\' . $plural . '\\' . $singular;
            $controllerEntity = self::ENTITY_NAMESPACE . '\\' . $singular;

            $this->controllerEntity = new $controllerEntity();
        } else {
            $this->controllerEntity = new $this->entity;
        }
    }

    public function getEntity()
    {
        return $this->controllerEntity;
    }
 
    /**
     * Call controller methods magically as properties of this class
     */
    public function __call($name, $arguments)
    {
        if(in_array($name, self::ALLOWED_METHODS) || in_array($name, $this->methods)) {

            $method = '_' . $name;

            try {
                return $this->$method($arguments);
            } catch(\Exception $e) {
                // handle response exceptions here
            }
        } else {

            throw new DefaultMethodException();
        }
    }

    // GET
    public function _index(): Collection
    {
        $response = $this->client->request(
            'GET', 
            $this->endpointUri
        );

        $result = json_decode((string) $response->getBody(), true);

        return new Collection($result['data'], $this->getEntity(), 'id');
        // convert response to collection
    }

    // POST
    public function _store(EntityInterface $entity): bool
    {
        $response = $this->client->request(
            'POST', 
            $this->endpointUri, 
            ['body' => $entity->toArray()]
        );

        return true;

        // return bool depending on status
    }

    // GET
    public function _show($arguments): EntityInterface
    {
        $id = $arguments[0];

        $response = $this->client->request(
            'GET', 
            $this->endpointUri . '/' . $id 
        );

        $result = (string) $response->getBody();
        $body = json_decode($result, true);
        $data = json_encode($body['data']);
     
        return $this->controllerEntity::fromJSON($data);
    }

    // PUT/PATCH
    public function _update($id, EntityInterface $entity, $method = 'PATCH'): bool
    {
        // only allow PUT or PATCH for the method argument
        $method = ($method === 'PATCH') ? $method : 'PUT';

        $response = $this->client->request(
            $method, 
            $this->endpointUri, 
            ['body' => $entity->toArray()]
        );

        // return bool depending on status

        return true;
    }

    // DELETE
    public function _destroy($id): bool
    {
        $response = $this->client->request(
            'DELETE', 
            $this->endpointUri
        );

        // return bool depending on status

        return true;
    }
}
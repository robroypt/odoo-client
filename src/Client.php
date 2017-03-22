<?php

/**
 * (c) Rob Roy <rob@pervasivetelemetry.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OdooClient;

use Ripcord\Ripcord;

/**
 * Odoo is an PHP client for Odoo's xmlrpc api that uses the Ripcord library.
 * This client should be compatible with version 6 and up of Odoo/OpenERP.
 *
 * This client is inspired on the OpenERP api from simbigo and the robroypt\Odoo library from
 * Jacob Steringa and uses a more or less similar API.
 * Instead of the Zend XMLRpc and Xml libraries it has been rewritten to use the the
 * Ripcord RPC library used in the Odoo Web API documentation.
 *
 * @author  Rob Roy <rob@pervasivetelemetry.com.au>
 */
class Client
{
	/**
 * Host to connect to
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Unique identifier for current user
	 *
	 * @var integer
	 */
	protected $uid;

	/**
	 * Current users username
	 *
	 * @var string
	 */
	protected $user;

	/**
	 * Current database
	 *
	 * @var string
	 */
	protected $database;

	/**
	 * Password for current user
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Ripcord Client
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * XmlRpc endpoint
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Odoo constructor
	 *
	 * @param string     $host       The url
	 * @param string     $database   The database to log into
	 * @param string     $user       The username
	 * @param string     $password   Password of the user
	 */
	public function __construct($host, $database, $user, $password)
	{
		$this->host = $host;
		$this->database = $database;
		$this->user = $user;
		$this->password = $password;
	}

	/**
	 * Get version
	 *
	 * @return array Odoo version
	 */
	public function version()
	{
		$response = $this->getClient('common')->version();

		return $response;
	}

	/**
	 * Search models
	 *
	 * @param string  $model    Model
	 * @param array   $criteria Array of criteria
	 * @param integer $offset   Offset
	 * @param integer $limit    Max results
	 *
	 * @return array Array of model id's
	 */
	public function search($model, $criteria, $offset = 0, $limit = 100)
	{
		$response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'search',
            [$criteria],
            ['offset'=>$offset, 'limit'=>$limit]
        );

		return $response;
	}

	/**
	 * Search_count models
	 *
	 * @param string  $model    Model
	 * @param array   $criteria Array of criteria
	 *
	 * @return array Array of model id's
	 */
	public function search_count($model, $criteria)
	{
		$response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'search_count',
            [$criteria]
        );

		return $response;
	}

	/**
	 * Read model(s)
	 *
	 * @param string $model  Model
	 * @param array  $ids    Array of model id's
	 * @param array  $fields Index array of fields to fetch, an empty array fetches all fields
	 *
	 * @return array An array of models
	 */
	public function read($model, $ids, $fields = array())
	{

        $response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'read',
            [$ids],
            ['fields'=>$fields]
        );

		return $response;
	}

	/**
	 * Search and Read model(s)
	 *
	 * @param string $model     Model
     * @param array  $criteria  Array of criteria
	 * @param array  $fields    Index array of fields to fetch, an empty array fetches all fields
     * @param integer $limit    Max results
	 *
	 * @return array An array of models
	 */
	public function search_read($model, $criteria, $fields = array(), $limit=100)
	{
        $response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'search_read',
            [$criteria],
            ['fields'=>$fields,'limit'=>$limit]
        );

		return $response;
	}

    /**
   	 * Create model
   	 *
   	 * @param string $model Model
   	 * @param array  $data  Array of fields with data (format: ['field' => 'value'])
   	 *
   	 * @return integer Created model id
   	 */
   	public function create($model, $data)
   	{
        $response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'create',
            [$data]
        );

//        print_r($response);
   		return $response;
   	}

	/**
	 * Update model(s)
	 *
	 * @param string $model  Model
	 * @param array  $id     Model id to update
	 * @param array  $fields A associative array (format: ['field' => 'value'])
	 *
	 * @return array
	 */
	public function write($model, $id, $fields)
	{
        $response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'write',
            [
                 [$id],
                $fields
            ]
        );

		return $response;
	}

	/**
	 * Unlink model(s)
	 *
	 * @param string $model Model
	 * @param array  $ids   Array of model id's
	 *
	 * @return boolean True is successful
	 */
	public function unlink($model, $ids)
	{
        $response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'unlink',
            [$ids]
        );

		return $response;
	}

	/**
	 * Get XmlRpc Client
	 *
	 * This method returns an XmlRpc Client for the requested endpoint.
	 * If no endpoint is specified or if a client for the requested endpoint is
	 * already initialized, the last used client will be returned.
	 *
	 * @param null|string $path The api endpoint
	 *
	 * @return Client
	 */
	protected function getClient($path = null)
	{
		if ($path === null) {
			return $this->client;
		}

		if ($this->path === $path) {
			return $this->client;
		}

		$this->path = $path;

		$this->client = Ripcord::client($this->host . '/' . $path);

        return $this->client;
	}

    /**
	 * Get uid
	 *
	 * @return int $uid
	 */
	protected function uid()
	{
		if ($this->uid === null) {
			$client = $this->getClient('common');

			$this->uid = $client->authenticate(
				$this->database,
				$this->user,
				$this->password,
                array()
			);
		}

		return $this->uid;
	}
}

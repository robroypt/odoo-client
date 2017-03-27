# OdooClient

OdooClient is an [Odoo][1] client for PHP. It is inspired on [OpenERP API][2] from simbigo and [OdooClient][3] from jacobsteringa and uses a more or less similar API.

However, instead of its own XML-RPC client or the Zend XML-RPC libraries it uses the [Ripcord][4] RPC library [as implemented by DarkaOnline][5] -- this is the library used in the [Odoo Web Service API documentation][6].

## Supported versions

This library should work with Odoo 8 or later. If you find any any incompatibilities, please create an issue or submit a pull request.

## Usage

Instantiate a new client.

```php
use OdooClient\Client;
........
$url = 'example.odoo.com/xmlrpc/2';
$database = 'example-database';
$user = 'user@email.com';
$password = 'yourpassword';

$client = new Client($url, $database, $user, $password);
```

For the client to work you have to include the `/xmlrpc/2` part of the url.

### xmlrpc/2/common endpoint

Getting version information.

```php
$client->version();
```

There is no login/authenticate method. The client does authentication for you, that is why the credentials are passed as constructor arguments.

### xmlrpc/2/object endpoint

Search for records.

```php
$criteria = [
  ['customer', '=', true],
];
$offset = 0;
$limit = 10;

$client->search('res.partner', $criteria, $offset, $limit);
```

Search and count records.

```php
$criteria = [
  ['customer', '=', true],
];

$client->search_count('res.partner', $criteria);
```

Reading records.

```php
$ids = $client->search('res.partner', [['customer', '=', true]], 0, 10);

$fields = ['name', 'email', 'customer'];

$customers = $client->read('res.partner', $ids, $fields);
```

Search and Read records.

```php
$criteria = [
  ['customer', '=', true],
];

$fields = ['name', 'email', 'customer'];

$customers = $client->search_read('res.partner', $criteria, $fields, 10);
```

Creating records.

```php
$data = [
  'name' => 'John Doe',
  'email' => 'foo@bar.com',
];

$id = $client->create('res.partner', $data);
```

Updating records.

```php
// change email address of user with current email address foo@bar.com
$ids = $client->search('res.partner', [['email', '=', 'foo@bar.com']], 0, 1);

$client->write('res.partner', $ids, ['email' => 'baz@quux.com']);

// 'uncustomer' the first 10 customers
$ids = $client->search('res.partner', [['customer', '=', true]], 0, 10);

$client->write('res.partner', $ids, ['customer' => false]);
```

Deleting records.

```php
$ids = $client->search('res.partner', [['email', '=', 'baz@quuz.com']], 0, 1);

$client->unlink('res.partner', $ids);
```

[1]: https://www.odoo.com/
[2]: https://bitbucket.org/simbigo/openerp-api
[3]: https://github.com/jacobsteringa/OdooClient
[4]: https://github.com/poef/ripcord
[5]: https://github.com/DarkaOnLine/Ripcord
[6]: https://www.odoo.com/documentation/9.0/api_integration.html

# License
MIT License. Copyright (c) 2017 Rob Roy.

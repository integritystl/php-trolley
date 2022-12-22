# Trolley PHP HTTP Client

A HTTP Client and Entity Library used to build client libraries for REST APIs. 

## Quick Start

There are three main components to consider when configuring your application to use Trolley: Client, Controllers, and Entities. In each case you will extend an abstract class of its type to define your objects. 

### Entity Configuration 

The Entity is the model for the data sent/recieved from a REST API. You will configure one or more entities depending on how many and the types of endpoints you plan to communicate with. 

For our example lets consider an API that communicates with a Blog service. An example JSON response from that service may look like this:

```JSON
{
    "data": {
        "id": "SqSDsfSF54f4SSDFs09",
        "title": "My Fanstastic Blog Post",
        "body": "Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce consectetur lacus vitae iaculis sagittis. Vivamus sed tristique risus. Ut ac ante vitae mauris rutrum maximus eget et erat. Sed ipsum nulla, congue sit amet lectus sed, ultricies mollis magna. Praesent et augue id felis venenatis dapibus. Suspendisse in iaculis ipsum, nec ornare nulla. Donec tincidunt sapien nulla.",
        "status": 1,
        "keywords": "Lorem, Ipsum, Dolor",
        "author": "John Doe",
        "createdAt": "12/01/2022 10:20:22",
        "updatedAt": "12/10/2022 02:20:22"
    }
}
```

So you would configure your entity as follows:

```PHP
<?php

namespace My\App\Entities;

class Blog extends \Integrity\Trolley\Entities\Entity
{
    protected $fields = [
        'id:text',
        'title:text',
        'body:text',
        'status:number',
        'keywords:text',
        'createdAt:datetime',
        'updatedAt:datetime'
    ];
}

```

### Controller Configuration

The controller uses the client library, and makes the requests to the API. The controller methods are predefined and only require you to define which are available. The controller methods will either return a boolean status for success (store, update, destroy), an entity (show), or a collection of entities (index).

Continuing with the blgo example here is how you would configure your Blogs Controller:

```PHP
<?php

namespace My\App\Controllers;

class BlogsController extends \Integrity\Trolley\Http\AbstractController
{
    protected $name = "blogs";

    protected $endpointUri = "blogs";

    protected $methods = [
        'index',
        'show'
    ];
}

```

In this example the **name** is "blogs". This will be used at the method name when chainging the methods to create the request. 

The **endpointUri** is "blogs". This is the URI that will be appended to the base URI configured in the client that the request is sent to. 

The **methods** are the available/allowed methods. These are also used when chaining the methods when creating the request. Either the enpoint only has these methods available, or you may choose to limit what the end user has access to through this configuration.


#### Controller Methods

- **index** | Makes a GET request to return a collection of entities of a particular type
- **store** | Makes a POST request to create a new item of a type
- **show** | Makes a GET requst to return an item of a particular type
- **update** |  Makes a PUT/PATCH request to update an item
- **destroy** | Makes a DELETE request to remove an item

### Client Configuration

The client is the entrypoint for the entire HTTP Library. Here you can define your headers, authentication, base uri, and available controllers. 

Configure your controllers:

```PHP
<?php

namespace My\App;

class Client extends \Integrity\Trolley\Client
{
    protected $controllers = [
        \My\App\Controllers\BlogsController::class,
    ];
}
```

## Using the Client 

Now you can bring it all together by creating an instance of the client, and making requests. In the following example we are using the Blog controller and entity we have already configured. Additionally, this api uses a simple api key header for its authentication.

```PHP
<?php 

use My\App\Client;
use Integrity\Trolley\Headers;

$headers = new Headers();
$headers->add('x-api-key', '7d53817b954c4ed0be5a0fa3a4e06ee0');

$client = new Client(
    'http://myapi.com/api/v1/',
    $headers
);

```

Make a request to get all blogs to see get a Collection of Blog entities with values from the API:

```PHP
$blogs = $client->blogs->index();
```


Make a request to get a specific blog to get an instance of the Blog entity with values from the API:

```PHP
$blog = $client->blogs->show('MJ8NbuhiSymAxCvqsRvX');
```


LtCommons Bundle for Symfony 2
==============================

Installation
------------
```
composer require ubc/ltcommons-bundle
```

Usage
-----
### With Symfony 2

First, enable the Bundle
```php
// app/AppKernel.php

class AppKernel extends Kernel
{
    // ...

    public function registerBundles()
    {
        $bundles = array(
            // ...,
            new UBC\LtCommonsBundle\LtCommonsBundle(),
        );

        // ...
    }
}
```

Then, add configuration in `Configuration` section to your app/config_*.yml

### With DI container
```php
require 'vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

// Bootstrap the JMS custom annotations for Object to Json mapping
\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation',
    dirname(__FILE__).'/vendor/jms/serializer/src'
);

$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
$container->registerExtension(new \UBC\LtCommons\UBCLtCommonsExtension());

$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('config.yml');

$container->compile();

$codes = $container->get('ubc_lt_commons.service.department_code')->getDepartmentCodes();
```

Configuration
-------------

```yml
ubc_lt_commons:
  providers:
    sis:
      base_url: http://sisapi.example.com
      auth:
        module: auth2
        rpc_path: /auth/rpc
        username: service_username 
        password: service_password 
        service_application: service_app
        service_url: https://www.auth.stg.id.ubc.ca
      http_client: Guzzle
      serializer: JMS
    xml:
      path: /path/to/data
      serializer: JMS
```
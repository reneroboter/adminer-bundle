TRIOTECH Adminer Bundle
=======================

Access your Symfony-defined databases via Adminer.

Security Notice
---------------

When loaded, this bundle exposes your databases to the world, you have to be careful when using it.

The configuration below shows how to load the bundle for your DEV environment. With a default Symfony installation, the DEV env is only accessible by localhost, if you want to access your databases from a production server, be sure to protect the path to adminer.

Installation
------------

1. Add the bundle to your project's dependencies

```bash
composer require triotech/adminer-bundle
```

2. Load the bundle in the dev environment

AppKernel.php:
```php
<?php
    // public function registerBundles()
    if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
        // ...
        $bundles[] = new \Triotech\AdminerBundle\TriotechAdminerBundle();
        // ...
    }
    // ...
```

routing_dev.yml:
```yaml
triotech_adminer:
  resource: "@TriotechAdminerBundle/Resources/config/routing.yml"
  prefix: /_adminer
```

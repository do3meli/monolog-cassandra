# monolog-cassandra
This package contains a [monolog](https://github.com/Seldaek/monolog) handler for Cassandra and is based on the [DataStax PHP-Driver](https://github.com/datastax/php-driver).

## Installation
Apart from the usual composer installation you will have to compile the DataStax C++ Driver wich is part of the [DataStax PHP-Driver](https://github.com/datastax/php-driver) and add it to your PHP configuration. To keep things as easy as possible i have added a step by step guide.

1. let composer know that you want to use this monolog-cassandra package
   ```
   composer require do3meli/monolog-cassandra
   ```
   
2. build the Cassandra C++ Driver
   ```
   sudo apt-get install git g++ make cmake libuv-dev libssl-dev php5 php5-dev libgmp-dev libpcre3-dev
   cd vendor/datastax/php-driver/
   git submodule update --init
   cd ext
   sudo ./install.sh
   ```
   
3. add the following line to `/etc/php5/cli/php.ini` and `/etc/php5/apache2/php.ini`
   ```
   extension=/usr/lib/php5/20121212/cassandra.so
   ```
   
## Usage
The following example shows the Cassandra monolog handler in action:
```
// require all composer libraries
require 'vendor/autoload.php';

// create the cassandra database connection
$cassandradb  = Cassandra::cluster()
                    ->withContactPoints("127.0.0.1")
                    ->withPort(9042)
                    ->build()
                    ->connect("your-keyspace");

// create cassandra monolog handler
$cassandraHandler = new \CassandraHandler\CassandraHandler($cassandradb);

// create monolog logger object
$logger = new \Monolog\Logger('general');

// add handler to monolog
$logger->pushHandler($cassandraHandler);

// now log messages as usual
$logger->addInfo('My logger is now ready');
```
<?php

namespace CassandraHandler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Cassandra\SimpleStatement;
use Cassandra\ExecutionOptions;
use Cassandra\Timestamp;

/**
 * This class is a handler for Monolog, which can be used
 * to write records in a Cassandra database table
 *
 * Class CassandraHandler
 * @package do3meli\monolog-cassandra
 */
class CassandraHandler extends AbstractProcessingHandler {

    /**
     * @var string the table to store the logs in
     */
    private $table = 'logs';

    /**
     * @var bool defines whether the table has been initialized
     */
    private $initialized = false;
    
    /**
     * @var bool defines whether the table has been initialized
     */
    private $cassandraConnection;

    /**
     * Constructor of this class, sets some instance vars and calls parent constructor
     *
     * @param Cassandra\DefaultSession $session    Cassandra session for the database
     * @param string $table                        Table in the database to store the logs in
     * @param bool|int $level                      Debug level which this handler should store
     * @param bool $bubble                         Monolog bubble status code
     */
    public function __construct($session, $table = 'logs', $level = Logger::DEBUG, $bubble = true) {
        $this->cassandraConnection = $session;
        $this->table = $table;
        parent::__construct($level, $bubble);
    }


    /**
     * Initializes this handler by creating the table if it not exists
     *
     * @return void
     */
    private function initialize() {
        
        // lets create the table if it does not yet exists 
        $this->cassandraConnection->execute(
            new SimpleStatement("CREATE TABLE IF NOT EXISTS ".$this->table." (channel VARCHAR, level INT, message TEXT, time timestamp, PRIMARY KEY (channel, level, time) )")
        );
        
        // ok we are now initialized
        $this->initialized = true;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  $record[]
     * @return void
     */
    protected function write(array $record) {

        if (!$this->initialized) {
            $this->initialize();
        }
        
        // create a cassandra timestamp object
        $time = new Timestamp($record['datetime']->format('U'));
              
        // now insert the data to cassandra
        $this->cassandraConnection->execute(
            new SimpleStatement("INSERT INTO ".$this->table." (channel, level, message, time) VALUES (?, ? , ?, ?)"),
            new ExecutionOptions(array('arguments' => array($record['channel'],$record['level'],$record['message'],$time )))
        );

    }


}

?>


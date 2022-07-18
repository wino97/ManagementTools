<?php
namespace CloudTechSolutions\ManagementTools\Config;

class Config {
    private $properties = array();

    public function __construct()
    {
        $this->load();
    }

    public function get(string $property, bool $create = false):string
    {
        if( ($create === true) && ($this->properties->$property === NULL) ){
            $this->set($property);
            $this->load();
        }
        return $this->properties->$property;
    }

    public function set(string $property, string $value = '')
    {
        $config = $this->read();
        var_dump($config);
        if($value === '') $config->$property = $this->randomKey();
        else $config->$property = $value;
        $this->save($config);
    }

    public function delete(string $property)
    {
        $config = $this->read();
        unset($config->properties->$property);
        $this->save($config);
    }

    private function randomKey():string
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    private function load()
    {
        $this->properties = $this->read();
    }

    public function read()
    {
        if( ( !file_exists(ABSPATH . '/config.json') ) || ( json_decode(file_get_contents(ABSPATH . '/config.json')) === NULL ) ) {
            $config = (object)[
                'apiKey' => $this->randomKey(),
                'apiSecret' => $this->randomKey(),
            ];
            $this->save($config);
        }
        return json_decode(file_get_contents(ABSPATH . '/config.json'));
    }

    private function save(object $config)
    {
        $json = json_encode($config, JSON_PRETTY_PRINT);
        file_put_contents(ABSPATH . '/config.json', $json);
    }
}

<?php
namespace CloudTechSolutions\ManagementTools;

class Main {
    public function __construct()
    {
        
    }

    public function __destruct()
    {
        
    }

    public function activate()
    {
        add_action('rest_api_init', function () {
            register_rest_route( 'ctsit/manager', 'backup(?P<api>[a-zA-Z0-9-]+)&(?P<secret>[a-zA-Z0-9-]+)&(?P<cmd>[a-zA-Z0-9-]+)',array(
                        'methods'  => 'GET',
                        'callback' => array( new Api, 'ctsit_api')
                ));
        });
    }

    public function deactivate()
    {

    }

    public function uninstall()
    {

    }
}
//  Activation
register_activation_hook( __FILE__, );
//  Deactivation

//  Uninstall
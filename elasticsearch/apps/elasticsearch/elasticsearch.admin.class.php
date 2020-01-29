<?php
namespace App;

/**
 * Elastic Search - Admin class
 * 
 * An alternative site search function for SCHLIX CMS using Elasticsearch. Combo extension consisting of App and Block.
 *
 * @copyright 2020 Roy H
 *
 * @license MIT
 *
 * @version 1.0
 * @package elasticsearch
 * @author  Roy H <ryhdr@maysora.com>
 * @link    https://github.com/ryhdr/elasticsearch-schlix
 */
class ElasticSearch_Admin extends \SCHLIX\cmsAdmin_Basic {

    public function __construct() {
        // Data: Item
        $methods = array('standard_main_app' => 'Main Page',);
        
        parent::__construct('basic', $methods);      
    }

    public function Run() {
        switch (fget_alphanumeric('action')) {
            case 'updateindex' :
                $this->app->updateIndex();
                $this->returnToMainAdminApplication();
                break;
            default: return parent::Run();
        }
        return true;
    }
}

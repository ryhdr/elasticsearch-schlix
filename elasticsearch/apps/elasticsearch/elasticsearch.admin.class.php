<?php
namespace App;

class ElasticSearch_Admin extends \SCHLIX\cmsAdmin_Basic {

    public function __construct() {
        // Data: Item
        $methods = array('standard_main_app' => 'Main Page',);
        
        parent::__construct('basic', $methods);      
    }

    private function isConfigsChanged($config_names, $original, $updated) {
        foreach($config_names as $name) {
            if ($original[$name] != $updated[$name]) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @global \SCHLIX\cmsConfigRegistry $SystemConfig
     * @param arrah $datavalues
     * @return array
     */
    public function getSaveConfigValidationErrorList($datavalues) {
        global $SystemConfig;
        $this->update_host_after_save_config = false;
        $this->update_index_after_save_config = false;
        if (!$this->app->isIndexExists())
            return false;
        $app_name = $this->app->getFullApplicationName();
        $original = $SystemConfig->get($app_name);
        $this->update_host_after_save_config = $this->isConfigsChanged([
            'int_replicas'
        ], $original, $datavalues);
        $this->update_index_after_save_config = $this->isConfigsChanged([
            'str_index_name',
            'array_enabled_apps',
            'int_value_max_length'
        ], $original, $datavalues);
        return [];
    }

    public function forceRefreshMenuLinks() {
        $this->app->configs(false); // refresh configs cache
        if ($this->update_host_after_save_config) {
            $this->app->initIndex(true);
        }
        if ($this->update_index_after_save_config) {
            $this->app->updateIndex();
        }
    }

    public function Run() {
        switch (fget_alphanumeric('action')) {
            case 'updateindex' :
                $this->app->updateIndex();
                $this->returnToMainAdminApplication();
                break;
            case 'deleteindex' :
                $this->app->deleteIndex();
                $this->returnToMainAdminApplication();
                break;
            default: return parent::Run();
        }
        return true;
    }
}

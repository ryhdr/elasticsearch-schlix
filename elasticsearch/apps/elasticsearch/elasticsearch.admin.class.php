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
            'int_value_max_length',
            'bool_public_items_only'
        ], $original, $datavalues);
        return [];
    }

    public function forceRefreshMenuLinks() {
        $this->app->config(NULL, false); // refresh configs cache
        if ($this->update_host_after_save_config) {
            $this->app->initIndex(true);
        }
        if ($this->update_index_after_save_config) {
            $this->app->updateIndex();
        }
    }

    /**
     * Return string formatted as singular or plural for getDateTimeDiffStr function
     * @param integer $i
     * @param string $singular
     * @param string $plural
     * @return string
     */
    private function formatDateDiffStr($i, $singular, $plural = NULL) {
        if(!$plural) $plural = $singular . 's';
        return $i . ' ' . (($i == 1) ? $singular : $plural);
    }

    /**
     * Return difference string between 2 DateTime object.
     * Example returns using 2 max_parts:
     *   - "1 year 3 months"
     *   - "2 months 1 day"
     *   - "3 minutes 3 seconds"
     *   - "20 seconds"
     * @param DateTime $date1
     * @param DateTime $date2
     * @param integer $max_parts
     * @return string
     */
    public function getDateTimeDiffStr($date1, $date2, $max_parts = 2) {
        $diff = date_diff($date1, $date2, true);
        if(!$diff) return false;
        $arr = [];
        $parts = [
            'y' => ['year'],
            'm' => ['month'],
            'd' => ['day'],
            'h' => ['hour'],
            'i' => ['minute'],
            's' => ['second']
        ];
        $found = false;
        $i = 0;
        foreach ($parts as $part => $texts) {
            if($found || $diff->$part) {
                $found = true;
                $arr[] = $this->formatDateDiffStr($diff->$part, $texts[0], $texts[1]);
                $i++;
            }
            if($i >= $max_parts) break;
        }
        return implode(' ', $arr);
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

<?php
namespace App;
class ElasticSearch extends \SCHLIX\cmsApplication_Basic {


    /**
     * Constructor
     */
    public function __construct() {
        require(__DIR__ . '/vendor/autoload.php');
        parent::__construct("Elastic Search");
        $this->has_versioning = false;
        $this->disable_frontend_runtime = false;
    }

    /**
     * Set config to default when $invalidCheck true.
     * @global \SCHLIX\cmsConfigRegistry $SystemConfig
     * @param string $name
     * @param mixed $default
     * @param string|function $invalidCheck
     */
    private function configDefault($name, $default, $invalidCheck) {
        global $SystemConfig;
        switch ($invalidCheck) {
            case 'presence':
                $result = !$this->_configs[$name];
                break;
            case 'numgt0':
                $result = (int) $this->_configs[$name] <= 0;
            case 'num':
                $result = $result || is_null($this->_configs[$name]) || !is_numeric($this->_configs[$name]);
                break;
            default:
                $result = $invalidCheck($this->_configs[$name]);
        }
        if ($result) {
            $this->_configs[$name] = $default;
            $SystemConfig->set($this->app_name, $name, $this->_configs[$name]);
        }
    }

    /**
     * Load and initialize configurations.
     * @global \SCHLIX\cmsConfigRegistry $SystemConfig
     */
    public function configs($use_cache = true) {
        global $SystemConfig;
        if(!$this->_configs || !$use_cache) {
            $SystemConfig->clearCache($this->app_name);
            $this->_configs = $SystemConfig->get($this->app_name);

            $this->configDefault('int_value_max_length', NULL, 'numgt0');
            $this->configDefault('int_elastic_cloud', 3, 'presence');
            $this->configDefault('str_index_name', 'schlixcms', 'presence');
            $this->configDefault('array_enabled_apps', ['html', 'blog'], function($v){
                return ___c($v) == 0 || !is_array($v);
            });
            $this->configDefault('int_per_page', 10, 'numgt0');
            $this->configDefault('int_fuzziness', 0, 'num');
            $this->configDefault('int_shards', 1, 'numgt0');
            $this->configDefault('int_replicas', 1, 'numgt0');
        }
        
        return $this->_configs;
    }

    /**
     * Return node hosts according to configuration
     * @return array
     */
    private function getHosts() {
        if (!isset($this->hosts)) {
            $hosts = trim($this->configs()['str_hosts']);
            $this->hosts = ($hosts) ? explode( "\n", $hosts) : [];
        }
        return $this->hosts;
    }

    /**
     * Return elasticsearch client instance
     * @return \GuzzleHttp\Ring\Client
     */
    private function client() {
        if (!$this->client) {
            switch ($this->configs()['int_elastic_cloud']) {
                case 1: // elasticcloud basic auth
                    if (!$this->configs()['str_ec_id'] || !$this->configs()['str_ec_user'] || !$this->configs()['str_ec_pass']) {
                        return NULL;
                    }
                    $this->client = \Elasticsearch\ClientBuilder::create()
                                        ->setElasticCloudId($this->configs()['str_ec_id'])
                                        ->setBasicAuthentication(
                                                $this->configs()['str_ec_user'],
                                                $this->configs()['str_ec_pass'])
                                        ->build();
                    break;
                case 2: // elasticcloud api key
                    if (!$this->configs()['str_ec_id'] || !$this->configs()['str_ec_api_id'] || !$this->configs()['str_ec_api_key']) {
                        return NULL;
                    }
                    $this->client = \Elasticsearch\ClientBuilder::create()
                                        ->setElasticCloudId($this->configs()['str_ec_id'])
                                        ->setApiKey(
                                                $this->configs()['str_ec_api_id'],
                                                $this->configs()['str_ec_api_key'])
                                        ->build();
                    break;
                default: // self / 3rd party hosted
                    if (empty($this->getHosts())) {
                        return NULL;
                    }
                    $this->client = \Elasticsearch\ClientBuilder::create()
                                        ->setHosts($this->getHosts())
                                        ->build();
                    break;
            }
        }
        return $this->client;
    }

    /**
     * Return array of supported applications
     * @return array
     */
    public function supportedApplications() {
        return ['html', 'blog', 'gallery'];
    }

    /**
     * Return true when application is configured and ready to use
     * @return boolean
     */
    public function isConfigured() {
        return !!$this->client();
    }

    public function isIndexExists() {
        if (!$this->isConfigured())
            return false;
        $index_name = $this->configs()['str_index_name'];
        return $this->client()->indices()->exists(['index' => $index_name]);
    }

    /**
     * Create a new index if it doesn't exists.
     * https://www.elastic.co/guide/en/elasticsearch/reference/7.4/indices-create-index.html
     * https://www.elastic.co/guide/en/elasticsearch/reference/7.4/indices-update-settings.html
     * @param bool $force_update
     * @return string
     */
    public function initIndex($force_update = false) {
        if (!$this->isConfigured())
            return NULL;
        $index_name = $this->configs()['str_index_name'];
        $params = [
            'index' => $index_name,
            'body' => [
                'settings' => [
                    'number_of_replicas' => $this->configs()['int_replicas']
                ]
            ]
        ];
        if (!$this->client()->indices()->exists(['index' => $index_name])) {
            $params['body']['settings']['number_of_shards'] = $this->configs()['int_shards'];
            $params['body']['mappings'] = [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => [
                    'id' => ['type' => 'long', 'index' => false],
                    'virtual_name' => ['type' => 'keyword'],
                    'title' => ['type' => 'text'],
                    'summary' => ['type' => 'text'],
                    'description_alternative_title' => ['type' => 'text', 'norms' => false],
                    'summary_secondary_headline' => ['type' => 'text', 'norms' => false],
                    'description' => ['type' => 'text'],
                    'description_secondary_headline' => ['type' => 'text', 'norms' => false],
                    'date_created' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                    'date_modified' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                    'meta_key' => ['type' => 'keyword'],
                    'meta_description' => ['type' => 'text'],
                    'tags' => ['type' => 'keyword'],
                    'url_media_file' => ['type' => 'text', 'index' => false, 'norms' => false],
                    'link' => ['type' => 'text', 'index' => false, 'norms' => false],
                    'app_name' => ['type' => 'keyword'],
                    'index_unix_timestamp' => ['type' => 'long']
                ]
            ];
            $response = $this->client()->indices()->create($params);
            if(!$response['acknowledged'])
                return NULL;
            if ($index_name != $response['index']) {
                $this->configs()['str_index_name'] = $index_name = $response['index'];
                $SystemConfig->set($this->app_name, 'str_index_name', $this->configs()['str_index_name']);
            }
        } elseif ($force_update) {
            $response = $this->client()->indices()->putSettings($params);
            if(!$response['acknowledged'])
                return NULL;
        }
        return $index_name;
    }

    /**
     * Delete index.
     * @global \SCHLIX\cmsLogger $SystemLog
     * @return boolean
     */
    public function deleteIndex() {
        global $SystemLog;
        
        if (!$this->isConfigured())
            return false;
        $index_name = $this->configs()['str_index_name'];
        $response = $this->client()->indices()->delete(['index' => $index_name, 'ignore_unavailable' => true]);
        if(!$response['acknowledged'])
            return false;

        $SystemLog->info('Delete index '.$index_name.' success.', $this->app_name);
        return true;
    }

    /**
     * Add records in bulk
     * https://www.elastic.co/guide/en/elasticsearch/reference/7.4/docs-bulk.html
     * @global \SCHLIX\cmsLogger $SystemLog
     * @param array $records
     * @return array
     */
    private function addBulkRecords($records) {
        global $SystemLog;

        try {
            $responses = $this->client()->bulk(['refresh' => 'wait_for', 'body' => $records]);
            if ($responses['errors']) {
                $SystemLog->error("Error indexing bulk records:\n" . var_dump($responses['items']), $this->app_name);
            }
            return $responses;
        } catch (\Exception $e) {
            $count = count($records);
            if ($count < 4) // single item
                $item_info = "item: " .
                    $records[1]['app_name'] .'-'. $records[1]['id'];
            elseif ($count < 2)
                $item_info = "NULL";
            else
                $item_info = "items: " .
                    $records[1]['app_name'] .'-'. $records[1]['id'] . ' - ' .
                    $records[$count - 1]['app_name'] .'-'. $records[$count - 1]['id'];
            $SystemLog->error("Error indexing bulk records $item_info.\n" . $e->getMessage(), $this->app_name);
        }
    }

    /**
     *
     * @global \SCHLIX\cmsLogger $SystemLog
     * @param string $index_name
     * @param int $timestamp
     * @return array ['success' => [bool], 'count' => [int]]
     */
    private function deleteOldRecords($index_name, $timestamp) {
        global $SystemLog;
        $params = [
            'index' => $index_name,
            'conflicts' => 'proceed',
            'ignore_unavailable' => 'true',
            'body' => [
                'query' => [
                    'range' => [
                        'index_unix_timestamp' => [
                            'lt' => ($timestamp - 1)
                        ]
                    ]
                ]
            ]
        ];
        try {
            $response = $this->client()->deleteByQuery($params);
        } catch (\Exception $e) {
            $SystemLog->error("Error deleting old records.\n" . $e->getMessage(), $this->app_name);
        }
        return ['success' => true, 'count' => $response['deleted']];
    }

    /**
     * Convert value according to type so it's acceptable for indexing.
     * @param mixed $value
     * @return mixed [converted $value]
     */
    private function getValueForIndexing($value) {
        switch (gettype($value)) {
            case 'string':
                // TODO: filter out macro keywords when possible
                $value = strip_tags($value);
                if((int) $this->configs()['int_value_max_length'] > 0) {
                    return mb_strimwidth($value, 0, $this->configs()['int_value_max_length'], '..', 'utf-8');
                }
            default:
                return $value;
        }
    }

    /**
     * Return array of indexed atttributes.
     * @return array
     */
    private function getIndexedAttributes() {
        return [
            'id', 'virtual_filename', 'title', 'summary', 'description_alternative_title',
            'summary_secondary_headline', 'description', 'description_secondary_headline',
            'date_created', 'date_modified', 'meta_key', 'meta_description', 'tags', 'url_media_file'
        ];
    }

    /**
     * Get indexed attributes for specified items.
     * @param array $item
     * @return array
     */
    private function getItemAttributes($item) {
        $attributes = [];
        foreach ($item as $key => $value) {
            if(in_array($key, $this->getIndexedAttributes())) {
                $attributes[$key] = $this->getValueForIndexing($value);
            }
        }
        return $attributes;
    }

    /**
     * Get all app items which available for indexing.
     * @param object $app
     * @param int $timestamp
     * @return array
     */
    private function getItemsForApp($app, $timestamp) {
        if (method_exists($app, 'getAllItems')) {
            $current_time = date('Y-m-d H:i:s', $timestamp);
            $current_time_str = sanitize_string($current_time);
            $invalid_date_str = sanitize_string(NULL_DATE);
            $sql_criteria_arr = [];
            if ($app->itemColumnExists('status')) {
                $sql_criteria_arr[] = "status > 0";
            }
            if ($app->itemColumnExists('date_available')) {
                $sql_criteria_arr[] = "(date_available IS NULL OR date_available < {$current_time_str})";
            }
            if ($app->itemColumnExists('date_expiry')) {
                $sql_criteria_arr[] = "((date_expiry IS NULL OR date_expiry = {$invalid_date_str}) OR date_expiry >= {$current_time_str})";
            }
            $sql_criteria = implode(' AND ', $sql_criteria_arr);
            return $app->getAllItems('*', $sql_criteria, 0, 0, 'id', 'ASC');
        }
        return [];
    }

    /**
     * Create / update index for specified app.
     * @param string $app_name
     * @param int $timestamp
     * @return array ['success' => [bool], 'count' => [int], 'message' => [string]]
     */
    private function updateIndexForApp($app_name, $timestamp) {
        $count = 0;

        $app_class_name = '\\App\\' . $app_name;
        $app = new $app_class_name;
        $items = $this->getItemsForApp($app, $timestamp);
        $records = [];
        foreach ($items as $item) {
            $attributes = array_merge([
                'link' => $app->createFriendlyURL("action=viewitem&id={$item['id']}"),
                'app_name' => $app_name,
                'index_unix_timestamp' => $timestamp
            ], $this->getItemAttributes($item));
            $records[] = [
                'index' => [
                    '_index' => $this->configs()['str_index_name'],
                    '_id' => $app_name.'-'.$attributes['id']
                ]
            ];
            $records[] = $attributes;
            $count += 1;
            if ($count % 1000 == 0) {
                $responses = $this->addBulkRecords($records);
                $records = [];
                if ($responses['errors']) {
                    return ['success' => false, 'count' => $count, 'message' => "Unable to update index, see log for more information."];
                }
                unset($responses);
            }
        }
        if (!empty($records)) {
            $responses = $this->addBulkRecords($records);
            if ($responses['errors']) {
                return ['success' => false, 'count' => $count, 'message' => "Unable to update index, see log for more information."];
            }
        }

        return ['success' => true, 'count' => $count, 'message' => 'Done.'];
    }

    /**
     * Create / update index from all enabled apps.
     * @global \SCHLIX\cmsLogger $SystemLog
     * @return array ['success' => [bool], 'message' => [string]]
     */
    public function updateIndex() {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => "App not yet configured."];
        }
        global $SystemLog;
        $apps = $this->supportedApplications();
        $index_name = $this->initIndex();

        $timestamp = time();
        $count = 0;
        foreach ($apps as $app_name) {
            if (in_array($app_name, $this->configs()['array_enabled_apps'])) {
                $result = $this->updateIndexForApp($app_name, $timestamp);
                if ($result['success']) {
                    $count += $result['count'];
                }
            }
        }
        $result = $this->deleteOldRecords($index_name, $timestamp);
        $deleted_count = $result['count'];

        $message = "$count records added/updated, $deleted_count records deleted.";
        $SystemLog->info($message, $this->app_name);
        return ['success' => true, 'message' => $message];
    }

    /**
     * Update index from cronscheduler
     */
    public function processRunUpdateIndex() {
        $Elasticsearch = new \App\ElasticSearch();
        echo "Updating index..";
        $result = $Elasticsearch->updateIndex();
        if ($result['success']){
            echo "Success: " . $result['message'];
        } else {
            echo "Failed: " . $result['message'];
        }
    }

    /**
     * Simple text search, with optional searched fields.
     * TODO: highlight https://www.elastic.co/guide/en/elasticsearch/reference/7.4/search-request-body.html#request-body-search-highlighting
     * @global \SCHLIX\cmsLogger $SystemLog
     * @param string $query
     * @param array $fields
     * @return array
     */
    private function textSearch($query, $fields = NULL) {
        global $SystemLog;
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => "App not configured."];
        }
        if (!$this->isIndexExists()) {
            return ['success' => false, 'message' => "Search data not available."];
        }
        $query = trim($query);
        if (!is_array($fields) || empty($fields)) {
            $fields = [
                'title^5',
                'virtual_filename^2', 'meta_key^2', 'tags^2', 'summary^2',
                'description_alternative_title', 'summary_secondary_headline',
                'description_secondary_headline', 'meta_description'
            ];
        }
        $per_page = $this->getNumberOfListingsPerPage();
        $page = max(fget_int('pg'), 1);
        $from = ($page - 1) * $per_page;
        $fuzziness = $this->configs()['int_fuzziness'];
        if (!is_numeric($fuzziness) || $fuzziness > 2 || $fuzziness < 0) {
            $fuzziness = 'AUTO:3,6';
        }
        $params = [
            'index' => $index_name,
            'from' => $from,
            'size' => $per_page,
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fuzziness' => $fuzziness,
                        'fields' => $fields
                    ]
                ]
            ]
        ];

        try {
            $results = $this->client()->search($params);
        } catch (\Exception $e) {
            $SystemLog->error("Error searching for ".___h($query).".\n".$e->getMessage(), $this->app_name);
            return ['success' => false, 'message' => 'Unexpeted error while searching.'];
        }
        $hits = $results['hits']['hits'];
        $total_page = (int) ceil($results['hits']['total']['value'] / $per_page);
        return [
            'success' => true,
            'hits' => $hits,
            'page' => $page,
            'per_page' => $per_page,
            'total_page' => $total_page];
    }

    public function getNumberOfListingsPerPage() {
        return $this->configs()['int_per_page'];
    }


    /**
     * View Main Page
     */
    public function viewMainPage() {
        $query = ___h(urldecode(\SCHLIX\cmsHttpInputFilter::string_noquotes_notags($_GET['elasticsearch'], 'query', 255)));
        $result = $this->textSearch($query);
        $this->loadTemplateFile('view.main', compact(array_keys(get_defined_vars())));
    }

    //_______________________________________________________________________________________________________________//
    public function Run($command) {
        switch ($command['action']) {
            default: return parent::Run($command);
        }
        return true;
    }
}

?>
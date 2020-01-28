<?php
/**
 * Elastic Search - Config
 * 
 * An alternative site search function for SCHLIX CMS using Elasticsearch. Combo extension consisting of App and Block.
 *
 * @copyright 2020 Roy H
 *
 * @license MIT
 *
 * @package elasticsearch
 * @version 1.0
 * @author  Roy H <ryhdr@maysora.com>
 * @link    https://github.com/ryhdr/elasticsearch-schlix
 */
if (!defined('SCHLIX_VERSION')) die('No Access');

$app_list = $this->app->supportedApplications();
?>
<!-- {top_menu} -->
<schlix-config:data-editor data-schlix-controller="SCHLIX.CMS.ElasticSearchAdminController" type="config">

        <x-ui:schlix-config-save-result />
        <x-ui:schlix-editor-form id="form-edit-config" method="post" data-config-action="save" action="<?= $this->createFriendlyAdminURL('action=saveconfig') ?>" autocomplete="off">

            <schlix-config:action-buttons />
            <x-ui:csrf />

            <x-ui:schlix-tab-container>
                <!-- tab -->
                <x-ui:schlix-tab id="tab_general" fonticon="far fa-file" label="<?= ___('General') ?>"> 
                    <!--content -->
                        
                    <schlix-config:app_alias />
                    <schlix-config:app_description />
                    <schlix-config:checkbox config-key='bool_disable_app' label='<?= ___('Disable application') ?>' />
                    <schlix-config:radiogroup config-key="int_elastic_cloud" label="<?= ('Host Type') ?>">
                        <schlix-config:option value="1"><?= ___('Elastic Cloud (Basic Auth)') ?></schlix-config:option>
                        <schlix-config:option value="2"><?= ___('Elastic Cloud (API)') ?></schlix-config:option>
                        <schlix-config:option value="3"><?= ___('Other / self hosted') ?></schlix-config:option>
                    </schlix-config:radiogroup>

                    <div class="es-fields es-fields-basic-auth es-fields-api">
                        <schlix-config:textbox config-key='str_ec_id' label='<?= ___('Elastic Cloud ID') ?>' />
                    </div>
                    <div class="es-fields es-fields-basic-auth">
                        <schlix-config:textbox config-key='str_ec_user' label='<?= ___('Elastic Cloud Username') ?>' />
                        <schlix-config:textbox type="password" config-key='str_ec_pass' label='<?= ___('Elastic Cloud Password') ?>' />
                    </div>
                    <div class="es-fields es-fields-api">
                        <schlix-config:textbox config-key='str_ec_api_id' label='<?= ___('Elastic Cloud API ID') ?>' />
                        <schlix-config:textbox config-key='str_ec_api_key' label='<?= ___('Elastic Cloud API Key') ?>' />
                    </div>
                    <div class="es-fields es-fields-other">
                        <schlix-config:textarea config-key='str_hosts' label='<?= ___('Elasticsearch Nodes') ?>' placeholder="http://localhost:9200" />
                        <span class="help-text"><?= ___('Multiple nodes supported, one node host per line.') ?></span><br />
                        <span class="help-text text-warning"><?= ___('Makes sure to secure your nodes access.') ?></span>
                    </div>
                </x-ui:schlix-tab>
                <x-ui:schlix-tab id="tab_advanced" fonticon="fa fa-cog" label="<?= ___('Advanced') ?>">
                    <fieldset>
                        <schlix-config:textbox config-key='int_per_page' label='<?= ___('Number of listing per page') ?>' type="number" />
                    </fieldset>
                    <fieldset>
                        <schlix-config:dropdownlist class="form-control" config-key="int_fuzziness" label="<?= 'Fuzziness' ?>" >
                            <schlix-config:option value="0"><?= ___('0 (Exact Match)') ?></schlix-config:option>
                            <schlix-config:option value="1"><?= ___('1') ?></schlix-config:option>
                            <schlix-config:option value="2"><?= ___('2') ?></schlix-config:option>
                            <schlix-config:option value="-1"><?= ___('Auto') ?></schlix-config:option>
                        </schlix-config:dropdownlist>
                        <span class="help-text"><?= ___('Allows inexact fuzzy matching.') ?></span>
                    </fieldset>
                    <schlix-config:checkboxgroup config-key="array_enabled_apps" label="<?=  ___('Index the following applications') ?>">
                        <?php foreach ($app_list as $enabled_app): ?>
                            <schlix-config:option value='<?= $enabled_app ?>'><?= $enabled_app ?></schlix-config:option>
                        <?php endforeach ?>
                    </schlix-config:checkboxgroup>
                    <fieldset>
                        <schlix-config:textbox config-key='str_index_name' label='<?= ___('Index Name') ?>' />
                        <span class="help-text text-warning"><?= ___('Please update index manually after changing this.') ?></span>
                    </fieldset>
                    <fieldset>
                        <schlix-config:textbox config-key='int_value_max_length' label='<?= ___('Text value max. length') ?>' type="number" />
                        <span class="help-text"><?= ___('Determine text maximum length to be indexed. Leaves blank to index everything.') ?></span>
                    </fieldset>
                </x-ui:schlix-tab>
                <!-- tab -->
                <?= \SCHLIX\cmsHooks::output('getApplicationAdminExtraEditConfigTab', $this) ?>
                <!-- end -->
            </x-ui:schlix-tab-container>
            
        </x-ui:schlix-editor-form>
</schlix-config:data-editor>     
<?php
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
                    <x-ui:container type="fluid">
                        <x-ui:row>
                            <schlix-config:app_alias />
                            <schlix-config:app_description />
                            <schlix-config:checkbox config-key='bool_disable_app' label='<?= ___('Disable application') ?>' />
                        </x-ui:row>
                        <schlix-config:checkboxgroup config-key="array_enabled_apps" label="<?=  ___('Index the following applications') ?>">
                            <?php foreach ($app_list as $enabled_app): ?>
                                <schlix-config:option value='<?= $enabled_app ?>'><?= $enabled_app ?></schlix-config:option>
                            <?php endforeach ?>
                        </schlix-config:checkboxgroup>
                        <x-ui:row>
                            <schlix-config:textbox config-key='int_per_page' label='<?= ___('Number of listing per page') ?>' type="number" />
                        </x-ui:row>
                    </x-ui:container>
                </x-ui:schlix-tab>
                <x-ui:schlix-tab id="tab_server" fonticon="fa fa-server" label="<?= ___('Host Setting') ?>">
                    <x-ui:container type="fluid">
                        <x-ui:row>
                            <x-ui:column sm="4">
                                <schlix-config:radiogroup config-key="int_elastic_cloud" label="<?= ('Host Type') ?>">
                                    <schlix-config:option value="1"><?= ___('Elastic Cloud (Basic Auth)') ?></schlix-config:option>
                                    <schlix-config:option value="2"><?= ___('Elastic Cloud (API)') ?></schlix-config:option>
                                    <schlix-config:option value="3"><?= ___('Other / self hosted') ?></schlix-config:option>
                                </schlix-config:radiogroup>
                            </x-ui:column>
                            <x-ui:column sm="8">
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
                                    <span class="help-text"><?= ___('Multiple nodes in cluster formation supported, one node host per line.') ?></span><br />
                                    <span class="help-text text-warning"><?= ___('Makes sure to secure your nodes access.') ?></span>
                                </div>
                            </x-ui:column>
                        </x-ui:row>
                        <hr />
                        <x-ui:row>
                            <?php $indexExists = $this->app->isIndexExists(); ?>
                            <x-ui:column sm="4">
                                <schlix-config:textbox config-key='int_shards' label='<?= ___('Number of shards') ?>' type="number"<?= ($indexExists) ? " readonly='readonly'" : "" ?> />
                                <?php if($indexExists): ?>
                                    <span class="help-text"><?= ___('Delete the index first before changing number of shards.') ?></span><br />
                                <?php endif; ?>
                            </x-ui:column>
                            <x-ui:column sm="4">
                                <schlix-config:textbox config-key='int_replicas' label='<?= ___('Number of replicas') ?>' type="number" />
                            </x-ui:column>
                            <x-ui:column sm="4">
                                <schlix-config:textbox config-key='str_index_name' label='<?= ___('Index Name') ?>' />
                            </x-ui:column>
                        </x-ui:row>
                    </x-ui:container>
                </x-ui:schlix-tab>
                <x-ui:schlix-tab id="tab_advanced" fonticon="fa fa-cog" label="<?= ___('Advanced') ?>">
                    <x-ui:container type="fluid">
                        <x-ui:row>
                            <x-ui:column sm="6">
                                <schlix-config:dropdownlist class="form-control" config-key="int_fuzziness" label="<?= 'Fuzziness' ?>" >
                                    <schlix-config:option value="0"><?= ___('0 (Exact Match)') ?></schlix-config:option>
                                    <schlix-config:option value="1"><?= ___('1') ?></schlix-config:option>
                                    <schlix-config:option value="2"><?= ___('2') ?></schlix-config:option>
                                    <schlix-config:option value="-1"><?= ___('Auto') ?></schlix-config:option>
                                </schlix-config:dropdownlist>
                                <span class="help-text"><?= ___('Allows inexact fuzzy matching.') ?></span>
                            </x-ui:column>
                            <x-ui:column sm="6">
                                <schlix-config:textbox config-key='int_value_max_length' label='<?= ___('Text value max. length') ?>' type="number" />
                                <span class="help-text"><?= ___('Determine text maximum length to be indexed. Leaves blank to index everything.') ?></span>
                            </x-ui:column>
                        </x-ui:row>
                    </x-ui:container>
                </x-ui:schlix-tab>
                <!-- tab -->
                <?= \SCHLIX\cmsHooks::output('getApplicationAdminExtraEditConfigTab', $this) ?>
                <!-- end -->
            </x-ui:schlix-tab-container>
            
        </x-ui:schlix-editor-form>
</schlix-config:data-editor>     
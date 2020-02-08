<?php
if (!defined('SCHLIX_VERSION')) die('No Access');

$indexExists = $this->app->isConfigured() && $this->app->isIndexExists();
$last_update = date_create()->setTimestamp($this->app->config('int_last_index_update'));
if($last_update) {
    $last_update_str = $this->getDateTimeDiffStr(date_create('now'), $last_update);
}
if(!$last_update_str) {
    $last_update_str = ___('Never');
} else {
    $last_update_str = $last_update_str . ' ' . ___('ago');
}
?>
<!-- {top_menu} -->
<x-ui:schlix-data-explorer-blank data-schlix-controller="SCHLIX.CMS.ElasticSearchAdminController" >

    <x-ui:schlix-explorer-toolbar>
        <x-ui:schlix-explorer-toolbar-menu data-position="left">                
            <!-- {config} -->
            <x-ui:schlix-explorer-menu-command data-schlix-command="config" data-schlix-app-action="editconfig" fonticon="fas fa-cog" label="<?= ___('Configuration') ?>" />
            <!-- {end config -->
            <?php if($this->app->isConfigured()): ?>
                <?php if($indexExists): ?>
                    <x-ui:schlix-explorer-menu-command data-schlix-command="updateindex" data-schlix-app-action="updateindex" fonticon="fas fa-exclamation-circle" label="<?= ___('Manually Update Index') ?>" />
                    <x-ui:schlix-explorer-menu-command data-schlix-command="deleteindex" data-schlix-app-action="deleteindex" fonticon="fas fa-exclamation-circle" label="<?= ___('Delete Index') ?>" />
                <?php else: ?>
                    <x-ui:schlix-explorer-menu-command data-schlix-command="initindex" data-schlix-app-action="initindex" fonticon="fa fa-search" label="<?= ___('Initialize Search Index') ?>" />
                <?php endif ?>
            <?php endif ?>
            <?= \SCHLIX\cmsHooks::output('getApplicationAdminExtraToolbarMenuItem', $this) ?>
        </x-ui:schlix-explorer-toolbar-menu>
        <!-- {help-about} -->
        <x-ui:schlix-explorer-toolbar-menu data-position="right">
            <x-ui:schlix-explorer-menu-folder fonticon="fa fa-question-circle" label="<?= ___('Help') ?>">
                <x-ui:schlix-explorer-menu-command data-schlix-command="help-about" data-schlix-app-action="help-about" fonticon="fas fas-cog" label="<?= ___('About') ?>" />
            </x-ui:schlix-explorer-menu-folder>
        </x-ui:schlix-explorer-toolbar-menu>
        <!-- {end help-about} -->

    </x-ui:schlix-explorer-toolbar>

    <div class="content">
        <?php if(!$this->app->isConfigured()): ?>
            <h3>Configuration:</h3>
            <ol class="configuration-steps">
                <li>
                    <p>
                        Install Elasticsearch, either using hosted service (Elastic Cloud / AWS / Alibaba Cloud / etc)
                        or installing Elasticsearch yourself.
                        <a href="https://www.elastic.co/guide/en/elasticsearch/reference/7.5/install-elasticsearch.html" target="_blank" rel="noreferer nofollow">Click here for install instruction</a>.
                    </p>
                </li>
                <li>
                    <p>
                        Get necessary host information, depending on where you install Elasticsearch:
                    </p>
                    <ol>
                        <li>
                            <dl>
                                <dt>Elastic Cloud</dt>
                                <dd>Get <code>Elastic Cloud ID</code> and either <code>Username & Password</code> or <code>API ID & API Key</code></dd>
                            </dl>
                        </li>
                        <li>
                            <dl>
                                <dt>3rd Party Host (AWS / Alibaba Cloud / etc)</dt>
                                <dd>Get the address & port along with username & password when required. Use encrypted protocol (https) when possible.</dd>
                                <dd>Combine with the following format: <code>http(s)://[username]:[password]@[address]:[port]</code></dd>
                                <dd>
                                    When Schlix CMS and Elasticsearch doesn't lives in the same network, it's necessary to
                                    use Public address instead of Internal address.<br />
                                    In this case for security reason the address should only be accessible by Schlix CMS.
                                </dd>
                                <dd>
                                    For AWS use port 80 (HTTP) or 443 (HTTPS) instead of default port 9200.
                                </dd>
                            </dl>
                        </li>
                        <li>
                            <dl>
                                <dt>Localhost</dt>
                                <dd>Similar with 3rd Party Host, but use <code>localhost</code> for the address.</dd>
                                <dd>For example: <code>https://myuser:mypassword@localhost:9200</code></dd>
                            </dl>
                        </li>
                    </ol>
                </li>
                <li>
                    <p>
                        Enter the host information in
                        <a href="<?= $this->createFriendlyAdminURL('action=editconfig'); ?>" data-schlix-command="config" data-schlix-app-action="editconfig" class="schlix-command-button"><i class="fas fa-cog " aria-hidden="true"></i> Configuration</a>.
                    </p>
                </li>
            </ol>
        <?php elseif (!$indexExists): ?>
            <x-ui:well>
                Elasticsearch configured but search index not yet created.
            </x-ui:well>
        <?php else: ?>
            <h4>Elasticsearch configured, start using search by placing Elasticsearch block on appropriate location.</h4>
            <p>
                <?= ___('Last index update at:') ?>
                <strong><?= $last_update_str ?></strong>
            </p>
            <p>
                This app will automatically update search index once per day.<br />
                You can change the frequency at <strong>Settings > System Scheduler > Elasticsearch update index</strong>.<br />
                Alternatively you can update the index manually using the button above.
            </p>
            <p>
                Log entry will be added each time search index updated.<br />
                You can view the log at <strong>Tools > Log Viewer</strong>.
            </p>
            <p>Learn more about Elasticsearch <a href="https://www.elastic.co/guide/en/elasticsearch/reference/7.5/elasticsearch-intro.html" target="_blank" rel="noreferer nofollow">here</a>.</p>
        <?php endif; ?>
    </div>
    <!-- End Data Viewer -->
</x-ui:schlix-data-explorer-blank>

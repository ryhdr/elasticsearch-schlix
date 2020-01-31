<?php
if (!defined('SCHLIX_VERSION')) die('No Access');
$value = ___h(urldecode(\SCHLIX\cmsHttpInputFilter::string_noquotes_notags($_GET['elasticsearch'], 'query', 255)));
?>
<div class="block-elasticsearch nice-search" id="<?= ___h($this->block_name) ?>">
    <form action="<?= $app_search->createFriendlyURL('') ?>" method="get">
        <x-ui:input-group>
            <x-ui:textbox placeholder="<?= ___('Search'); ?>" name="elasticsearch[query]" id="<?= $this->block_name.'_block_query' ?>" value="<?= $value ?>" />
            <x-ui:input-addon-button>
                <x-ui:button-info type="submit"><i class="fa fa-search"></i></x-ui:button-info>
            </x-ui:input-addon-button>
        </x-ui:input-group>
    </form>
</div>

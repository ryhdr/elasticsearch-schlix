<?php
/**
 * Elastic Search - Main page view (frontend)
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

$cached_app = [];
?>
<div class="app-<?= $this->app_name; ?>" id="app-<?= $this->app_name; ?>-search_result">

    <h1><?= ___h($this->getApplicationDescription()) ?></h1>

        <form action="<?= $this->createFriendlyURL('') ?>" method="get">
            <div class="nice-search">
                <x-ui:input-group>
                    <x-ui:textbox placeholder="<?= ___('Search'); ?>" name="elasticsearch[query]" id="<?= $this->app_name.'_query' ?>" value="<?= $query ?>" />
                    <x-ui:input-addon-button>
                        <x-ui:button-info type="submit"><i class="fa  fa-search"></i></x-ui:button-info>
                    </x-ui:input-addon-button>
                </x-ui:input-group>
            </div>

        </form>
    <br />
    <?php if (!$result['success']) : ?>
        <p><?= ___($result['message']) ?></p>
    <?php elseif (empty($result['hits'])): ?>
        <p><?= sprintf(___('No search result for: %s'), $query) ?></p>
    <?php else: ?>
        <?php foreach ($result['hits'] as $hit): ?>
            <?php
                $item = $hit['_source'];
            ?>
            <h3 class="search_result_title">
                <a href="<?= $item['link'] ?>"><?= ___h($item['title']); ?></a>
            </h3>
            <?php if ($item['url_media_file'] && $item['app_name']): ?>
                <?php
                    $item_app_name = '\\App\\'.$item['app_name'];
                    if ($cached_app[$item['app_name']])
                    {
                        $item_app = $cached_app[$item['app_name']];
                    } else
                    {
                        $item_app = new $item_app_name();
                        $cached_app[$item['app_name']] = $item_app;
                    }
                    if (method_exists($item_app, 'getGalleryImage')) {
                        $img_src = $item_app->getGalleryImage('image_small', $item['url_media_file'], '');
                    } elseif (method_exists($item_app, 'getBlogImage')) {
                        $img_src = $item_app->getBlogImage('image_small', $item['url_media_file']);
                    }

                    if ($img_src):
                ?>
                    <div class="search_result_image">
                        <img src="<?= $img_src ?>" alt="<?= $item['title'] ?>" />
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="search_result_text">
                <p class="search_result_text">
                    <?= mb_strimwidth($item['summary'], 0, 150, '..', 'UTF-8'); ?>
                    <br/><a class="search_result_link" href="<?= $item['link'] ?>">[<?= ___('Read More') ?>...]</a>
                </p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <br />
    <?php $pagination_str = $this->displayItemPagination($result['page'], $result['total_page'], '?elasticsearch[query]='.$query); ?>
    <?php if ($pagination_str): ?>
        <div class="pagination"><?= $pagination_str; ?></div>
    <?php endif ?>
</div>
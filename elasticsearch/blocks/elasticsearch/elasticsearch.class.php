<?php
namespace Block;
/**
 * Elastic Search - Main Class
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
class ElasticSearch extends \SCHLIX\cmsBlock
{
	public function Run()
	{
                $app_search = new \App\ElasticSearch();
                $this->loadTemplateFile('view.block',compact(array_keys(get_defined_vars())));
  	}
}

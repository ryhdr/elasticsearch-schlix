<?php
namespace Block;
class ElasticSearch extends \SCHLIX\cmsBlock
{
	public function Run()
	{
                $app_search = new \App\ElasticSearch();
                $this->loadTemplateFile('view.block',compact(array_keys(get_defined_vars())));
  	}
}

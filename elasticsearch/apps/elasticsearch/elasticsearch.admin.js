/**
 * Elastic Search - Javascript admin controller class
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
SCHLIX.CMS.ElasticSearchAdminController = class extends SCHLIX.CMS.BaseController  {  
    /**
     * Constructor
     */
    constructor ()
    {
        super("elasticsearch");
    };
    
    onElasticCloudTypeClick(e)
    {
        const input    = e.target,
              classes  = ['es-fields-basic-auth', 'es-fields-api', 'es-fields-other'],
              selected = classes[input.value - 1];

        const toHide = SCHLIX.Dom.get('{.es-fields:not(.'+selected+')}');
        const toShow = SCHLIX.Dom.get('{.'+selected+'}');
        toHide.forEach(function(el) {
            el.style.display = 'none';
        });
        toShow.forEach(function(el) {
            el.style.display = '';
        });
    }

    onDOMReady()
    {
        SCHLIX.Event.on('{#int_elastic_cloud input}','click',this.onElasticCloudTypeClick, this, true);
        const selectedInput = SCHLIX.Dom.get('{#int_elastic_cloud input:checked}');
        if(selectedInput)
            selectedInput[0].click();
    };
 
    runCommand (command, evt)
    {
        switch (command)
        {
            case 'config':
                this.redirectToCMSCommand("editconfig");
                return true;
                break;
            case 'updateindex':
                if (confirm('Manually update index?\n( To avoid problem please don\'t update too frequently )' ))
                    this.redirectToCMSCommand("updateindex");
                return true;
                break;
            default:
                return super.runCommand(command, evt);
                break;
        }
    }
};



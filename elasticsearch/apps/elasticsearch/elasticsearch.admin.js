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
            case 'initindex':
                this.redirectToCMSCommand("updateindex");
                return true;
                break;
            case 'updateindex':
                if (confirm('Manually update index?\n( To avoid problem please don\'t update too frequently )' ))
                    this.redirectToCMSCommand("updateindex");
                return true;
                break;
            case 'deleteindex':
                if (confirm('Delete index? Search will not working until you created the index again.' ))
                    this.redirectToCMSCommand("deleteindex");
                return true;
                break;
            default:
                return super.runCommand(command, evt);
                break;
        }
    }
};



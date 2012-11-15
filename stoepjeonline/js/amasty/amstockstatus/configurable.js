StockStatus = Class.create();

StockStatus.prototype = 
{
    options : null,
    
    initialize : function(options)
    {
        this.options = options;
    },
    
    onConfigure : function(key, settings)
    {
        this._removeStockStatus();
        if ('undefined' != typeof(this.options[key]))
        {
            if (this.options[key]['custom_status'])
            {
                $$('.product-options-bottom .price-box').each(function(pricebox) {
                    span = document.createElement('span');
                    span.id = 'amstockstatus-status';
                    span.style.paddingLeft = '10px';
                    span.innerHTML = this.options[key]['custom_status'];
                    pricebox.appendChild(span);
                }.bind(this));
            }
            if (0 == this.options[key]['is_in_stock'])
            {
                $$('.add-to-cart').each(function(elem) {
                    elem.hide();
                });
            } else 
            {
                $$('.add-to-cart').each(function(elem) {
                    elem.show();
                });
            }
        } else 
        {
            $$('.add-to-cart').each(function(elem) {
                elem.show();
            });
        }

        keyParts = explode(',', key);
        if ("" == keyParts[keyParts.length-1]) // this means we have something like "28," - the last element is empty - config is not finished
        {
            needConcat  = true;
            selectIndex = keyParts.length-1;
        } else 
        {
            needConcat  = false;
            selectIndex = keyParts.length;
        }
        // now searching if we have any option to which we should add custom status
        for (i = 0; i < settings.length; i++)
        {
            if (i == keyParts.length-1)
            {
                for (x = 0; x < settings[i].options.length; x++)
                {
                    if (needConcat)
                    {
                        keyCheck = key + settings[i].options[x].value;
                    } else 
                    {
                        keyCheckParts = explode(',', key);
                        keyCheckParts[keyCheckParts.length-1] = settings[i].options[x].value;
                        keyCheck = implode(',', keyCheckParts);
                    }
                    if ('undefined' != typeof(this.options[keyCheck]))
                    {
//                        if ( (0 == this.options[keyCheck]['is_in_stock'] || 1 == this.options[keyCheck]['is_qnt_0']) && this.options[keyCheck]['custom_status'])
                        if (this.options[keyCheck]['custom_status'])
                        {
                            if (!strpos(settings[i].options[x].text, this.options[keyCheck]['custom_status']))
                            {
                                settings[i].options[x].text = settings[i].options[x].text + ' (' + this.options[keyCheck]['custom_status'] + ')';
                            }
                        }
                    }
                }
            }
        }
    },
    
    _removeStockStatus : function()
    {
        if ($('amstockstatus-status'))
        {
            $('amstockstatus-status').remove();
        }
    }
};

Product.Config.prototype.configure = function(event){
    var element = Event.element(event);
    this.configureElement(element);
    var key = '';
    this.settings.each(function(element){
        key += element.value + ',';
    });
    key = key.substr(0, key.length - 1);
    stStatus.onConfigure(key, this.settings);
};

Product.Config.prototype.loadStatus = function()
{
    var key = '';
    stStatus.onConfigure(key, this.settings);
}

function explode (delimiter, string, limit) 
{
    var emptyArray = { 0: '' };
    
    // third argument is not required
    if ( arguments.length < 2 ||
        typeof arguments[0] == 'undefined' ||
        typeof arguments[1] == 'undefined' )
    {
        return null;
    }
 
    if ( delimiter === '' ||
        delimiter === false ||
        delimiter === null )
    {
        return false;
    }
 
    if ( typeof delimiter == 'function' ||
        typeof delimiter == 'object' ||
        typeof string == 'function' ||
        typeof string == 'object' )
    {
        return emptyArray;
    }
 
    if ( delimiter === true ) {
        delimiter = '1';
    }
    
    if (!limit) {
        return string.toString().split(delimiter.toString());
    } else {
        // support for limit argument
        var splitted = string.toString().split(delimiter.toString());
        var partA = splitted.splice(0, limit - 1);
        var partB = splitted.join(delimiter.toString());
        partA.push(partB);
        return partA;
    }
}

function implode (glue, pieces) {
    var i = '', retVal='', tGlue='';
    if (arguments.length === 1) {
        pieces = glue;
        glue = '';
    }
    if (typeof(pieces) === 'object') {
        if (pieces instanceof Array) {
            return pieces.join(glue);
        }
        else {
            for (i in pieces) {
                retVal += tGlue + pieces[i];
                tGlue = glue;
            }
            return retVal;
        }
    }
    else {
        return pieces;
    }
}

function strpos (haystack, needle, offset) 
{
    var i = (haystack+'').indexOf(needle, (offset ? offset : 0));
    return i === -1 ? false : i;
}
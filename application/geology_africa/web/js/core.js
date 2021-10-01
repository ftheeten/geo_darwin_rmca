		var global_modal_index_value="";
		var global_modal_index_text="";
		
		var select2_generic=function(url, key, val, minlen)
		{
			return select2_generic_full(url, key, val, minlen, true);
		}
		
		var select2_generic_full=function(url, key, val, minlen, include_pattern)
		{
			var global_pattern="";
			return {
                        minimumInputLength: minlen,
                        ajax:
                        {
                            url:url,
                            dataType: 'json',
                            data: function(param)
                            {
                                global_pattern= param.term;
                                return {
									
                                    code: param.term
                                };
                            }
                            ,
                            processResults: function(result)
                            {
                                returned=Array();
                          
								if(include_pattern)
								{
									returned.push({"id": global_pattern , "text":global_pattern });
								}
                                if(result.length>0)
                                {
									
                                   
                                    for(var i=0; i<result.length; i++ )
                                    {
                                        returned.push({"id": result[i][key] , "text":result[i][val] });
                                    }
                                    
                                    
                                }
                                return {"results":returned};
                            }
                        }
                    
                    }
		}
    //ftheeten 2018 09 18
    function onElementInserted(containerSelector, elementSelector, callback) {

            var onMutationsObserved = function(mutations) {
				
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        var elements = $(mutation.addedNodes).find(elementSelector);
                        for (var i = 0, len = elements.length; i < len; i++) {
                            callback(elements[i]);
                        }
                    }
                });
            };

            var target = $(containerSelector)[0];
            var config = { childList: true, subtree: true };
            var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
            var observer = new MutationObserver(onMutationsObserved);    
            observer.observe(target, config);

        }
		
		
		    //ftheeten 2018 09 18
    function onElementModified(containerSelector, elementSelector, callback) {

            var onMutationsObserved = function(mutations) {				
                mutations.forEach(function(mutation) {                  
                      
							 var elements = $(elementSelector);
								for (var i = 0, len = elements.length; i < len; i++) 
								{									
									callback(elements[i]);
								}
                });
            };

            var target = $(containerSelector)[0];
            var config = { childList: true, subtree: true };
            var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
            var observer = new MutationObserver(onMutationsObserved);    
            observer.observe(target, config);

        }
		
	var get_param=function( name, url ) 
	{
        if (!url) url = location.href;
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( url );
        return results == null ? "" : results[1];
    }
	



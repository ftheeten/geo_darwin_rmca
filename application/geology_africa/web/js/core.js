		var global_modal_index_value="";
		var global_modal_index_text="";
		var current_tab="main-tab";
		var search=null;
		
		var select2_generic=function(url, key, val, minlen)
		{
			return select2_generic_full(url, key, val, minlen, true);
		}
		
		var select2_pattern_only=function(minlen)
		{			
			var tmp_data={
				tags: true,				
				minimumInputLength: minlen
			};
			return tmp_data
			
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
	
	 var get_all_params=function(url)
    {       
		console.log(url);
        var regexS = /(?<=&|\?)([^=]*=[^&#]*)/;
        var regex = new RegExp( regexS,'g' );
        var results = url.match(regex);
        if(results==null)
        {
            return {};
        }
        else
        {
            returned={};
            for(i=0;i<results.length;i++)
            {
                var tmp=results[i];                
                var regexS2="([^=]+)=([^=]+)";
                var regex2 = new RegExp( regexS2 );
                var results2 = regex2.exec(tmp );                
                returned[results2[1]]=results2[2];
            }
            return returned;
        }   
    }
	
	var replace_param=function(url, param, value)
	{
		var get_params=get_all_params(url);
		var base_url=url.split("?");
		get_params[param]=value;
		var new_params=Array();
		for(key in get_params)
		{
			new_params.push(key+"="+get_params[key]);
		}
		return base_url[0]+"?"+new_params.join("&");
	}
	
	
	var init_tab_url=function(p_class, current_tab)
	{
		console.log(p_class);
		var tmp_url=$(p_class).attr("href");
		console.log(tmp_url);
		if(typeof  tmp_url!=="undefined")
		{
			
			tmp_url=replace_param(tmp_url,"current_tab",current_tab );
			$(p_class).attr("href",tmp_url );
		}
	}
	
	var handle_ajax_template=function(counter, prefix, url )
	{
		counter++;
		
		$("#"+prefix+"_list").append('<li><div class="widget_subform_'+ prefix +'_'+ counter.toString() +'"></div></li>');
		jQuery.get(url,
					{
						index:counter,
						ctrl_prefix: prefix
					},
					function(result)
					{						
						$('.widget_subform_'+ prefix+'_'+ counter.toString() ).html(result);
					}
				);
		return counter;
	}
	
	var handle_remove_ajax_template=function(counter, prefix)
	{
		if(counter>0)
		{
			counter--;
			$('#'+prefix+'_list li:last-child').remove();
		}
		return counter;
			
	}
	
	$(document).ready(
		function()
		{
			$(".mode_shift").click(
				function()
				{					
					init_tab_url(".mode_shift", current_tab);
				}
			);
			
			$(".nav-link").click(
				function()
				{
					current_tab=$(this).attr("id");
					init_tab_url(".mode_shift", current_tab);
				}
			);
			
			$("#choose_log").change(
				function()
				{					
					var tmp=$(this).val();
					console.log(tmp);
					if(tmp=="")
					{
						$(".typelog").show();
					}
					else
					{
						$(".typelog").hide();
						$("."+tmp).show();
					}
				}
			);
			
			$(".spinner-border").hide();
			
			
		}
	);
	



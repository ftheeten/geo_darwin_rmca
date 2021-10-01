	var current_class;
	
	var click_callback_fancytree=function(event, data) {
              var node = data.node;
			   console.log("Selected callback test");
                var val=node["key"];
				console.log(val);
			    
				console.log("current class");
				console.log(current_class);
				$(".widget_keywords_"+current_class).val(val);
				$(".browsekeywords_"+current_class).click();
				
				 				
    }
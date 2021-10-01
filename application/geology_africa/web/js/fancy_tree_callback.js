	var click_callback_fancytree=function(event, data) {
                var node = data.node;
                var val=node["key"];
			    console.log("Selected callback");
				console.log(val);
				$("#modalbrowse").modal('hide');
				$(".keyw_parent").val(val);
				$("#modaladd").modal('show');                
    }
var map;
var mousePositionControl;
var scaleLineControl;
var source_draw = new ol.source.Vector({wrapX: false});
var draw;
var iLayer=0;
var vectorLoaded =false;
var type_draw="";
var last_layer=null;

var layerLoaded=false;
var overlay;
var container = document.getElementById('popup');
var content = document.getElementById('popup-content');
var closer = document.getElementById('popup-closer');


//pop up helper
function isCluster(feature) {
      if (!feature || !feature.get('features')) { 
            return false; 
      }
      return feature.get('features').length >= 1;
    }
  
  var init_overlay=function()
 {
	 
	overlay = new ol.Overlay(/** @type {olx.OverlayOptions} */ ({
               element: container,
                    autoPan: true,
                    autoPanAnimation: {
                      duration: 250
                    }
                  }));
                  

     closer.onclick = function() {
                    overlay.setPosition(undefined);
                    closer.blur();
                    return false;
                  };
     map.addOverlay(overlay);
 } 
  
var ol_ext_inherits = function(child,parent) {
		child.prototype = Object.create(parent.prototype);
		child.prototype.constructor = child;
};

   	var styleWKT= new ol.style.Style({
			  fill: new ol.style.Fill({
				color: 'rgba(255, 255, 255, 0.2)'
			  }),
			  stroke: new ol.style.Stroke({
				color: '#ffcc33',
				width: 4
			  }),
			  image: new ol.style.Circle({
				radius: 7,
				fill: new ol.style.Fill({
				  color: '#ffcc33'
				})
			  })
			});
            
       function removeDarwinLayer(p_max){		
		if(vectorLoaded){
			map.getLayers().forEach(function(layer) {	
				if (typeof layer !== 'undefined') {			
					if(layer.get("name")!="background"&&parseInt(layer.get("name"))==p_max ){				
						map.removeLayer(layer);
					}
				}
			});
		}		
	}
            
   function addDarwinLayer(feature,origininput)
    {
            var tmp_geom =new ol.geom.Polygon(feature.getGeometry().getCoordinates());
            var  generic_feature = new ol.Feature({geometry: tmp_geom});
              
            var tmpSource=new ol.source.Vector();
            tmpSource.addFeature(generic_feature);
            iLayer++;
            var vectorlayer_local = new ol.layer.Vector({
                        name: iLayer,
                        source: tmpSource,
                        style: styleWKT	});
                        
            
            map.addLayer(vectorlayer_local);
            var format = new ol.format.WKT();
			tmp_geom4326= tmp_geom.clone();
			tmp_geom4326.transform("EPSG:3857", "EPSG:4326");
			wktfeaturegeom = format.writeGeometry(tmp_geom4326);
			$('.wkt_search').val(wktfeaturegeom);
            vectorLoaded=true;		
    }
        

 var init_map=function(target)
 {
	 
	var layers = [];
	var styles=["esri_satelite"];
	var esri= new ol.layer.Tile({
		  source: new ol.source.XYZ({
			url:
			  'http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
	
			  maxZoom:12
		  }),
		});
	layers.push(esri);
	var OSM_layer = new ol.layer.Tile({
		    visible: false,
            source: new ol.source.OSM()
          });
	mousePositionControl= new ol.control.MousePosition({
			 coordinateFormat: ol.coordinate.createStringXY(4),
			projection:'EPSPG:4326',
			className: "custom-mouse-position",
			target: document.getElementById("mouse-position"),
			undefinedHTML: "&nbsp;"
		});
		
	     //button draw bbox
      DrawBoxControl = function(opt_options) {
                
                
                var options = opt_options || {};
                var element = document.createElement('div');
                element.className = 'draw-box ol-unselectable ol-control';
                element.innerHTML='&#9633;';   
                $(element).click(
                    function()
                    {
                        type_draw="box";
                        removeDarwinLayer(iLayer);
                        map.removeInteraction(draw);
                        draw = new ol.interaction.Draw({
                        source: source_draw,
                        type: 'Circle',
                        geometryFunction: ol.interaction.Draw.createBox(),
                        //finishCondition: ol.events.condition.doubleClick 
                        });
                        draw.on('drawend', function (event) {                        
                            addDarwinLayer(event.feature,"from drawing");
                            map.removeInteraction(draw);
                        });
                        map.addInteraction(draw);
                    }
                );
                 ol.control.Control.call(this, {
                  element: element,
                  target: options.target
                });
      };
     ol_ext_inherits(DrawBoxControl, ol.control.Control);
     
     //button draw Polygons
      DrawPolygonControl = function(opt_options) {
               
                var options = opt_options || {};
                var element = document.createElement('div');
                element.className = 'draw-polygon ol-unselectable ol-control';
                element.innerHTML='&#11040;';   
                $(element).click(
                    function()
                    {
                         type_draw="polygon";
                        removeDarwinLayer(iLayer);
                        map.removeInteraction(draw);
                        draw = new ol.interaction.Draw({
                        source: source_draw,
                        type: 'Polygon'
                        });
                        draw.on('drawend', function (event) {
                            addDarwinLayer(event.feature,"from drawing");
                            map.removeInteraction(draw);
                        });
                        map.addInteraction(draw);
                    }
                );
                 ol.control.Control.call(this, {
                  element: element,
                  target: options.target
                });
      };
     ol_ext_inherits(DrawPolygonControl, ol.control.Control);	
	 
	scaleLineControl = new ol.control.ScaleLine();
	var fullScreenControl = new ol.control.FullScreen();
	 
	      
	 
	map = new ol.Map({
				target: target,
				layers: layers,    
				 
				view: new ol.View({                    
				  center: ol.proj.fromLonLat([0,0]),
				  zoom: 2
				}),
				controls: ol.control.defaults({
						attributionOptions: ({collapsible: false})
				}).extend([mousePositionControl, scaleLineControl,  fullScreenControl, new DrawBoxControl(), new DrawPolygonControl()])
		});
	map.addLayer(OSM_layer);
	init_overlay();
    mousePositionControl.setProjection("EPSG:4326");
	
		var select = document.getElementById('layer-select');
			function onChange() {
			
			if(select.value=="esri_satelite")
			{
				OSM_layer.setVisible(false);
				var style = select.value;
				for (var i = 0, ii = layers.length; i < ii; ++i) {
				  layers[i].setVisible(styles[i] === style);
				}
			}
			else
			{
				//console.log("trye");
				for (var i = 0, ii = layers.length; i < ii; ++i) {
				  layers[i].setVisible(false);
				}
				OSM_layer.setVisible(true);
			}
		}
		select.addEventListener('change', onChange);
		onChange();
		
			//display data popup
	map.on('click', function(evt) {
				var coordinate = evt.coordinate;
				var hdms = ol.coordinate.toStringHDMS(coordinate);
				//console.log("click");
				var feature = map.forEachFeatureAtPixel(evt.pixel, 
							  function(feature) { return feature; });
				if (isCluster(feature)) {
				//var popup = new ol.Overlay.Popup();
				//map.addOverlay(popup);
			 
			   var html="";
				// is a cluster, so loop through all the underlying featuresZ
				var features_tmp = feature.get('features');
				var key_sort=[]
				var feature_bag={};
				for(var i = 0; i < features_tmp.length; i++) {
					key_sort[key_sort.length] = features_tmp[i].get('dw_pk');
					feature_bag[features_tmp[i].get('dw_pk')]=features_tmp[i];
				}
				key_sort.sort();
				for(var i = 0; i < key_sort.length; i++) {
				  // here you'll have access to your normal attributes:
				  ////console.log(features[i].get('name'));
			     var tmp_feat=feature_bag[key_sort[i]];
				 var type=tmp_feat.get('dw_type')||'';
				 if(type=="sample")
				 {
					 var link_url="../edit/sample/";
				 }
				 else if(type=="georef")
				 {
					 var link_url="../edit/point/";
				 }
			     else
				 {
					  var link_url="../edit/doc/";
				 }
				  html+="<a target='_blank' href='"+ link_url+ tmp_feat.get('dw_pk') +"' ><u>"+ (tmp_feat.get('dw_type')||'') +" "+ (tmp_feat.get('dw_idcollection')||'') + " "+ (tmp_feat.get('dw_idobject')||'') + "</u></a>"+"<br/>";
				  
				}
			   
				 content.innerHTML = '<p>' + html +'</p>';
					overlay.setPosition(coordinate);
			  } 
			});	


 }
 
 $(document).ready(
	function()
	{
		//console.log("LOADMAP");
		//init_map("searchmap");
	}
);

 var getFeaturesRow=function(p_geoJSON)
 {    
    console.log("map");
    
    var tmpFeatures=(new ol.format.GeoJSON()).readFeatures(jQuery.parseJSON(p_geoJSON), {
                dataProjection: 'EPSG:4326',
                featureProjection: 'EPSG:3857'
            });
			
    var vectorSource = new ol.source.Vector({features: tmpFeatures});
   
    if(tmpFeatures.length>0)
    {	
      
        var clusterSource = new ol.source.Cluster({
          distance: 40,
          source: vectorSource
        });
        
      var styleCache = {};
      var keysForClick=[];
      clusters = new ol.layer.Vector({
        source: clusterSource,
        style: function(feature) {
        
        jQuery(feature.get('features')).each(
                    function(idx, item)
                    {
                        keysForClick[item.get('id')||'']=item.get('code')||'';
                     
                    }
                );
               
          var size = feature.get('features').length;
          var style = styleCache[size];
          if (!style) {
            style = new ol.style.Style({
              image: new ol.style.Circle({
                radius: 10,
                stroke: new ol.style.Stroke({
                  color: '#fff'
                }),
                fill: new ol.style.Fill({
                  color: '#3399CC'
                })
              }),
              text: new ol.style.Text({
                text: size.toString(),
                fill: new ol.style.Fill({
                  color: '#fff'
                })
              })
            });
            styleCache[size] = style;
          }
          return style;
        },
        keysForClick: keysForClick
      });      
       
        
       
       if(!layerLoaded)
       {
         map.addLayer(clusters);
       }
       else
       {
         map.removeLayer(last_layer);
         map.addLayer(clusters);
       }
       last_layer=clusters;
        
        layerLoaded=true;
        
        var extent = vectorSource.getExtent();
        //alert(vectorSource.getExtent());
        map.getView().fit(extent, map.getSize(),{maxZoom:10});
       if(map.getView().getZoom()>10)
       {
         map.getView().setZoom(10);
       }
	   if(map.getView().getZoom()>1)
	   {
		   map.getView().setZoom(map.getView().getZoom()-1);
	   }
      //alert("end selection");
	  
    }
	else
	{
		
	}
};
  
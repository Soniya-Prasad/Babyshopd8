//<![CDATA[
var map;
var markers = [];
var gicons = [];
var locationSelect;
var sidebar;
var totalMarkerCount;

load();
searchLocations();

function load() {
    resetMap();
    sidebar = document.getElementById('location-list');
    sidebar.innerHTML = '';
}

   function resetMap() {

		latitudevalue = parseFloat(document.getElementById('latitude').value);
		longitudevalue = parseFloat(document.getElementById('longitude').value);
		zoomlevellvalue = parseInt(document.getElementById('zoomlevel').value);

	    map = new google.maps.Map(document.getElementById("map-holder"), {
			center: new google.maps.LatLng(latitudevalue, longitudevalue),
	        zoom: zoomlevellvalue,
	        mapTypeId:'roadmap',
			scrollwheel:true,
	        mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
		});
   }

   function searchLocations() {

	 sidebar.innerHTML = '';
	 var address = document.getElementById("store-locator-search-input").value;
     address = address.trim();
     if(address == "")
	 {
		//sidebar.innerHTML = '<div class="scroll jcf-scrollable"><strong>No stores found!</strong></div>';
		//resetMap();
		address = document.getElementById("location").value;
	 }

	 var geocoder = new google.maps.Geocoder();
     geocoder.geocode({address: address}, function(results, status) {
       if (status == google.maps.GeocoderStatus.OK) {
			searchLocationsNear(results[0].geometry.location);
			return false;
       } 
       else {
			sidebar.innerHTML = '<div class="scroll jcf-scrollable"><strong>No stores found!</strong></div>';
			resetMap();
			return false;
       }
     });
	 return false;
   }

   function clearLocations() {
   	 sidebar.innerHTML = '';
	 google.maps.event.trigger(map, "click");
     for (var i = 0; i < markers.length; i++) {
       markers[i].setMap(null);
     }
     markers.length = 0;
   }

   function searchLocationsNear(center) {
	clearLocations(); 
	base_url = document.getElementById('site_url').value;
	var language = document.getElementById('language').value;
	var searchloc = document.getElementById('store-locator-search-input').value;
	searchloc = searchloc.trim();
	var searchUrl = '';
	if(searchloc.length == 0) {
		var location = document.getElementById("location").value;
		searchUrl = base_url + '/' + language + '/store_callback/'+ location;
	}
	else {
		searchUrl = base_url + '/' + language + '/store_callback/'+ searchloc;
	}
	

    downloadUrl(searchUrl, function(data) {
    	
	   var xml = parseXml(data);
       var markerNodes = xml.documentElement.getElementsByTagName("marker");
	   //var pager = xml.documentElement.getElementsByTagName("pager");
	   var haveresult = xml.documentElement.getElementsByTagName("haveresult");
       var bounds = new google.maps.LatLngBounds();
	   if(haveresult[0].getAttribute("status") == 'yes')
	   {
	 
		   sidebar.innerHTML = '';
		   for (var i = 0; i < markerNodes.length; i++) {
			
			var name = "";
			var address = "";
			var latlng = "";
			var timings = "";
			var size = "";
			var phone = "";
			var getaddress = "";
			var sidebarEntry = "";
			name = markerNodes[i].getAttribute("name");
			address = markerNodes[i].getAttribute("address");
			latlng = new google.maps.LatLng(
				parseFloat(markerNodes[i].getAttribute("lat")),
				parseFloat(markerNodes[i].getAttribute("lng")));

			timings = markerNodes[i].getAttribute("timings");
			size = markerNodes[i].getAttribute("size");
			phone = markerNodes[i].getAttribute("phone");
			getaddress = markerNodes[i].getAttribute("getaddress");
			//sidebarEntry = createSidebarEntry(latlng,i, name, address);
			var html = '<li class="search-result-item" id="'+i+'"><h2>' + name + '</h2><address>' + address+'</address></li>';
			//sidebar.appendChild(html);
			jQuery('#location-list').append(html);
			createMarker(latlng, name, address,timings,size,phone,i,getaddress);
			bounds.extend(latlng);

			var viewlist = document.getElementById(i);
			viewlist.onclick = function(){
							selectMarker(this.id);
							jQuery("li.search-result-item").removeClass("active");
							jQuery(this).addClass("active");
			 };
		   }

		  

		   totalMarkerCount = i;
		   //var lastpage = pager[0].getAttribute("lastpage");
		   //var cur = pager[0].getAttribute("curpage");
		   //var pagerdiv = document.createElement('div');
		   //pagerdiv.id = "storelocator-pagination";
		   
		   //pagerdiv.setAttribute('class','search-result-pagination');
		  //  var html = "";
		  //  if(lastpage > 1 )
		  //  {
			 //   for(var inc=1; inc <= lastpage;inc++)
			 //   {
				//    var classchg = 'pagination-link';
				//   if(cur == inc)
				//   {
				// 	  classchg = 'pagination-link-current'; 
				//   }
				//  html+='<a class="'+classchg+'" style="cursor:pointer" onClick="javascript:searchLocationsNear('+center+','+inc+')">'+inc+'</a>&nbsp;';
			 //   }
			 //   //pagerdiv.innerHTML = html;
			 // var pagehtml = "<div id='storelocator-pagination' class='search-result-pagination'>"+html+"</div>";
			 // jQuery('#location-list').append(pagehtml);
		  //  }
		   
	  }
	  else if(haveresult[0].getAttribute("status") == 'no')
	  {
		sidebar.innerHTML = '';
		sidebar.innerHTML = '<div class="no-item" style="display: block;font-weight: bold;margin-left: 18px;"><strong>No stores found!</strong></div>';
		resetMap();
		return false
	  }
       map.fitBounds(bounds);
	    if (markerNodes.length <= 1) {
			   map.setZoom(15);
		   }
      });
	  
    }
	
	function selectMarker(inc)
	{
		google.maps.event.trigger(markers[inc], 'click');
	}
	
    function createMarker(latlng, name, address,timings,size,phone,i,getaddress) {

		var storeMarker = {
			url: base_url+"/modules/custom/store_locator/images/marker.png",
			size: new google.maps.Size(35, 48),
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(0, 48)
		};

		var html = '';
		
		html = '<div id="beacon" style="top: 78px; left: 106px; display: block; height: auto;"><div class="address-panel"><span class="beacon-title">'+ name +'</span><p class="beacon-address">'+address+'</p><div class="op-details">';
		if(phone!="")
		{
			html+= '<span><strong>Phone:</strong>'+phone+'</span>';
		}
		if(timings!="")
		{
			html+= '<span><strong>Timings:</strong>'+timings+'</span>';
		}
		if(size!="" && size != 0)
		{
			html+= '<span><strong>Size:</strong>'+size+' sq. ft.</span>';
		}
		html+= '</div><form action="http://maps.google.com/maps?key=AIzaSyCEh_5ZMyT9d8-BWhyM1wIYb8DYE1ODckU" method="get" target="_blank"><label for="get-directions">Get directions<span class="d-gt">&gt;</span></label><input type="hidden" name="daddr" value="'+getaddress.replace(/(<([^>]+)>)/ig,"")+'" /><input type="text" size="25" name="saddr" id="get-directions" class="sm-text-box" placeholder="Please enter your address" value="Please enter your address" onClick="if(this.value == \'\' || this.value == \'Please enter your address\'){this.value=\'\' ;}" onBlur="if(this.value == \'\' || this.value == \'Please enter your address\'){this.value=\'Please enter your address\';}"/><input type="submit" value="" class="search-location-button"></form></div><div id="beacon-ptr"></div></div>';
	
		var marker = new MarkerWithLabel({
			position: latlng,
			icon:storeMarker,
			map: map,
			draggable: false,
			raiseOnDrag: false,
			labelContent: (i+1),
			labelAnchor: new google.maps.Point(0, 40),
			labelClass: "store-custom-labels label_"+(i)+"_no",
			labelInBackground: false
		});

		marker.set("id", i);
		//map.setZoom(5);
		
		var map_x = -142;
		var map_y = -379;
		if (jQuery(window).width() < 767 ) {
			map_x = -180;
			map_y = -100;
   	}

		var myOptions = {
			content: html
			,position: latlng
			,disableAutoPan: false
			,maxWidth: 0
			,pixelOffset: new google.maps.Size(map_x, map_y)
			,boxStyle: { 
				width: "400px"
			}
			,zIndex: null
			,closeBoxMargin: "87px -96px 2px 362px"
			,closeBoxURL: base_url+"/themes/babyshop/images/close_button_sprite.png"
			,infoBoxClearance: new google.maps.Size(1, 1)
			,pane: "overlayMouseTarget"
			,enableEventPropagation: false
		};

		if (jQuery(window).width() > 767 ) {
			myOptions.isHidden = false;
		}
		else {
			myOptions.isHidden = true;
		}

		var ib = new InfoBox(myOptions);
		google.maps.event.addListener(marker, "click", function (e) {
			google.maps.event.trigger(map, "click");
			// for(var i=0;i<totalMarkerCount;i++)
			// {
			// 	var giconsc =  new google.maps.MarkerImage(base_url+"/modules/custom/store_locator/images/marker.png", new google.maps.Size(35, 45), new google.maps.Point(0, 0), new google.maps.Point(0, 45));
			// 	markers[i].setIcon(giconsc);
			// }
			var val = marker.get("id");
			jQuery("li.search-result-item").removeClass("active");
			jQuery("#"+val).addClass('active');

			// var gicons =  new google.maps.MarkerImage(base_url+"/modules/custom/store_locator/images/marker.png", new google.maps.Size(35, 45), new google.maps.Point(0, 0), new google.maps.Point(0, 45));
			// marker.setIcon(gicons);
			map.setCenter(marker.getPosition());
			map.setZoom(13);
			ib.open(map, marker);
			
		});
		
		// google.maps.event.addListener(marker, "mouseover", function() {
		// 	var val = marker.get("id");
		// 	if (!jQuery('#' + val).hasClass('search-result-item active')) {
		// 		var gicons = new google.maps.MarkerImage(base_url+"/modules/custom/store_locator/images/marker.png", new google.maps.Size(35, 45), new google.maps.Point(0, 0), new google.maps.Point(0, 45));
		// 		marker.setIcon(gicons);
		// 	}
  //       });
                
  //       google.maps.event.addListener(marker, "mouseout", function() {
		// 	var val = marker.get("id");
		// 	if (!jQuery('#' + val).hasClass('search-result-item active')) {
		// 		var gicons = new google.maps.MarkerImage(base_url+"/modules/custom/store_locator/images/marker.png", new google.maps.Size(35, 45), new google.maps.Point(0, 0), new google.maps.Point(0, 45));
		// 		marker.setIcon(gicons);
		// 	}
  //       });

		google.maps.event.addListener(ib, "closeclick", function() {
			ib.close();
			var val = marker.get("id");
			jQuery("#"+val).removeClass('active');
			//var gicons = new google.maps.MarkerImage(base_url+"/modules/custom/store_locator/images/marker.png", new google.maps.Size(35, 45), new google.maps.Point(0, 0), new google.maps.Point(0, 45));
			//marker.setIcon(gicons);
		});
		google.maps.event.addListener(map, 'click', function() {
			ib.close();
			jQuery("li.search-result-item").removeClass("active");
		});
			
	    markers.push(marker);
    }

	


    function createOption(name, distance, num) {
      var option = document.createElement("option");
      option.value = num;
      option.innerHTML = name + "(" + distance.toFixed(1) + ")";
      locationSelect.appendChild(option);
    }
	function createSidebarEntry(marker, num,name, address) {
      var div = document.createElement('div');
		
	  div.id = num
	  div.setAttribute('class','search-result-item');
     var html = '<div class="search-result-item" id="'+num+'"><div class="store-locator-search-result-no">'+(num+1)+'</div><div class="store-locator-search-result"><h3>' + name + '</h3><p>' + address+'</p></div><div class="clear"></div></div>';
      div.innerHTML = html;
      return html;
    }
    function downloadUrl(url, callback) {
      var request = window.ActiveXObject ?
          new ActiveXObject('Microsoft.XMLHTTP') :
          new XMLHttpRequest;

      request.onreadystatechange = function() {
        if (request.readyState == 4) {
          request.onreadystatechange = doNothing;
          callback(request.responseText, request.status);
        }
      };

      request.open('GET', url, true);
      request.send(null);
    }

    function parseXml(str) {
      if (window.ActiveXObject) {
        var doc = new ActiveXObject('Microsoft.XMLDOM');
        doc.loadXML(str);
        return doc;
      } else if (window.DOMParser) {
        return (new DOMParser).parseFromString(str, 'text/xml');
      }
    }

    function doNothing() {}
		//document.onload = load();
    //]]>
	
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	}
var __gmapLoaded;
window.BCInitMap = function () {
    __gmapLoaded = true;
};
(function ($) {

    window.BCMapEngine = function (id,configs) {
        switch (bookingCore.map_provider) {
            case "osm":
                return new OsmMapEngine(id,configs);
                break;
            case "gmap":
                $instance = new GmapEngine(id,configs);

                const waitForGMap = setInterval(() => {
                    if (__gmapLoaded) {
                      clearInterval(waitForGMap);
                      console.log('Google Maps is loaded');
                      // Safe to use google.maps now
                      $instance.init();
                    }
                  }, 100);
                
                return $instance;
                break;
            case "yandex":
                const yInstance = new YandexEngine(id, configs);
                const waitForYandex = setInterval(() => {
                    if (window.ymaps) {
                        clearInterval(waitForYandex);
                        yInstance.init();
                    }
                }, 100);
                return yInstance;
        }
    };

    function BaseMapEngine(id,options){
        var defaults = {};
    }

    BaseMapEngine.prototype.getOption = function (key) {

        if(typeof this.options[key] == 'undefined'){

            if(typeof this.defaults[key] != 'undefined'){
                return this.defaults[key];
            }
            return null;

        }
        return this.options[key];

    };


    function OsmMapEngine(id,options){
        this.defaults = {
            fitBounds:true
        };
        var el = {};
        this.map = null;
        this.id = id;
        this.options = options;
        this.markers = [];
        this.bounds = null;

        this.init();

        return this;
    }

    OsmMapEngine.prototype = new BaseMapEngine();

    OsmMapEngine.prototype.initScripts = function (func) {
        func();
    };

    OsmMapEngine.prototype.init = function () {

        var me = this;

        this.el  = $('#'+this.id);

        this.initScripts(function () {

            var center = me.getOption('center');
            var zoom = me.getOption('zoom');

            me.map = L.map(me.id).setView(center, zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(me.map);

            var rd = me.getOption('ready');
            if(typeof rd == "function"){
                rd(me);
            }

        });

    };

    OsmMapEngine.prototype.addMarker = function (latLng,options) {

        // if(typeof options.icon_options.iconUrl == 'undefined'){
        //     options.icon_options.iconUrl = bookingCore.url+'/images/favicon.png';
        // }
        // if(options.icon_options){
        //     options.icon = L.icon(options.icon_options);
        // }

        var m = L.marker(latLng,options).addTo(this.map);

        this.markers.push(m);

    };
    OsmMapEngine.prototype.addMarker2 = function (marker) {

        var options = {
            icon_options:{
                iconUrl:''
            }
        };
            options.icon_options.iconUrl = marker.marker
        if(options.icon_options){
            options.icon = L.icon(options.icon_options);
        }

        var m = L.marker([marker.lat,marker.lng],options).addTo(this.map);

        this.markers.push(m);

    };

    OsmMapEngine.prototype.addMarkers = function (markers) {

        for(var i = 0 ; i < markers.length; i++){

            this.addMarker(markers[i][0],markers[i][1]);

        }

        if(this.getOption('fitBounds'))
        {
            this.bounds = [];
            for (var key in this.markers) {
                var marker = this.markers[key];
                this.bounds.push([ marker._latlng.lat , marker._latlng.lng ])
            }
            try {
                this.map.fitBounds(this.bounds);
            }catch (e) {
                console.log(e);
            }
            this.map.invalidateSize();
        }

    };
    OsmMapEngine.prototype.addMarkers2 = function (markers) {
        for(var i = 0 ; i < markers.length; i++){
            this.addMarker2(markers[i]);
        }
        if(this.getOption('fitBounds'))
        {
            this.bounds = [];
            for (var key in this.markers) {
                var marker = this.markers[key];
                this.bounds.push([ marker._latlng.lat , marker._latlng.lng ])
            }
            try {
                this.map.fitBounds(this.bounds);
            }catch (e) {
                console.log(e);
            }
            this.map.invalidateSize();
        }
    };

    OsmMapEngine.prototype.clearMarkers = function (markers) {

        for(var i = 0; i < this.markers.length; i++){

            this.map.removeLayer(this.markers[i]);

        }

        this.markers = [];

    };

    OsmMapEngine.prototype.on = function (type,func) {

        switch (type) {
            case "click":
                return this.map.on(type,function(e){
                    func([
                        e.latlng.lat,
                        e.latlng.lng,
                    ])
                });
            case "zoom_changed":
                return this.map.on('zoomend',function(e){
                    func(e.target.getZoom())
                });
            break;
        }

    };

    OsmMapEngine.prototype.searchBox = function (classSearchBox ,func) {
        classSearchBox.hide();
    }

    function GmapEngine(id,options){

		this.defaults = {
            fitBounds:true
        };
        var el = {};
        this.map = null;
        this.id = id;
        this.options = options;
        this.markersPositions = [];
        this.markers = [];
        var bounds = null;
        this.infoboxs = [];

        return this;

    }

    GmapEngine.prototype = new BaseMapEngine();

    GmapEngine.prototype.initScripts = function (func) {

        func();
        return;
        if(typeof window.bc_gmap_script_inited != 'undefined') return;
        if(this.getOption('disableScripts')){
            func();
            return;
        }

        var head= document.getElementsByTagName('head')[0];
        var script= document.createElement('script');
        script.type= 'text/javascript';
        script.src= 'https://maps.googleapis.com/maps/api/js?key='+bookingCore.map_gmap_key+'&libraries=places';
        head.appendChild(script);

		var script2 = document.createElement('script');
		script2.type= 'text/javascript';
		script2.src= bookingCore.url+'/libs/infobox.js';
		head.appendChild(script2);

        window.bc_gmap_script_inited = true;

        script.onload = function(){
            func();
        }
    };

    GmapEngine.prototype.init = function () {

        var me = this;

        this.el  = $('#'+this.id);

        this.initScripts(function () {

            var center = me.getOption('center');
            var zoom = me.getOption('zoom');

            me.map = new google.maps.Map(document.getElementById(me.id), {
                center: {lat:center[0],lng:center[1]},
                zoom: zoom,
                maxZoom:15,
                mapId: 'BC_MAP_ID',
            });

            var rd = me.getOption('ready');
            if(typeof rd == "function"){
                rd(me);
                if(me.getOption('markerClustering'))
                {
                    new markerClusterer.MarkerClusterer(me.map, me.markers, {
                        imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
                    });
                }
            }
        });

    };

    GmapEngine.prototype.addMarker = async function (latLng,options) {

        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        let markerImg = null;
    
        if(options?.icon_options?.iconUrl){
            markerImg = document.createElement('img');
            markerImg.src = options.icon_options.iconUrl;
        }

        var m = new AdvancedMarkerElement({
            position: {
                lat:latLng[0],
                lng:latLng[1]
            },
            map: this.map,
            content: markerImg
        });

        this.markers.push(m);

    };

    GmapEngine.prototype.addMarker2 = async function (marker) {

        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        const markerImg = document.createElement('img');
        markerImg.src = marker.marker;
        var m = new AdvancedMarkerElement({
            position: {
                lat:marker.lat,
                lng:marker.lng
            },
            map: this.map,
            content: markerImg
        });

        if(marker.infobox){
			const contentString = marker.infobox;
            const infowindow = new google.maps.InfoWindow({
                content: contentString,
                ariaLabel: "Uluru",
            });

            this.infoboxs.push(infowindow);
			var me = this;
			m.addListener('click', function() {

                // Close Old
                for(var i = 0 ; i < me.infoboxs.length ; i++){
                    me.infoboxs[i].close();
                }
			    infowindow.open({
                    anchor: m,
                    map: me.map,
                });

			    me.map.panTo(m.position);

                if(window.lazyLoadInstance){
                    window.lazyLoadInstance.update();
                }
			});


        }

        this.markers.push(m);
        this.markersPositions.push(m.position);

    };

    GmapEngine.prototype.addMarkers2 = function (markers) {
        // Just alias
        return this.addMarkers(markers);

    };
    GmapEngine.prototype.addMarkers = async function (markers) {

        await Promise.all(markers.map(marker => this.addMarker2(marker)));

        if(this.getOption('fitBounds'))
        {
            this.bounds = new google.maps.LatLngBounds();

            for(var i = 0; i < this.markersPositions.length; i++){

                this.bounds.extend(this.markersPositions[i]);

            }
            this.map.fitBounds(this.bounds);
        }

    };

    GmapEngine.prototype.clearMarkers = function () {
        if(this.markers.length > 0){
            for(var i = 0; i < this.markers.length; i++){
                this.markers[i].setMap(null);
            }
        }

        this.markers = [];
        this.markersPositions = [];

        this.infoboxs = [];

    };

    GmapEngine.prototype.on = function (type,func) {
        switch (type) {
            case "click":
                return this.map.addListener(type,function(e){
                    let zoom = this.getZoom();
                    func([
                        e.latLng.lat(),
                        e.latLng.lng(),
                        zoom,
                    ])
                });
            break;
            case "zoom_changed":
                return this.map.addListener(type,function(e){
                    let zoom = this.getZoom();
                    func(
                        zoom
                    )
                });
            break;
        }
    };

    GmapEngine.prototype.searchBox = function (classSearchBox ,func) {
        var me = this;
        var searchBox = new google.maps.places.SearchBox(classSearchBox[0]);
        google.maps.event.addListener(searchBox, 'places_changed', function() {
            var places = searchBox.getPlaces();
            if (places.length == 0) {
                return;
            }
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0, place ; place = places[i]; i++) {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }
                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
                if(i===0){
                    func([
                        place.geometry.location.lat(),
                        place.geometry.location.lng(),
                        me.map.getZoom()]
                    );
                }
            }
            me.map.fitBounds(bounds);
        });
    }

    function YandexEngine(id,options){
        this.defaults = {
            fitBounds:true
        };
        var el = {};
        this.map = null;
        this.id = id;
        this.options = options;
        this.markersPositions = [];
        this.markers = [];
        var bounds = null;
        this.infoboxs = [];

        return this;

    }

    YandexEngine.prototype = new BaseMapEngine();
    YandexEngine.prototype.initScripts = function (func) {
        func();
        return;
        if(typeof window.bc_gmap_script_inited != 'undefined') return;
        if(this.getOption('disableScripts')){
            func();
            return;
        }

        var head= document.getElementsByTagName('head')[0];
        var script= document.createElement('script');
        script.type= 'text/javascript';
        script.src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=' + '98746b15-4156-4225-98e7-c8293b0eeba2';
        head.appendChild(script);

        var script2 = document.createElement('script');
        script2.type= 'text/javascript';
        script2.src= bookingCore.url+'/libs/infobox.js';
        head.appendChild(script2);

        window.bc_gmap_script_inited = true;

        script.onload = function(){
            func();
        }
    };

    YandexEngine.prototype.init = function () {
        var me = this;
        this.el = $('#' + this.id);

        ymaps.ready(function () {
            var center = me.getOption('center'); // [lat, lng]
            var zoom = me.getOption('zoom') || 10;

            me.map = new ymaps.Map(me.id, {
                center: center,
                zoom: zoom,
                controls: ['zoomControl', 'geolocationControl']
            });

            var rd = me.getOption('ready');
            if (typeof rd === "function") {
                rd(me);
            }
        });
    };

})(jQuery);

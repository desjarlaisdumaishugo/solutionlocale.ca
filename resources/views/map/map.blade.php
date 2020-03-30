@extends('layouts.public')

@php
function generateSanitizedPlaceData($place)
{
    if($place->hide_address)
    {
        unset($place->address);
    }

    return json_encode($place);
}
@endphp

@section('styles-head')
<link href="https://api.mapbox.com/mapbox-gl-js/v1.9.0/mapbox-gl.css" rel="stylesheet" />
<style>
    body, html { margin: 0; padding: 0; height: 100%; }
    main { height: calc(100% - 145px); }
    #map-wrapper {position: relative; min-height: 500px; height: 100%; width: 100%; }
	#map { position: absolute; top: 0; bottom: 0; width: 100%; }
</style>
@endsection

@section('scripts-body')
    <script type="text/javascript">

        function generatePlaceHTML(place)
        {
            var place = JSON.parse(place);

            var innerHTML = '<strong>'+place.name+'</strong>';

            if(place.address)
            {
                innerHTML += '<p>'+place.address+', ' + place.city + '</p>';
            }
            
            innerHTML += '<p><a href="'+place.url+'">Site web</a></p>';

            //TODO : Vincent add more stuff...

            return innerHTML;
        }

        $(function() {
            window.mapboxgl.accessToken = 'pk.eyJ1Ijoib2tpZG9vIiwiYSI6ImNrOGVpNjZsMTE1Ym4zZWw2YWJuNDFtOHYifQ.j9TamQ7clfM4xYNXkTtVKw';

            var map = new window.mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v9',
                zoom: 5.338278866434004,
                center: {lng: -72.69407606492541, lat: 46.205834144697576}
            });

            map.on('load', function() {
                map.loadImage(
                    '/images/pin-verte.png',
                    function(error, image) {
                        if (error) throw error;

                        map.addImage('pin-verte', image);

                        map.addSource('places', {
                            'type': 'geojson',
                            'data': {
                                'type': 'FeatureCollection',
                                'features': [
                                    @foreach($places as $place)
                                    {
                                        'type': 'Feature',
                                        'properties': {
                                            'description': @php echo generateSanitizedPlaceData($place); @endphp,
                                        },
                                        'geometry': {
                                            'type': 'Point',
                                            'coordinates': [{{ $place->long }}, {{ $place->lat }}]
                                        }
                                    },
                                    @endforeach
                                ]
                            }
                        });

                        // Add a layer showing the places.
                        map.addLayer({
                            'id': 'places',
                            'type': 'symbol',
                            'source': 'places',
                            'layout': {
                                'icon-image': 'pin-verte',
                                'icon-size': 0.4,
                                'icon-allow-overlap': true
                            }
                        });

                        // Add geolocate control to the map.
                        map.addControl(
                            new mapboxgl.GeolocateControl({
                                positionOptions: {
                                    enableHighAccuracy: true
                                },
                                trackUserLocation: true
                            })
                        );

                        // When a click event occurs on a feature in the places layer, open a popup at the
                        // location of the feature, with description HTML from its properties.
                        map.on('click', 'places', function (e) {
                            var coordinates = e.features[0].geometry.coordinates.slice();
                            var description = e.features[0].properties.description;

                            // Ensure that if the map is zoomed out such that multiple
                            // copies of the feature are visible, the popup appears
                            // over the copy being pointed to.
                            while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                                coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                            }

                            new mapboxgl.Popup()
                                .setLngLat(coordinates)
                                .setHTML(generatePlaceHTML(description))
                                .addTo(map);
                        });

                        // Change the cursor to a pointer when the mouse is over the places layer.
                        map.on('mouseenter', 'places', function () {
                            map.getCanvas().style.cursor = 'pointer';
                        });

                        // Change it back to a pointer when it leaves.
                        map.on('mouseleave', 'places', function () {
                            map.getCanvas().style.cursor = '';
                        });
                    }
                );
            });
        });
    </script>
@endsection

@section('content')
    <main role="main">
        <div id="map-wrapper">
            <div id="map"></div>
        </div>
    </main>
@endsection
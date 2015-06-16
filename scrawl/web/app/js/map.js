//angular controller for angular-leaflet-directive homepage map

app.controller('MapCtrl', ['$scope', '$http',
	function ($scope, $http) {

		var geos = [];
		$http.get('/ajax/latlon').success(function(data){

			angular.forEach(data, function(value, key){
				geos[key] = {
					lat: value[0], 
					lng: value[1],
					focus: false,
					icon: {}
				};

				// console.log('GEOS key' + key);
				// console.log('GEOS lat' + geos[key].lat);
				// console.log('GEOS lng' + geos[key].lng);

			});
			$scope.geos = geos;
		});

		$scope.highlight = function(marker_id){

			angular.forEach($scope.markers, function(value, key){
				$scope.markers[key].icon = $scope.defaultIcon;
			});

			$scope.markers[marker_id].icon = $scope.awesomeMarkerIcon;
		};

		angular.extend($scope, {
			center: {
				autoDiscover: true,
				lat: 37.760944207425936,
				lng: -122.43850708007811,
				zoom: 6
			},
			markers: geos,

			defaultIcon: {},

			awesomeMarkerIcon: {
				type: 'awesomeMarker',
				icon: 'star',
				markerColor: 'red'
			},
			events: {
				markers: {
					enable: ['dragend']
                // ,logic: 'emit'
            }
        }
    });

	}]);

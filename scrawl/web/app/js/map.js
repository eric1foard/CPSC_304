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

//angular controller for angular-leaflet-directive image upload map


app.controller('MarkerCtrl', ['$scope',
	function($scope)
	{

		var mainMarker = {
			lat: 51,
			lng: 0,
			focus: true,
			message: "drag me to select art location",
			draggable: true
		};

		angular.extend($scope, {
			london: {
				lat: 51.505,
				lng: -0.09,
				zoom: 8
			},
			markers: {
				mainMarker: angular.copy(mainMarker)
			},
			position: {
				lat: 51,
				lng: 0
			},
			events: {
				markers: {
					enable: ['dragend']
                // ,logic: 'emit'
            }
        }
    });

		$scope.$on("leafletDirectiveMarker.dragend", function(event, args){
			console.log("position updated");
			$scope.position.lat = args.model.lat;
			$scope.position.lng = args.model.lng;
		});

	}]);

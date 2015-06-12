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
//angular controller for angular-leaflet-directive image upload map


app.controller('MarkerCtrl', ['$scope',
	function($scope)
	{

		var mainMarker = {
			lat: 49.28505157127188,
			lng: -123.13133239746094,
			focus: true,
			message: "drag me to select art location",
			draggable: true
		};

		angular.extend($scope, {
			vancouver: {
				lat: 49.28505157127188,
				lng: -123.13133239746094,
				zoom: 8
			},
			markers: {
				mainMarker: angular.copy(mainMarker)
			},
			position: {
				lat: 49.28505157127188,
				lng: -123.13133239746094
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
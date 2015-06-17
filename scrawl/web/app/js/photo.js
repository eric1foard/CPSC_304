app.controller('PhotoCtrl', [ '$scope', '$http', '$modal', '$location',
	function ($scope, $http, $modal, $location) {

		var photos = [];
		var userPhotos = [];
		var geos = [];

		$http.get('/ajax/photos').success(function(data){

			angular.forEach(data, function(value, key){
				//photos.push({ value['path'], key });
				photos[key] = {
					path: value['path'],
					key: key
				};
				console.log('photo key type:' + typeof(key));
				console.log('photo key:' + key);
				console.log('photo path:' + photos[key].path);
			});
			$scope.photos = photos;

			angular.forEach(data, function(value, key){

				//lat and lon are strings from JSON object
				latitude = parseFloat(value['latitude']);
				longitude = parseFloat(value['longitude']);

				geos[key] = {
					lat: latitude, 
					lng: longitude,
					focus: false,
					icon: {}
				};

				console.log('GEOS key ' + key);
				console.log('GEOS lat ' + geos[key].lat);
				console.log('GEOS lng ' + geos[key].lng);
			});
		});

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

		$scope.highlight = function(marker_id){

			console.log('from highlight, marker id  '+marker_id);

			angular.forEach($scope.markers, function(value, key){
				$scope.markers[key].icon = $scope.defaultIcon;
			});

			$scope.markers[marker_id].icon = $scope.awesomeMarkerIcon;
		};


		$scope.getUserPhotos = function() {

			var url = $location.absUrl();
			slash = url.lastIndexOf('/');
			username = url.slice(slash+1, url.length);

			console.log('PATH: ' + username);

			$http.get('/ajax/userphotos/'+username).success(function(data){
				console.log('DATA' + data); 
				angular.forEach(data, function(value, key){
					userPhotos.push({value, key});
					console.log('VALUE  '+value);
					console.log('KEY  '+key);

				});
				$scope.userPhotos = userPhotos;
			});

		};

		$scope.getArtInfo = function(uploadLocation) {

			uploadLocation = uploadLocation.toString();
			var photoPK = '';

			//uploadLocation is a string of the form 'upload/photoPK',
			//we want only the photo PK
			photoPK = uploadLocation.substring('uploads/'.length, uploadLocation.length);

			console.log("from getArtInfo " + photoPK);
			$scope.animationsEnabled = true;

			$http.get('/ajax/viewdata/update/'+photoPK).success(function (data){
				console.log(data);

			}).error(function (data) {
				console.log(data);
			});
			
			$http.get('/ajax/photo/'+ photoPK).success(function (data){

				$scope.artInfo = data;

				var modalInstance = $modal.open({
					animation: true,
					templateUrl: 'modal.html',
					controller: 'ModalInstanceCtrl',
					size: 'lg',
					resolve: {
						artInfo: function() {
							return $scope.artInfo;
						}
					}

				});
			});
		};

	}]);

// Please note that $modalInstance represents a modal window (instance) dependency.
// It is not the same as the $modal service used above.

app.controller('ModalInstanceCtrl', function ($scope, $modalInstance, artInfo) {

	$scope.artInfo = artInfo;

	console.log("from the ModalInstanceCtrl: "+ artInfo.latitude);

	$scope.cancel = function () {
		$modalInstance.dismiss('cancel');
	};
});
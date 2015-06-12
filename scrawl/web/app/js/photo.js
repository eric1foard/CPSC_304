app.controller('PhotoCtrl', [ '$scope', '$http', '$modal',
 function ($scope, $http, $modal) {

	var photos = [];
	$http.get('/ajax/photos').success(function(data){

		angular.forEach(data, function(value, key){
			photos.push({value, key});
		});
		$scope.photos = photos;

	});

	$scope.getArtInfo = function(id) {
		console.log("from getArtInfo " + id);

		$scope.animationsEnabled = true;
		$http.get('/ajax/photo/'+ id).success(function (data){

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
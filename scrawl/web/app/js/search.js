app.controller('SearchCtrl', function ($scope, $http) {

	$scope.isCollapsed = true;
	var tagNames = [];

	$scope.search = {
		tags: []
	};
	var photos = [];

	$scope.showTags = function() {

		$scope.isCollapsed = !$scope.isCollapsed;
		console.log('from show tags' + $scope.isCollapsed);

		//only make call to get tags if opening modal
		if ($scope.isCollapsed === false)
		{
			$http.get('/ajax/tags').success(function(data){
				console.log('DATA' + data); 

				angular.forEach(data, function(tagName){
					tagNames.push(tagName);
					console.log('TAG  '+tagName);
				});
				$scope.tagNames = tagNames;
			});
		}

		else 
		{
			//clear array
			$scope.tagNames.length = 0;
			$scope.search.tags.length = 0;
			console.log('empty' + $scope.tagNames);
		}
	};

	$scope.submitSearch = function() {
		$scope.$parent.photos.length=0;
		$http.get('/ajax/search/tags/' + $scope.search.tags).success(function(data){
			console.log('FROM SUBMIT SEARCH'); 

			angular.forEach(data, function(value, key){
					photos.push({value, key});
					console.log('VALUE  '+value);
					console.log('KEY  '+key);
			});
			$scope.$parent.photos = photos;
			console.log('photos '+photos[0]);

		});
	};



	// $scope.submitSearch = function() {

	// 	$http({
	// 		method: 'POST',
	// 		url: '/ajax/search/tags',
	// 		data: $.param(['test','hey','yo']),
	// 		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	// 	})
	// 	.success(function(data){
	// 		console.log('from submitSearch success' + data);

	// 	})
	// 	.error(function(){
	// 		console.log('from submitSearch failure');
	// 	});
	// };


});
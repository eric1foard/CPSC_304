//resolve conflict between angular interpolation symbols and
//twig interpolation symbols by setting angular interps to 
// [[ and ]]


var app = angular.module('Scrawl', ['akoenig.deckgrid', 'leaflet-directive']);

(function (window, angular) {
    'use strict';
    app.config(['$interpolateProvider', '$httpProvider', function ($interpolateProvider, $httpProvider) {
        // change default characters for interpolateion
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');
    }]);

})(window, angular);
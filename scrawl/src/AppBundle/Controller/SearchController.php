<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Entity\Photo;

/**
 * Search controller
 * Logic for search feature
 *
 * note that ALL methods are called AJAXly from the anjular controller photo.js
 *
 * in ALL cases, the response is a hash in which they key is the name of the photo file
 * and the value is the path relative to the web directory (i.e., uploads/username_filename.filetye)
 * so that the value can be passed directly to an HTML img directive in the view to render the photo
 */
class SearchController extends Controller
{
    //expose search logic to angular controller
    public function searchAction(Request $request)
    {

        $searchArgs = $this->parseArguments($request->get('params'));
        $radius = $searchArgs['radius'];
        $searchTags = $searchArgs['tags'];

        $this->createTempDivisionTable($searchTags);

        $searchResult = $this->searchDistance($radius);

        $paths = $this->preparePhotoJson($searchResult);

        $this->dropTempTable();

        return $paths;
    }

        //execute query to search by distance based on user input
    public function searchDistance($radius)
    {
        $sql = 'SELECT path, latitude, longitude, SQRT(POW((latitude - (SELECT latitude FROM scrawl_users WHERE username =:username)), 2)+
            POW(((SELECT longitude FROM scrawl_users WHERE username =:username) - longitude), 2)) AS distance FROM scrawl_photos 
WHERE exists (SELECT DISTINCT ht.path as path, p.latitude, p.longitude FROM has_tag ht, scrawl_photos p WHERE ht.path=p.path 
    AND NOT EXISTS (SELECT t.element FROM temp t WHERE t.element NOT IN (SELECT ht3.tagName FROM has_tag ht3 WHERE ht3.path = ht.path))) 
HAVING distance <=:radius ORDER BY distance;';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        $username = $this->getLoggedInUser();

        $stmt->bindValue('username', $username);
        $stmt->bindValue('radius', $radius);

            //execute query
        $stmt->execute();

            //get all rows of results 
        $entities = $stmt->fetchAll();

        return $entities;
    }

    //execute query to search tags by divsion on temp table
    // public function searchTags()
    // {
    //     $sql = 'SELECT DISTINCT ht.path as path, p.latitude, p.longitude 
    //     FROM has_tag ht, scrawl_photos p WHERE ht.path=p.path 
    //     AND NOT EXISTS (SELECT t.element FROM temp t WHERE t.element NOT IN (SELECT ht3.tagName FROM has_tag ht3 WHERE ht3.path = ht.path))';

    //     $stmt = $this->getDoctrine()->getManager()
    //     ->getConnection()->prepare($sql);

    //         //execute query
    //     $stmt->execute();

    //         //get all rows of results 
    //     $entities = $stmt->fetchAll();

    //     return $entities;
    // }


    //parse request object for search parameters
    public function parseArguments($searchString)
    {
        //find radius search parameter from string
        $pos = strpos($searchString, '&');
        $radius = substr($searchString, $pos+1, strlen($searchString));

        //remove radius and create array from comma separated
        //string of tags that remains
        $replace = substr($searchString, $pos, strlen($searchString));
        $tagArgs = str_replace($replace, '', $searchString);
        $searchTags = explode(',', $tagArgs);

        return ['radius'=>$radius, 'tags'=>$searchTags];
    }

    public function getLoggedInUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser()->getId();
    }

    //consume response from DB and build JSON response to send to
    //angular photo controller
    public function preparePhotoJson($queryResult)
    {
        $result = [];
        //need to index by integer because leaflet needs integer for markers
        for ($i=0; $i < sizeof($queryResult); $i++) { 
            $result[$i] = ['path'=>'uploads/'.$queryResult[$i]['path'], 
            'latitude'=>$queryResult[$i]['latitude'], 
            'longitude'=>$queryResult[$i]['longitude']];
        }
        return new JsonResponse($result);
    }

    public function createTempDivisionTable($array)
    {
            //create a temporary table
        $sql = 'CREATE TEMPORARY TABLE temp(element VARCHAR(32))';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        $stmt->execute();

        foreach ($array as $elt) {
            $sql = 'INSERT INTO temp value(:elt)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            $stmt->bindValue('elt', $elt);


            $stmt->execute();

        }
        return;
    }

    public function dropTempTable()
    {
        $sql = 'DROP TABLE IF EXISTS temp';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

            //execute query
        $stmt->execute();
    }
}

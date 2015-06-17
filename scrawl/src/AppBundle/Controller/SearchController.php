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
    public function searchTagsAction(Request $request)
    {
        $searchString = $request->get('params');

        $searchTags = explode(',', $searchString);

        $this->createTempDivisionTable($searchTags);

        $sql = 'SELECT DISTINCT ht.path as path, p.latitude, p.longitude 
        FROM has_tag ht, scrawl_photos p WHERE ht.path=p.path 
        AND NOT EXISTS (SELECT t.element FROM temp t WHERE t.element NOT IN (SELECT ht3.tagName FROM has_tag ht3 WHERE ht3.path = ht.path))';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

            //execute query
        $stmt->execute();

            //get all rows of results 
        $entities = $stmt->fetchAll();

        $paths = $this->preparePhotoJson($entities);

        $this->dropTempTable();

        return $paths;
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

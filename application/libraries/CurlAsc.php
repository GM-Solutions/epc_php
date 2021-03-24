<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CurlAsc
 *
 * @author pavaningalkar
 */
class CurlAsc {
    function __construct($urls)
    {
        
        // Create get requests for each URL
        $mh = curl_multi_init();

        foreach($urls as $i => $url)
        {
            $ch[$i] = curl_init($url);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($mh, $ch[$i]);
        }

        // Start performing the request
        do {
            $execReturnValue = curl_multi_exec($mh, $runningHandles);
        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);

        // Loop and continue processing the request
        while ($runningHandles && $execReturnValue == CURLM_OK) 
        {
            // !!!!! changed this if and the next do-while !!!!!

            if (curl_multi_select($mh) != -1) 
            {
                usleep(100);
            }

            do {
                $execReturnValue = curl_multi_exec($mh, $runningHandles);
            } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
        }

        // Check for any errors
        if ($execReturnValue != CURLM_OK) 
        {
            trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
        }

        // Extract the content
        foreach ($urls as $i => $url)
        {
            // Check for errors
            $curlError = curl_error($ch[$i]);

            if ($curlError == "") 
            {
                $responseContent = curl_multi_getcontent($ch[$i]);
                $res[$i] = $responseContent;
            } 
            else 
            {
                print "Curl error on handle $i: $curlError\n";
            }
            // Remove and close the handle
            curl_multi_remove_handle($mh, $ch[$i]);
            curl_close($ch[$i]);
        }

        // Clean up the curl_multi handle
        curl_multi_close($mh);

        // Print the response data
        //print "response data: " . print_r($res, true);
        return $res;
    }
}

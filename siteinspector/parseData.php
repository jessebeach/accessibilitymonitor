<?php
        
        //THIS SCRIPT READS OUTPUT FROM QUAIL, CONVERT IT TO JSON AND SENDS IT TO SOLR
	//rein@mechanicape.com
        error_reporting(0);
	if (isset($argv) && count($argv)>1) { $parameter=$argv[1]; }
	if (isset($_REQUEST["url"])) { $parameter=$_REQUEST["url"]; }
	//print $parameter;
	
        processFile($parameter);

	function filterDomein($textWithUrlAtEnd)
	{
		$arr=explode("http",$textWithUrlAtEnd);
		return trim($arr[1]);
	}
	
	function getDomain($url)
	{
             $domainArr=explode("/",$url);
	     return $domainArr[2];
        }


	function processFile($uri)
	{
		$res=array();
		$dataArr=file($uri);
	
		while (list($index,$data)=each($dataArr))
		{
			if (substr($data,0,1)!="{")
			{
				unset($res[$index]);
			}else
			{
				$res[$index]=processRecord($index,$data);
			} 

		}
		$report='<h1>Accessibility Test results</h1>';
		$report.="\n";
		$report.=print_r($res,true);
		//print $report;
	}

	function post($fields){
                        
			$ch = curl_init();
			$post_url = 'http://dev-crawler.wrl.org:8983/solr/phantomcore/update?commit=true';
			$json_fields='{"add":{"doc":'.json_encode($fields).'}}' ;
			$header = array("Content-type:application/json; charset=utf-8");
			curl_setopt($ch, CURLOPT_URL, $post_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_fields);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
 
			$data = curl_exec($ch);
 
			if (curl_errno($ch)) {
			   throw new Exception ( "curl_error:" . curl_error($ch) );
				print $curl_error($ch);
			} else {
			   curl_close($ch);
			print $data;
			   return TRUE;
			}
		}

	function processRecord($index,$data)
	{
		$testresult=$data;
		$json=json_decode($data);
		$json->url_main="";
		$json->url_sub="";
		$urlarr=parse_url($json->url);
                 $fqdArr=explode(".",$urlarr["host"]);
                 if (count($fqdArr)>2)
                 {  
                    $partcount=count($fqdArr);
                    $json->url_main=$fqdArr[$partcount-2].".".$fqdArr[$partcount-1];
                 }
                 else
                 {
                   $json->url_main=$urlarr["host"];
                 }
                 $json->url_sub=$urlarr["host"];

		$json->id=(string)(time().$index);
		if (isset($json->wcag) && ($json->wcag!=""))
                {
		 $url=json_decode($json->url);
		 $wcag=json_decode($json->wcag);
		 $json->applicationframework="";
		 $json->techniques="";
		 while (list($applicationNr,$techniques)=each($wcag))
		 {
		   $json->applicationframework[]=$applicationNr;
		   if (count($techniques)>0)
                   {
		      foreach($techniques as $technique)
		      {
                         foreach($technique as $techniqueStr)
                         {
		   	    $thistechniques[]=$techniqueStr;
                         }
                      }
                   }	
		 }
		 $json->techniques=array_unique($thistechniques);
                }
		post($json);
		
		return $testresult;

	}

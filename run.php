<?php

require __DIR__ . '/vendor/autoload.php';

use Phpoaipmh\Client;
use Phpoaipmh\Endpoint;
use Phpoaipmh\HttpAdapter\GuzzleAdapter;
use GuzzleHttp\Client as GuzzleClient;

$zbUrl = getenv( 'zbMATHUrl' );
$metaFormat = 'oai_zbmath';
$date = date( DateTime::ISO8601 );
$options = [];

if ( getenv( 'zbMATHUser' ) !== false ) {
	$options = [
		'auth' => [ getenv( 'zbMATHUser' ), getenv( 'zbMATHPassword' ) ],
		'verify' => false,
	];
}
$guzzle = new GuzzleAdapter( new GuzzleClient( $options ) );

$myEndpoint = new Endpoint( new Client( $zbUrl, $guzzle ) );

$results = $myEndpoint->listMetadataFormats();

$iterator = $myEndpoint->listRecords( $metaFormat );
echo "Total count is " . ( $iterator->getTotalRecordCount() ?: 'unknown' );

$iterator = $myEndpoint->listRecords( 'oai_zbmath' );

// Write the header
echo /** @lang XML */
"<?xml version=\"1.0\" encoding=\"utf-8\"?>
<OAI-PMH xmlns=\"http://www.openarchives.org/OAI/2.0/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
		xsi:schemaLocation=\"http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd\">
	<responseDate>$date</responseDate>
	<request metadataPrefix=\"$metaFormat\" verb=\"ListRecords\">$zbUrl</request>
	<ListRecords>
";

foreach ( $iterator as $rec ) {
	try{
		echo $rec->asXML();
	} catch (Exception $exception){
		error_log(print_r($rec, true), $exception->getMessage() );
	}
}

// Write the footer
echo /** @lang XML */
<<<XML

	</ListRecords>
</OAI-PMH>
XML;

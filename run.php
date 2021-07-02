<?php

require __DIR__ . '/vendor/autoload.php';

use Phpoaipmh\Client;
use Phpoaipmh\Endpoint;
use Phpoaipmh\Exception\MalformedResponseException;
use Phpoaipmh\HttpAdapter\GuzzleAdapter;
use GuzzleHttp\Client as GuzzleClient;

$zbUrl = getenv( 'zbMATHUrl' ) ?: 'https://oai.zbmath.org/v1/';
$metaFormat = getenv( 'zbMATHFormat' ) ?: 'oai_dc';
$max_request = (int) getenv( 'zbMATHLimit' ) ?: 900;
$date = date( DateTime::ISO8601 );
$options = [];

if ( getenv( 'zbMATHApiKey' ) !== false ) {
	$options = [
		'headers' => [ 'X-API-KEY' => getenv( 'zbMATHApiKey' ) ],
	];
}
$guzzle = new GuzzleAdapter( new GuzzleClient( $options ) );

class ErrorReportingClient extends Client {
	protected function decodeResponse( $resp ): SimpleXMLElement {
		try {
			return parent::decodeResponse( $resp );
		}
		catch ( MalformedResponseException $exception ) {
			error_log( "MalformedResponseException: " . $resp );
			throw $exception;
		}
	}
}

$myEndpoint = new Endpoint( new ErrorReportingClient( $zbUrl, $guzzle ) );

$results = $myEndpoint->listMetadataFormats();

$iterator = $myEndpoint->listRecords( $metaFormat );
echo "Total count is " . ( $iterator->getTotalRecordCount() ?: 'unknown' );

// Write the header
echo /** @lang XML */
"<?xml version=\"1.0\" encoding=\"utf-8\"?>
<OAI-PMH xmlns=\"http://www.openarchives.org/OAI/2.0/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
		xsi:schemaLocation=\"http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd\">
	<responseDate>$date</responseDate>
	<request metadataPrefix=\"$metaFormat\" verb=\"ListRecords\">$zbUrl</request>
	<ListRecords>
";

$t = time();
$request_number = 0;
foreach ( $iterator as $rec ) {
	echo $rec->asXML();
	if ( time() > $t ) {
		$t = time();
		error_log( "{$t}: Crawl rate ({$request_number} records)/s." );
		$request_number = 0;
	} else {
		$request_number ++;
		if ( $request_number >= $max_request ) {
			error_log( "{$t}: Rate limit penalty: Wait one second!" );
			sleep( 1 );
		}
	}

}

// Write the footer
echo /** @lang XML */
<<<XML

	</ListRecords>
</OAI-PMH>
XML;

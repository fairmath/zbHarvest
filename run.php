<?php

require __DIR__ . '/vendor/autoload.php';

use Phpoaipmh\Client;
use Phpoaipmh\Endpoint;
use Phpoaipmh\Exception\MalformedResponseException;
use Phpoaipmh\HttpAdapter\GuzzleAdapter;
use GuzzleHttp\Client as GuzzleClient;

$zbUrl = getenv( 'zbMATHUrl' ) ?? 'https://zboai.formulasearchengine.com/v1/';
$metaFormat = 'oai_zb_preview';
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

foreach ( $iterator as $rec ) {
	echo $rec->asXML();
}

// Write the footer
echo /** @lang XML */
<<<XML

	</ListRecords>
</OAI-PMH>
XML;

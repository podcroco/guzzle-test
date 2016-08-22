<?php
require dirname(__FILE__)."/vendor/autoload.php";
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;


define("ENDPOINT", 'http://10.10.100.80:32770/1234');
define("CONCURRENCY", 10);
define("MAX_REQUESTS", 100);

///////////////////////////////////////////////////////////
// http://ENDPOINT/
//   wait in 0.2sec and return "OK"
///////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////
// Test 1. serial 100 request
$client = new Client();
$s = microtime(true);
for ($i = 0; $i<MAX_REQUESTS; $i++ ) {
	$response = $client->request('GET', ENDPOINT);
}
echo "===== Serial ".MAX_REQUESTS." request took ".sprintf("%f",microtime(true)-$s)." sec =====\n";


///////////////////////////////////////////////////////////
// Test 2. promise 100 request
$client = new Client();
$s = microtime(true);

$requests = function() {
    for ($i = 0; $i < MAX_REQUESTS; ++$i) {
        yield new Request('GET', ENDPOINT);
    }
};

$pool = new Pool($client, $requests(), [
    'concurrency' => CONCURRENCY,
//  'fulfilled' => function(ResponseInterface $response, $index) {
//      echo sprintf("%5d: %s\n", $index, $response->getBody());
//	},
]);

$promise = $pool->promise();
$promise->wait();

echo "===== promise ".MAX_REQUESTS." request took ".sprintf("%f",microtime(true)-$s)." sec =====\n";

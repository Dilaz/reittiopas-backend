<?php
/**
 * Contains ApiController -controller class
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client as HttpClient;
use Cache;

/**
 * Handles API-requests
 */
class ApiController extends Controller
{
	/**
	 * Handles bus stop api requests for given stop ID
	 * @param Request $request
	 * @param int $stopId
	 * @return Response
	 */
	public function stop(Request $request, $stopId)
	{
		// Make sure stopID is numeric
		$stopId = (int) $stopId;

		// If there is no stopId, just return empty response
		if (!$stopId) {
			return response()->json([]);
		}

		// Cache each request for 1 minute
		$val = Cache::remember('stop-' . $stopId, 1, function() use($stopId) {
			// Create new http client
			$client = new HttpClient();

			// Url & params
			$url = 'http://api.reittiopas.fi/hsl/prod/';
			$params = [
				'request' => 'stop',
				'code' => $stopId,
				'user' => env('HSL_USER', ''),
				'pass' => env('HSL_PASS', ''),
			];

			// Request the reittiopas api with queryparams
			$res = $client->request('GET', $url, [
				'query' => $params,
			]);

			// Return the response content
			return $res->getBody()->getContents();
		});

		// Return the value with json-header
		return response($val)
			->withHeaders([
				'Content-Type' => 'application/json',
			]);
	}
}

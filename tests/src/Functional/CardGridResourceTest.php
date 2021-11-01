<?php

namespace Drupal\Tests\card_grid\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Tests\rest\Functional\CookieResourceTestTrait;
use Drupal\Tests\rest\Functional\ResourceTestBase;

/**
 * Tests the Card Shuffeler API.
 *
 * @group card_grid
 */
class CardGridResourceTest extends ResourceTestBase {

  use CookieResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $format = 'json';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'application/json';

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'cookie';

  /**
   * {@inheritdoc}
   */
  protected static $resourceConfigId = 'get_card_shuffler_rest_resource';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['rest', 'card_grid'];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $auth = isset(static::$auth) ? [static::$auth] : [];
    $this->provisionResource([static::$format], $auth);
  }

  /**
   * Retrieves the game via the REST API.
   */
  public function testWatchdog() {
    $this->container->get('logger.channel.rest')->notice('Testing Card Shuffler REST API');
    $rows = 3;
    $columns = 5;

    $this->initAuthentication();
    $url = Url::fromUri('/api/card-grid?rows=' . $rows . '&columns=' . $columns);
    $request_options = $this->getAuthenticationRequestOptions('GET');

    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(403, "The restful card grid permission is required.", $response, ['4xx-response', 'http_response'], ['user.permissions'], FALSE, FALSE);

    $this->setUpAuthorization('GET');

    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceResponse(200, FALSE, $response, ['config:rest.resource.get_card_shuffler_rest_resource', 'http_response'], ['user.permissions'], FALSE, 'MISS');
    $log = Json::decode((string) $response->getBody());
    $this->assertEquals($log['meta']['cardCount'], $rows * $columns, 'Card Count is correct');
    $this->assertEquals($log['uniqueCardCount'], count($log['meta']['uniqueCards']), 'Unique Cards and the count is correct');

    $url->setRouteParameter('rows', 0);
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(404, "Rows should be between 1 and 6", $response);
    
    $url->setRouteParameter('rows', 7);
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(404, "Rows should be between 1 and 6", $response);

    $url->setRouteParameter('columns', 0);
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Columns should be between 1 and 6', $response);
    
    $url->setRouteParameter('columns', 7);
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Columns should be between 1 and 6', $response);
  
    $url->setRouteParameter('rows', 3);
    $url->setRouteParameter('columns', 5);
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Atleast one of rows and columns should be even number', $response);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    switch ($method) {
      case 'GET':
        $this->grantPermissionsToTestedRole(['restful get get_card_shuffler_rest_resource']);
        break;

      default:
        throw new \UnexpectedValueException();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function assertNormalizationEdgeCases($method, Url $url, array $request_options) {}

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessMessage($method) {}

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessCacheability() {}

}

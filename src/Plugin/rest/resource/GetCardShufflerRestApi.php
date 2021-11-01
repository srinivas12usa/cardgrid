<?php

namespace Drupal\card_grid\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\rest\ResourceResponse;

/**
 * Provides REST API for Card Memory Game.
 *
 * @RestResource(
 *   id = "get_card_shuffler_rest_resource",
 *   label = @Translation("Card Shuffler API"),
 *   uri_paths = {
 *     "canonical" = "/api/card-grid"
 *   }
 * )
 */
class GetCardShufflerRestApi extends ResourceBase {
  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Returning rest resource.
   */
  public function get() {
    // Get all parameters from URL
		$params = \Drupal::request()->query->all();

		// Check if only 2 parameters are passed and check if the parameters are rows and colums only and if one of them is even or not.
		if (count($params) != 2 || !array_key_exists('rows', $params) || !array_key_exists('columns', $params)) {
			$meta = array(
				'success' => false,
				'message' => 'Only two parameters are allowed and they should be \'rows\' and \'columns\'. Both the parameters are required.',
			);

			$data = array();
		}
		else {
			$rows = $params['rows'];
			$columns = $params['columns'];

			if ($rows <= 0 || $rows > 6) {
				$meta = array(
					'success' => false,
					'message' => 'Row count should be between 1 and 6',
				);

				$data = array();
			}
			elseif ($columns <= 0 || $columns > 6) {
				$meta = array(
					'success' => false,
					'message' => 'Column count should be between 1 and 6',
				);

				$data = array();
			}
			elseif ($rows % 2 == 0 || $columns % 2 == 0) {
				// Items.
				$items = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

				// Variables
				$cardCount = $rows * $columns;
				$uniqueCardCount = $cardCount / 2;

				$numbers = range(0, count($items) - 1);
				shuffle($numbers);
				$numbers = array_slice($numbers, 0, $uniqueCardCount);

				foreach ($numbers as $index) $uniqueCards[] = $items[$index];
        $temp = $uniqueCards;

				$cards = $values = array();

        for ($i = 0; $i < $cardCount; $i++) {
          $index = rand(0, count($uniqueCards) - 1);
				  $value = $uniqueCards[$index];
      
          $values[$value] += 1;
      
          if ($values[$value] >= 2) {
            array_splice($uniqueCards, $index, 1);
            $cards[] = $value;
          }
          else {
            $cards[] = $value;
          }
      
          #print $value . '[' . $values[$value] . ']<br />';
        }
    
        $k = 0;
        for ($i = 0; $i < $rows; $i++) {
          for ($j = 0; $j < $columns; $j++) {
            $tCards[$i][$j] = $cards[$k];
            $k++;
          }
        }
        #print '<hr />' . var_dump($cards);
        #die;
		
				$meta = array(
					'success' => true,
					'cardCount' => $cardCount,
					'uniqueCardCount' => $uniqueCardCount,
					'uniqueCards' => $temp,
				);

				$data = array(
					'cards' => $tCards,
				);
			}
			else {
				$meta = array(
					'success' => false,
					'message' => 'Either rows or columns needs to be an even number.',
				);

				$data = array();
			}
		}

		// The response.
		$output = array(
			'meta' => $meta,
			'data' => $data,
		);
        
      $build = array(
        '#cache' => array(
          'max-age' => 0,
        ),
      );
      return (new ResourceResponse($output))->addCacheableDependency($build);
  }
}
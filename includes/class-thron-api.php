<?php

class ThronAPI {

	private $token_id;

	private $appId;

	private $clientId;

	private $appKey;

	private static $instance = null;

	public static function getInstance() {
		if ( self::$instance == null ) {
			$c              = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}


	public function __construct( $appId, $clientId, $appKey ) {

		$this->appId    = $appId;
		$this->clientId = $clientId;
		$this->appKey   = $appKey;

		// Viene caricato l'orario dell'ultimo salvtaggio del TOKEN

		$token_id_time = get_option( 'thron_token_id_time' );

		// Se il TOKEN non e ancora stato salvato o se
		// e' passato piu' di un ora dall'ultimo salvataggio
		// allora aggiorna il TOKEN

		if ( ( ! $token_id_time or ( ( time() - $token_id_time ) > 3600 ) ) ) {
			$this->request_token_id();
		} else {
			$this->token_id = get_option( 'thron_token_id' );
		}
	}

	public function search( $term = '', $categories = null, $tagList = null, $mime_type = null, $per_page = null, $pageToken = null ) {
		if ( ! $this->check_params() ) {
			return;
		}

		/**
		 * @todo da rifare completamente creando un oggetto e trasformandolo in JSON successivamente!!!!!!!!
		 */

		$endpoint['search'] = "https://{$this->clientId}-view.thron.com/api/xcontents/resources/content/search/{$this->clientId}";

		$criteria_lemma      = '';
		$criteria_categories = '';
		$criteria_tag        = '';

		if ( $term ) {
			$criteria_lemma = '
				"lemma": {
					"text": "' . $term . '",
					"textMatch": "any_word_match"
				}';

		}

		if ( $categories and ! is_array( $categories ) ) {
			$categories = array( $categories );
		}
		if ( is_array( $categories ) and count( $categories ) ) {
			$criteria_categories .= '
			"linkedCategories": {
		      "haveAtLeastOne": [
		      ';
			foreach ( $categories as $category ) {
				$criteria_categories .= '{
		          "cascade": false,
		          "id": "' . $category . '"
		        },';
			}
			$criteria_categories = trim( $criteria_categories, ',' );
			$criteria_categories .= ']}';
		}

		$criteria_tag .= '"itag": {"haveAll": [';
		if ( $tagList != '' ) {
			$tags = explode( ',', $tagList );

			foreach ( $tags as $tag ) {

				list ( $classification, $tag ) = explode( ';', $tag );

				if ( $tag != '' ) {
					$criteria_tag .= '
					{
		                "cascade": true,
			            "classificationId": "' . $classification . '",
			            "id": "' . $tag . '"
			        },';
				}
			}
			$criteria_tag = trim( $criteria_tag, ',' );
		}
		$criteria_tag .= ']}';


		$serch = null;

		if ( $criteria_lemma ) {
			{
				$serch .= $criteria_lemma;
			}
		}
		if ( $criteria_categories ) {
			{
				$serch .= $serch ? ',' : '';
				$serch .= $criteria_categories;
			}
		}
		if ( $criteria_tag ) {
			{
				$serch .= $serch ? ',' : '';
				$serch .= $criteria_tag;
			}
		}

		if ( $mime_type ) {
			{
				$serch .= $serch ? ',' : '';

				switch ( $mime_type ) {
					case 'video':
						$serch .= '"contentType": ["VIDEO"]';
						break;
					case 'image':
						$serch .= '"contentType": ["IMAGE"]';
						break;
					case 'audio':
						$serch .= '"contentType": ["AUDIO"]';
						break;
					default:
						$serch .= '"contentType": ["OTHER"]';
						break;
				}
			}
		}

		$pageTokenBody = $pageToken ? '"pageToken": "' . $pageToken . '",' : '';

		$response = wp_remote_post( $endpoint['search'], array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        =>
					'{
						"criteria": {' . $serch . '},
						' . $pageTokenBody . '
						"responseOptions": {
						    "resultsPageSize": ' . $per_page . ',
							"thumbsOptions": [
							    {
							        "divArea": "150x150"
							    }
						    ],
						    "returnDetailsFields": [
						        "locales", "source", "availableChannels"
						    ]
					    },
						"clientId": "' . $this->clientId . '"
					}',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = json_decode( $response['body'] );

			

			return $result;
		}
	}

	public function get_content_detail( $id ) {
		if ( ! $this->check_params() ) {
			return;
		}

		$endpoint['search'] = "https://{$this->clientId}-view.thron.com/api/xcontents/resources/content/detail?clientId={$this->clientId}&contentId={$id}&returnThumbUrl=true&=true";

		$response = wp_remote_get( $endpoint['search'], array(
				'method'      => 'GET',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        => '',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = json_decode( $response['body'] );

			return $result;
		}
	}

	public function get_folder( $offset = 0, $search = '', $rootFolder = null ) {
		if ( ! $this->check_params() ) {
			return;
		}


		$endpoint['search'] = "https://hub-view.thron.com/api/xcontents/resources/category/findByProperties2";

		$childOf = $rootFolder ? '"childOf": "' . $rootFolder . '",' : '';

		$response = wp_remote_get( $endpoint['search'], array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        => '
				{
					  "categoryFieldOption": {
					        "returnTotalResults": true
					  },
					  "offset": ' . $offset . ',
					  "properties": {
					        ' . $childOf . '
					        "textSearch": "' . $search . '"
					  },
					  "client": {
					        "clientId": "' . $this->clientId . '"
				      }
				}
				',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = json_decode( $response['body'] );

			return $result;

		}
	}

	function getListItag( $textSearchTHRON = '', $classificationID = null, $ids = '' ) {
		if ( ! $this->check_params() ) {
			return;
		}

		$all_language = get_available_languages();

		$lang = 'EN,';
		foreach ($all_language as $language) {
			$lang .= strtoupper(substr($language, 0, 2)) . ',';
		}

		$numberOfresultsTHRON = 50;
		$offsetTHRON          = 0;

		$response = array();

		$header = array(
			'X-TOKENID: ' . $this->token_id,
			"Content-Type: application/json"
		);

		if ( $ids ) {
			$endpoint['search'] = "https://" . $this->clientId . "-view.thron.com/api/xintelligence/resources/itagdefinition/listGet/" . $this->clientId . "/" . $classificationID;
			$endpoint['search'] .= "?limit=" . $numberOfresultsTHRON . "&";
			$endpoint['search'] .= "ids=" . $ids . "&";
			$endpoint['search'] .= "offset=" . $offsetTHRON . "&";
			$endpoint['search'] .= "text=" . urlencode( $textSearchTHRON ) . '&';
			$endpoint['search'] .= 'showSubNodeIds=true';

			$response = wp_remote_get( $endpoint['search'], array(
					'method'      => 'GET',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
					'body'        => '',
					'cookies'     => array()
				)
			);

			if ( is_wp_error( $response ) ) {
				thron_write_log($response->get_error_message);
			} else {
				$result = json_decode( $response['body'] );

				return $result->items;

			}

		} else {

			$classifications = $this->classification_list();

			$return = array();

			foreach ( $classifications as $classification ) {
				$endpoint['search'] = "https://" . $this->clientId . "-view.thron.com/api/xintelligence/resources/itagdefinition/listGet/" . $this->clientId . "/" . $classification['id'];
				$endpoint['search'] .= "?limit=" . $numberOfresultsTHRON . "&";
				$endpoint['search'] .= "offset=" . $offsetTHRON . "&";
				$endpoint['search'] .= "text=" . urlencode( $textSearchTHRON ) . '&';
				$endpoint['search'] .= 'lang=' . $lang . '&';
				$endpoint['search'] .= 'showSubNodeIds=true&';
				$endpoint['search'] .= 'lang=' . $lang . '&';
				$endpoint['search'] .= 'showSubNodeIds=true';

				$response = wp_remote_get( $endpoint['search'], array(
						'method'      => 'GET',
						'timeout'     => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
						'body'        => '',
						'cookies'     => array()
					)
				);

				if ( is_wp_error( $response ) ) {
					thron_write_log($response->get_error_message);
				} else {
					$result = json_decode( $response['body'] );
					$return[ $classification['classificationType'] . ';' . $classification['id'] ] = $result->items;

				}
			}

			return $return;
		}
	}

	/**
	 * Return list of template
	 *
	 * @param $classificationID
	 * @param $tagID
	 *
	 * @return mixed
	 */
	public function getTemplateList($limit, $offset) {
		if ( ! $this->check_params() ) {
			return;
		}

		$endpoint['temolateList'] = "https://{$this->clientId}-view.thron.com/api/xcontents/resources/playerembedtemplate/list/{$this->clientId}";

		$response = wp_remote_get( $endpoint['temolateList'], array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        => '
				{
                       "criteria": {
                            "text": ""
                       },
                            "limit": '.$limit.',
                            "offset": '.$offset.',
                            "options": {
                                "returnRoles": false,
                                "returnValues": true
                            }
                       }
				}
				',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = json_decode( $response['body'] );
			return $result;
		}
	}

	/**
	 * Restituisce i dettagli di un tag
	 *
	 * @param $classificationID
	 * @param $tagID
	 *
	 * @return mixed
	 */
	public function getTagByID( $classificationID, $tagID ) {
		if ( ! $this->check_params() ) {
			return;
		}

		$endpoint['tagDetail'] = "https://{$this->clientId}-view.thron.com/api/xintelligence/resources/itagdefinition/detail/{$this->clientId}/$classificationID/$tagID?showSubNodeIds=true";

		$response = wp_remote_get( $endpoint['tagDetail'], array(
				'method'      => 'GET',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        => '',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = json_decode( $response['body'] );

			return $result->item;
		}
	}

	/**
	 * Restituisce i dettagli di un tag
	 *
	 * @param $classificationID
	 * @param $tagID
	 *
	 * @return mixed
	 */
	public function folderNameByID( $folderID ) {
		if ( ! $this->check_params() ) {
			return;
		}

		$endpoint['folderDetail'] = "https://{$this->clientId}-view.thron.com/api/xcontents/resources/category/getCategory?clientId={$this->clientId}&categoryId=$folderID";

		$response = wp_remote_get( $endpoint['folderDetail'], array(
				'method'      => 'GET',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        => '',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = json_decode( $response['body'] );

			return $result->category;
		}
	}

	private function classification_list() {
		$endpoint['loginApp'] = "https://{$this->clientId}-view.thron.com/api/xintelligence/resources/classification/list/{$this->clientId}";

		$response = wp_remote_get( $endpoint['loginApp'], array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        => '
				{
				  "criteria": {}
				}
				',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = array();

			$classifications = json_decode( $response['body'] );

			foreach ( $classifications->items as $classifiction ) {
				$result[] = array(
					'id'                 => $classifiction->id,
					'classificationType' => $classifiction->classificationType
				);
			}

			return $result;

		}
	}

	public function sync_list($start, $nextPage = null) {
		if ( ! $this->check_params() ) {
			return;
		}

		$yesterday =  date("Y-m-d\TH:i:s.vO", $start);
		$today =  date("Y-m-d\TH:i:s.vO");

		$folderID = get_option( 'thron_rootCategoryId' );

		$endpoint['loginApp'] = "https://{$this->clientId}-view.thron.com/api/xcontents/resources/sync/updatedContent/{$this->clientId}";

		$response = wp_remote_get( $endpoint['loginApp'], array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'X-TOKENID' => $this->token_id, 'Content-Type' => 'application/json' ),
				'body'        => '
				{
					"criteria": {
					    "fromDate": "'.$yesterday.'",
					    "toDate": "'.$today.'",
					    
					    "linkedCategoryOp": {
						      "cascade": true,
						      "linkedCategoryIds": [
						           "' . $folderID . '"
						      ]
					    }
				    },
				    "options": {
					    "returnDeliveryInfo": true,
					    "returnImetadata": true,
					    "returnItags": true,
					    "returnLinkedCategories": true,
					    "returnVersionInfo": true
				    },
				    "nextPage": "'.$nextPage.'"
				}
				',
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {

			$sync_list = json_decode( $response['body'] );

			return $sync_list;
		}
	}

	private function request_token_id() {
		if ( ! $this->check_params() ) {
			return;
		}

		$endpoint['loginApp'] = "https://{$this->clientId}-view.thron.com/api/xadmin/resources/apps/loginApp/{$this->clientId}";

		$response = wp_remote_post( $endpoint['loginApp'], array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => array( 'appId' => $this->appId, 'appKey' => $this->appKey ),
				'cookies'     => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			thron_write_log($response->get_error_message);
		} else {
			$result = json_decode( $response['body'] );

			$this->token_id = $result->appUserTokenId;

			$pkey             = null;
			$tracking_context = null;
			$playerTemplates  = null;

			$metadata = $result->app->metadata;

			if ( is_array( $metadata ) and ( count( $metadata ) > 0 ) ) {
				foreach ( $metadata as $data ) {


					if ( 'pkey' == $data->name ) {
						$pkey = $data->value;
					}

					if ( 'playerTemplates' == $data->name ) {
						$object          = json_decode( $data->value );
						$playerTemplates = $object->default;
					}

					if ( 'tracking_context' == $data->name ) {
						$tracking_context = $data->value;
					}
				}
			}

			// Salva il token ID
			update_option( 'thron_token_id', $this->token_id );

			// Salva l'orario del salvataggio del token ID
			update_option( 'thron_token_id_time', time() );

			// Salva la pkey
			update_option( 'thron_pkey', $pkey );

			// Salva il tracking context
			update_option( 'thron_tracking_context', $tracking_context );

			// Salva il player template di default
			update_option( 'thron_playerTemplates', $playerTemplates );

			// Salva la cartella radice
			update_option( 'thron_rootCategoryId', $result->app->rootCategoryId );
		}
	}

	private function check_params() {
		if (
			( $this->clientId == '' ) or
			( $this->appId == '' ) or
			( $this->appKey == '' )
		) {
			return false;
		}

		return true;
	}
}

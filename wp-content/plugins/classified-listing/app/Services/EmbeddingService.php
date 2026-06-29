<?php

namespace Rtcl\Services;

use Rtcl\Helpers\Functions;
use Rtcl\Models\EmbeddingModel;
use Rtcl\Services\AIServices\AIClients\GeminiClient;
use Rtcl\Services\AIServices\AIClients\OpenAIClient;

class EmbeddingService {

	protected $client;

	/**
	 * @throws \Exception
	 */
	public function __construct() {
		$aiType = Functions::get_ai_client();

		if ( 'openai' === $aiType ) {
			$this->client = new OpenAIClient();
		} elseif ( 'gemini' === $aiType ) {
			$this->client = new GeminiClient();
		} else {
			throw new \Exception( 'AI type not supported' );
		}
	}

	/**
	 * Generate and save an embedding for a listing
	 */
	public function generate_and_store( $listing_id, $title, $content ) {
		$text      = $title . ' ' . wp_strip_all_tags( $content );
		$embedding = $this->client->generateEmbedding( $text );

		if ( $embedding ) {
			$info = [
				'word_count' => str_word_count( $text ),
				'source'     => 'listing',
			];

			return EmbeddingModel::upsert( $listing_id, $title, $embedding, $info );
		}

		return false;
	}

	/**
	 * Perform semantic search using cosine similarity
	 */
	public function search( $query, $limit = 0, $action = '' ) {
		$query_embedding = $this->client->generateEmbedding( $query );
		if ( ! $query_embedding ) {
			return [];
		}

		$rows = EmbeddingModel::get_all();

		$results = ! empty( $rows ) ? $this->find_similar( $query_embedding, $rows, $limit, $action ) : [];

		return wp_list_pluck( $results, 'post_id' );
	}

	/**
	 * @param $embedding
	 * @param $rows
	 * @param $limit
	 *
	 * @return array
	 */
	public function find_similar( $embedding, $rows, $limit, $action = '' ) {
		$minimum_match = 'best_match' === $action ? Functions::get_embedding_best_matching() : Functions::get_embedding_minimum_accuracy();

		$scored = [];
		foreach ( $rows as $row ) {
			$vector = json_decode( $row['embedding'], true );
			if ( is_array( $vector ) ) {
				$score = $this->cosine_similarity( $embedding, $vector );
				if ( $score >= $minimum_match ) {
					$scored[] = [ 'post_id' => $row['listing_id'], 'score' => $score ];
				}
			}
		}

		usort( $scored, fn( $a, $b ) => $b['score'] <=> $a['score'] );

		return ( intval( $limit ) < 1 ) ? $scored : array_slice( $scored, 0, $limit );
	}

	/**
	 * @param $vecA
	 * @param $vecB
	 *
	 * @return float|int
	 */
	protected function cosine_similarity( $vecA, $vecB ) {
		$dot   = $normA = $normB = 0.0;
		$count = min( count( $vecA ), count( $vecB ) );
		for ( $i = 0; $i < $count; $i ++ ) {
			$dot   += $vecA[ $i ] * $vecB[ $i ];
			$normA += $vecA[ $i ] ** 2;
			$normB += $vecB[ $i ] ** 2;
		}

		return $dot / ( sqrt( $normA ) * sqrt( $normB ) );
	}
}
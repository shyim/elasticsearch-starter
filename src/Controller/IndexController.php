<?php

namespace App\Controller;

use OpenSearch\Client;
use OpenSearchDSL\Aggregation\Bucketing\TermsAggregation;
use OpenSearchDSL\Query\FullText\MatchQuery;
use OpenSearchDSL\Query\TermLevel\TermQuery;
use OpenSearchDSL\Query\TermLevel\TermsQuery;
use OpenSearchDSL\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    public function __construct(private Client $client)
    {
    }

    #[Route('/')]
    public function index(): Response
    {
        return $this->render('search.html.twig');
    }

    #[Route('/result')]
    public function results(Request $request): JsonResponse
    {
        $term = $request->request->get('term', '');

        $facets = $request->request->all();
        unset($facets['term']);


        $search = new Search();
        $search->addQuery(new MatchQuery('title', $term));

        if (!empty($facets)) {
            $search->addPostFilter(new TermsQuery('genres', array_keys($facets)));
        }

        $search->addAggregation(new TermsAggregation('genres', 'genres'));

        $searchResult = $this->client->search([
            'index' => 'movies',
            'body' => $search->toArray()
        ]);

        $result = [
            'facets' => [
                [
                    'name' => 'genres',
                    'terms' => [],
                ],
            ],
        ];

        foreach ($searchResult['hits']['hits'] as $item) {
            $result['results'][] = $item['_source'];
        }

        foreach ($searchResult['aggregations']['genres']['buckets'] as $item) {
            $result['facets'][0]['terms'][] = [
                'term' => $item['key'],
                'count' => $item['doc_count']
            ];
        }

        return new JsonResponse($result);
    }
}
<?php

namespace App\Controller;

use OpenSearch\Client;
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

        // @todo: Search the Elasticsearch index for the term and facets genre

        $result = [
            'facets' => [
                [
                    'name' => 'genre',
                    'terms' => [
                        [
                            'term' => 'action',
                            'count' => 1
                        ],
                        [
                            'term' => 'comedy',
                            'count' => 1
                        ],
                        [
                            'term' => 'drama',
                            'count' => 1
                        ],
                        [
                            'term' => 'horror',
                            'count' => 1
                        ],
                        [
                            'term' => 'thriller',
                            'count' => 1
                        ]
                    ]
                ]
            ],
            'results' => [
                [
                    'id' => '1',
                    'title' => 'Ariel',
                    'overview' => 'Taisto Kasurinen is a Finnish coal miner whose father has just committed suicide and who is framed for a crime he did',
                ],
            ]
        ];

        return new JsonResponse($result);
    }
}
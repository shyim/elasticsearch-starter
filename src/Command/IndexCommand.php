<?php

namespace App\Command;

use OpenSearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand("es:index")]
class IndexCommand extends Command
{
    public static $defaultName = 'es:index';
    public static $defaultDescription = "Indexes the Elasticsearch index";
    private Client $client;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $movies = json_decode(file_get_contents('movies.json'), true, 512, JSON_THROW_ON_ERROR);

        try {
            $this->client->indices()->delete(['index' => 'movies']);
        } catch (\Throwable $e) {

        }

        $this->client->indices()->create([
            'index' => 'movies',
        ]);

        $this->client->indices()->putMapping([
            'index' => 'movies',
            'body' => [
                'properties' => [
                    'genres' => [
                        'type' => 'keyword'
                    ],
                ]
            ],
        ]);

        $documents = [];

        foreach ($movies as $movie) {
            $documents[] = ['index' => ['_id' => $movie['id']]];
            $documents[] = $movie;
        }

        $this->client->bulk([
            'index' => 'movies',
            'body' => $documents
        ]);

        return self::SUCCESS;
    }
}
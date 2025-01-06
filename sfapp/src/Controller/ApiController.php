<?php

namespace App\Controller;

use App\Entity\Measure;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Sa;
use Doctrine\ORM\EntityManagerInterface;

class ApiController extends AbstractController
{
    private $client;
    private $logger;
    private $entityManager;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/obtenir-donnees', name: 'api_obtenir_donnees')]
    public function getDatasFromApi(): Response
    {
        $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures?page=1';

        $dbname = getenv('API_DBNAME');
        $username = getenv('API_USERNAME');
        $userpass = getenv('API_USERPASS');

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'accept' => 'application/json',
                    'dbname' => "sae34bdm1eq3",
                    'username' => "m1eq3",
                    'userpass' => "wewnUw-zetwo7-mognov",
                ],

            ]);

            $statusCode = $response->getStatusCode();
            $this->logger->info('API Response Status Code: ' . $statusCode);

            $data = $response->toArray();
            $this->logger->info('API Response Data: ' . json_encode($data));

            $this->storeDataInDatabase($data);

            return new Response('Données récupérées et stockées avec succès');
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des données: ' . $e->getMessage());
            return new Response('Erreur lors de la récupération des données: ' . $e->getMessage(), 500);
        } catch (TransportExceptionInterface $e) {
        } catch (ClientExceptionInterface $e) {
        } catch (DecodingExceptionInterface $e) {
        } catch (RedirectionExceptionInterface $e) {
        } catch (ServerExceptionInterface $e) {
        }

        return new Response('Données non récupérées.');
    }

    private function storeDataInDatabase(array $data)
    {
        foreach ($data as $item) {
            // Trouver ou créer l'entité 'Sa'
            $sa = $this->entityManager->getRepository(SA::class)->findOneBy(['name' => $item['nomsa']]);

            if (!$sa) {
                $sa = new SA();
                $sa->setName($item['nomsa']);
                $sa->setState(SAState::Installed);
                $sa->setTemperature(null);  // Par défaut à null
                $sa->setHumidity(null);
                $sa->setLum(null);
                $sa->setPres(null);
                $sa->setCO2(null);
                $this->entityManager->persist($sa);
            }

            // Log de l'élément reçu
            $this->logger->info('Traitement de l\'élément : ' . json_encode($item));

            // Vérification de la validité des données avant insertion
            if (isset($item['valeur']) && $item['valeur'] !== null) {
                $measure = null;

                if ($item['nom'] === 'temp') {
                    $measure = new Measure($item['id'], $item['valeur'], $item['nom'], new \DateTime($item['dateCapture']), $sa);
                } elseif ($item['nom'] === 'hum') {
                    $measure = new Measure($item['id'], $item['valeur'], $item['nom'], new \DateTime($item['dateCapture']), $sa);
                } elseif ($item['nom'] === 'co2') {
                    $measure = new Measure($item['id'], $item['valeur'], $item['nom'], new \DateTime($item['dateCapture']), $sa);
                } elseif ($item['nom'] === 'lum') {
                    $measure = new Measure($item['id'], $item['valeur'], $item['nom'], new \DateTime($item['dateCapture']), $sa);
                } elseif ($item['nom'] === 'pres') {
                    $measure = new Measure($item['id'], $item['valeur'], $item['nom'], new \DateTime($item['dateCapture']), $sa);
                }

                if ($measure) {
                    $this->logger->info('Création de la mesure avec la valeur : ' . $measure->getValue());

                    $this->entityManager->persist($measure);
                    $sa->addMeasure($measure);
                    $this->entityManager->persist($sa);
                }
            } else {
                $this->logger->warning('Valeur manquante ou nulle pour ' . $item['nom']);
            }
        }

        $this->entityManager->flush();
    }
}

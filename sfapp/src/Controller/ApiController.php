<?php

namespace App\Controller;

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
use App\Entity\SA; // Assurez-vous d'importer votre entité

class ApiController extends AbstractController
{
    private $client;
    private $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    #[Route('/api/obtenir-donnees', name: 'api_obtenir_donnees')]
    public function getDatasFromApi(): Response
    {
        $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures?page=1';

        $dbname = getenv('API_DBNAME');
        $username = getenv('API_USERNAME');
        $userpass = getenv('API_USERPASS');
        // $encryptionKey = getenv('API_ENCRYPTION_KEY');

        // Déchiffrer le mot de passe
        // $userpass = $this->decryptValue($encryptedUserpass, $encryptionKey);

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'accept' => 'application/json',
                    'dbname' => $dbname,
                    'username' => $username,
                    'userpass' => $userpass,
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
    }

    private function decryptValue($encryptedValue, $key)
    {
        $method = 'aes-256-cbc';
        $decoded = base64_decode($encryptedValue);

        if ($decoded === false) {
            throw new \Exception('Failed to decode the encrypted value.');
        }

        $parts = explode('::', $decoded, 2);

        if (count($parts) !== 2) {
            throw new \Exception('Invalid encrypted value format.');
        }

        list($encryptedData, $iv) = $parts;

        $decrypted = openssl_decrypt($encryptedData, $method, $key, 0, $iv);

        if ($decrypted === false) {
            throw new \Exception('Failed to decrypt the value.');
        }

        return $decrypted;
    }

    private function storeDataInDatabase(array $data)
    {
        $entityManager = $this->getDoctrine()->getManager();

        foreach ($data as $item) {
            $sa = new SA();
            $sa->setTemperature($item['temperature']);
            $sa->setHumidity($item['humidity']);

            $entityManager->persist($sa);
        }

        $entityManager->flush();
    }
}

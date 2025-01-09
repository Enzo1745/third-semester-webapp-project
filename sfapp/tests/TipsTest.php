<?php

namespace App\Tests;
use App\Repository\TipsRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TipsTest extends WebTestCase
{
	public function TipsComsummer(): void
	{
		$client = static::createClient();
		$container = $client->getContainer();

		// mock repository to force a return
		$mockRepo = $this->createMock(TipsRepository::class);
		$mockRepo->method('findRandTips')->willReturn('Un tip simulé pour le test.');

		$container->set('App\Repository\TipsRepository', $mockRepo);


		$crawler = $client->request('GET', '/');

		$this->assertResponseIsSuccessful();
		$tipsText = $crawler->filter('h2')->text(); // get texte
		$this->assertSelectorTextContains('h2', 'TIPS');
		$this->assertStringStartsWith('TIPS :', $tipsText);
		$this->assertNotEmpty(trim(str_replace('TIPS :', '', $tipsText)), 'Un tip est bien affiché.');
	}
}

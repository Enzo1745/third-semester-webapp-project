<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Process\Process;
class TestsController extends AbstractController
{
    #[Route('/tests', name: 'run_tests')]
    public function runTests(): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $phpunitCommand = sprintf(
            'php %s/bin/phpunit --no-logging --configuration=%s/phpunit.xml.dist --testdox %s/tests',
            $projectDir, // project folder
            $projectDir, // config xml
            $projectDir  // tests folder
        );



        $process = \Symfony\Component\Process\Process::fromShellCommandline($phpunitCommand);
        $process->run();

        // Vérification des erreurs
        if (!$process->isSuccessful()) {
            return new Response(
                '<pre>Erreur pendant l\'exécution des tests : '
                . htmlspecialchars($process->getErrorOutput())
                . "\nSortie standard :\n"
                . htmlspecialchars($process->getOutput())
                . '</pre>',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Retourner les résultats des tests
        return new Response('<pre>' . htmlspecialchars($process->getOutput()) . '</pre>');
    }
}

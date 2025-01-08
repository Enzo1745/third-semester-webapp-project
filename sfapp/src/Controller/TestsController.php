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
        // Rrecup racine repertory
        $projectDir = $this->getParameter('kernel.project_dir');
        $phpunitCommand = sprintf(
            'php %s/bin/phpunit --no-logging --configuration=%s/phpunit.xml.dist --testdox %s/tests',
            $projectDir,
            $projectDir,
            $projectDir
        );

        // make process
        $process = Process::fromShellCommandline($phpunitCommand);
        $process->run();

        // check errors
        if (!$process->isSuccessful()) {
            return new Response( //color red for error
                '<div style="font-family:monospace; padding:1rem; background:#ffecec; color:#d8000c; border:1px solid #d8000c;">
                    <h3>Erreur pendant l\'exécution des tests :</h3>
                    <pre>' . htmlspecialchars($process->getErrorOutput()) . '</pre>
                </div>',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        //get result into a variable
        $output = $process->getOutput();


        // Return test
        return new Response(
            '<div style="font-family:monospace; padding:1rem; background:#e8f5e9; color:#2e7d32; border:1px solid #2e7d32;">
                <h3>Résultats des tests :</h3>
                <pre>' . htmlspecialchars($output) . '</pre>
            </div>'
        );
    }
}

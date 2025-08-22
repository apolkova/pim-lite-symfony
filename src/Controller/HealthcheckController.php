<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HealthcheckController
{
    #[Route("/healthz", methods: ["GET"])]
    public function __invoke(): Response
    {
        return new Response("ok", 200, ["Content-Type" => "text/plain"]);
    }
}

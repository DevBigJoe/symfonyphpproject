<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

#[AsController]
final readonly class FehlerseiteController
{
    public function __construct(private Environment $twig) {}

    /* //OldOne
    public function show(Request $request, ?\Throwable $exception = null): Response
    {
        // Status-Code ermitteln
        $statusCode = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } elseif ($request->attributes->get('code')) {
            $statusCode = (int) $request->attributes->get('code');
        }

        // Template-Name bauen (404/403 -> spezielle Templates, ansonsten fallback)
        $template = sprintf('bundles/TwigBundle/Exception/error%s.html.twig', $statusCode);
        if (!file_exists($this->twig->getLoader()->getSourceContext($template)->getPath())) {
            // fallback
            $template = 'bundles/TwigBundle/Exception/error.html.twig';
        }

        return new Response($this->twig->render($template, [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Fehler',
            'exception' => $exception,
        ]), $statusCode);
    }
    */

    public function show(Request $request, null|\Throwable $exception = null): Response
{
    // Standardwert: 500 (Internal Server Error)
    $statusCode = 500;

    // Wenn Exception vorhanden → Statuscode extrahieren
    if ($exception instanceof HttpExceptionInterface) {
        $statusCode = $exception->getStatusCode();
    } elseif ($request->attributes->get('code')) {
        $statusCode = (int) $request->attributes->get('code');
    }

    // Nur für 403 und 404 spezielle Templates verwenden
    $specialTemplates = [403, 404];

    if (in_array($statusCode, $specialTemplates, true)) {
        $template = sprintf('bundles/TwigBundle/Exception/error%d.html.twig', $statusCode);
    } else {
        // Für alle anderen Fehler das generische Template verwenden
        $template = 'bundles/TwigBundle/Exception/error.html.twig';
    }

    // Rendern
    return new Response(
        $this->twig->render($template, [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Fehler',
            'exception' => $exception,
        ]),
        $statusCode
    );
}

    
}

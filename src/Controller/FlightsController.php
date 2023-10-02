<?php
namespace App\Controller;

use App\Manager\FlightsManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class FlightsController extends AbstractController
{
    /** @var FlightsManager */
    private $flightsManager;

    public function __construct(FlightsManager $flightsManager,) {
        $this->flightsManager = $flightsManager;
    }

    #[Route('/', name: 'flights')]
    public function flights(): Response
    {
        $flights = [
            '#1',
            '#12',
            '#123',
            '#1234',
            '#12345',
            '#123456',
            '#1234567',
            '#54321',
            '#321',
            '#321a',
            '#321b_________________',
        ];

        return $this->render('air.html.twig', [
            'flights' => $flights
        ]);
    }

    #[Route('/check_flights_ajax', name: 'check_flights_ajax')]
    public function checkFlightAjax(Request $request): JsonResponse
    {
        $airport = $request->request->get('airport');
        $flight = $request->request->get('flight');
        $delay = $request->request->getInt('delay');
        $fog = $request->request->getBoolean('fog');

        sleep(1);

        return new JsonResponse($this->flightsManager->checkFlight($airport, $flight, $delay, $fog));
    }

    #[Route('/airport_list_ajax', name: 'airport_list_ajax')]
    public function airportListAjax(Request $request): JsonResponse
    {
        $q = trim($request->request->get('term', ''));

        $airportsList = $this->flightsManager->getAirports($q);

        return new JsonResponse($airportsList);
    }
}

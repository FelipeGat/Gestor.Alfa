<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RotaController extends Controller
{
    protected string $tomtomApiKey = 'mG0UAzcg0C6K7PT0NgIzxqCY6L8Z6reG';
    protected string $tomtomHost = 'https://api.tomtom.com';
    protected string $osrmHost = 'http://router.project-osrm.org';

    public function consulta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origem_lat' => 'required|numeric|between:-90,90',
            'origem_lon' => 'required|numeric|between:-180,180',
            'destino_lat' => 'required|numeric|between:-90,90',
            'destino_lon' => 'required|numeric|between:-180,180',
        ]);

        $origemLat = (float) $validated['origem_lat'];
        $origemLon = (float) $validated['origem_lon'];
        $destinoLat = (float) $validated['destino_lat'];
        $destinoLon = (float) $validated['destino_lon'];
        
        $nocache = $request->query('nocache', false);

        $cacheKey = sprintf(
            'rota:v2:%.6f,%.6f:%.6f,%.6f',
            $origemLat, $origemLon, $destinoLat, $destinoLon
        );

        if (!$nocache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return response()->json($cached);
            }
        }

        $result = $this->calcularRotaTomTom($origemLat, $origemLon, $destinoLat, $destinoLon);

        if ($result['sucesso']) {
            Cache::put($cacheKey, $result['data'], now()->addMinutes(10));
            return response()->json($result['data']);
        }

        Log::warning('TomTom falhou, usando fallback OSRM', ['erro' => $result['erro']]);

        $fallbackResult = $this->calcularRotaFallback($origemLat, $origemLon, $destinoLat, $destinoLon);
        Cache::put($cacheKey, $fallbackResult, now()->addMinutes(10));
        
        return response()->json($fallbackResult);
    }

    protected function calcularRotaTomTom(float $origemLat, float $origemLon, float $destinoLat, float $destinoLon): array
    {
        $url = sprintf(
            '%s/routing/1/calculateRoute/%f,%f:%f,%f/json?key=%s&traffic=true&travelMode=car&routeType=fastest&departAt=now&maxAlternatives=0&instructionsType=text',
            $this->tomtomHost,
            $origemLat,
            $origemLon,
            $destinoLat,
            $destinoLon,
            $this->tomtomApiKey
        );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('TomTom cURL error', ['error' => $curlError]);
                return ['sucesso' => false, 'erro' => $curlError];
            }

            if ($httpCode !== 200) {
                $responseData = json_decode($response, true);
                $erroMsg = $responseData['detailedError']['message'] ?? 'HTTP Error';
                $erroCode = $responseData['detailedError']['code'] ?? 'HTTP_' . $httpCode;
                Log::error('TomTom HTTP error', ['code' => $httpCode, 'response' => $response]);
                return ['sucesso' => false, 'erro' => $erroMsg, 'codigo_erro' => $erroCode];
            }

            $data = json_decode($response, true);

            if (isset($data['detailedError'])) {
                $erroMsg = $data['detailedError']['message'] ?? 'Erro desconhecido';
                $erroCode = $data['detailedError']['code'] ?? '';
                Log::error('TomTom route error', [
                    'message' => $erroMsg,
                    'code' => $erroCode,
                    'origem' => "$origemLat,$origemLon",
                    'destino' => "$destinoLat,$destinoLon"
                ]);
                return ['sucesso' => false, 'erro' => $erroMsg, 'codigo_erro' => $erroCode];
            }

            if (isset($data['errorText']) || empty($data['routes'])) {
                return ['sucesso' => false, 'erro' => $data['errorText'] ?? 'Rota não encontrada', 'codigo_erro' => 'NO_ROUTE'];
            }

            $route = $data['routes'][0];
            $summary = $route['legs'][0]['summary'];
            $points = $route['legs'][0]['points'];

            $distanciaKm = round($summary['lengthInMeters'] / 1000, 2);
            $tempoTotalSegundos = $summary['travelTimeInSeconds'];
            $tempoTotalMinutos = round($tempoTotalSegundos / 60);
            $atrasoPorTrafegoMinutos = isset($summary['trafficDelayInSeconds']) 
                ? round($summary['trafficDelayInSeconds'] / 60) 
                : 0;

            $geometry = [];
            foreach ($points as $point) {
                $geometry[] = [
                    'lat' => $point['latitude'],
                    'lon' => $point['longitude']
                ];
            }

            return [
                'sucesso' => true,
                'data' => [
                    'origem' => ['lat' => $origemLat, 'lon' => $origemLon],
                    'destino' => ['lat' => $destinoLat, 'lon' => $destinoLon],
                    'distancia_km' => $distanciaKm,
                    'distancia' => $distanciaKm,
                    'tempo_total_minutos' => $tempoTotalMinutos,
                    'tempo' => $tempoTotalMinutos,
                    'atraso_por_trafego_minutos' => $atrasoPorTrafegoMinutos,
                    'provedor' => 'TomTom',
                    'nivel_confianca' => 'alta',
                    'geometry' => $geometry,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Erro na chamada TomTom', ['exception' => $e->getMessage()]);
            return ['sucesso' => false, 'erro' => $e->getMessage(), 'codigo_erro' => 'TOMTOM_EXCEPTION'];
        }
    }

    protected function calcularRotaFallback(float $origemLat, float $origemLon, float $destinoLat, float $destinoLon): array
    {
        $url = sprintf(
            '%s/route/v1/driving/%f,%f;%f,%f?overview=full&geometries=geojson',
            $this->osrmHost,
            $origemLon,
            $origemLat,
            $destinoLon,
            $destinoLat
        );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError || $httpCode !== 200) {
                return $this->respostaErro($origemLat, $origemLon, $destinoLat, $destinoLon, 'OSRM indisponível', 'OSRM_DOWN');
            }

            $data = json_decode($response, true);

            if ($data['code'] !== 'Ok' || empty($data['routes'])) {
                return $this->respostaErro($origemLat, $origemLon, $destinoLat, $destinoLon, 'Rota não encontrada', 'OSRM_NO_ROUTE');
            }

            $route = $data['routes'][0];
            $distanciaMeters = $route['distance'];
            $tempoSegundos = $route['duration'];
            
            $geometry = [];
            if (isset($route['geometry']['coordinates'])) {
                foreach ($route['geometry']['coordinates'] as $coord) {
                    $geometry[] = [
                        'lon' => $coord[0],
                        'lat' => $coord[1]
                    ];
                }
            }

            $distanciaKm = round($distanciaMeters / 1000, 2);
            $tempoTeoricoMinutos = round($tempoSegundos / 60);
            $multiplicador = $this->getMultiplicadorHorario();
            $tempoComTrafegoMinutos = round($tempoTeoricoMinutos * $multiplicador);
            $atrasoPorTrafegoMinutos = round($tempoComTrafegoMinutos - $tempoTeoricoMinutos);

            return [
                'origem' => ['lat' => $origemLat, 'lon' => $origemLon],
                'destino' => ['lat' => $destinoLat, 'lon' => $destinoLon],
                'distancia_km' => $distanciaKm,
                'distancia' => $distanciaKm,
                'tempo_total_minutos' => $tempoComTrafegoMinutos,
                'tempo' => $tempoComTrafegoMinutos,
                'atraso_por_trafego_minutos' => $atrasoPorTrafegoMinutos,
                'provedor' => 'Fallback - OSRM',
                'nivel_confianca' => 'media',
                'geometry' => $geometry,
            ];
        } catch (\Exception $e) {
            Log::error('Erro no fallback OSRM', ['exception' => $e->getMessage()]);
            return $this->respostaErro($origemLat, $origemLon, $destinoLat, $destinoLon, $e->getMessage(), 'OSRM_EXCEPTION');
        }
    }

    protected function getMultiplicadorHorario(): float
    {
        $hora = now()->setTimezone('America/Sao_Paulo')->format('H:i');

        if ($hora >= '07:00' && $hora <= '09:30') {
            return 1.8;
        }
        if ($hora >= '17:00' && $hora <= '19:30') {
            return 1.8;
        }
        if ($hora >= '10:00' && $hora <= '16:30') {
            return 1.4;
        }
        if ($hora >= '22:00' || $hora <= '06:00') {
            return 1.1;
        }

        return 1.2;
    }

    protected function respostaErro(float $origemLat, float $origemLon, float $destinoLat, float $destinoLon, string $erro, string $codigoErro = ''): array
    {
        Log::error('Erro ao calcular rota', [
            'erro' => $erro,
            'codigo' => $codigoErro,
            'origem' => "$origemLat,$origemLon",
            'destino' => "$destinoLat,$destinoLon"
        ]);

        return [
            'origem' => ['lat' => $origemLat, 'lon' => $origemLon],
            'destino' => ['lat' => $destinoLat, 'lon' => $destinoLon],
            'distancia_km' => 0,
            'distancia' => 0,
            'tempo_total_minutos' => 0,
            'tempo' => 0,
            'atraso_por_trafego_minutos' => 0,
            'provedor' => 'Erro',
            'nivel_confianca' => 'baixa',
            'erro' => $erro,
            'codigo_erro' => $codigoErro,
            'geometry' => [],
        ];
    }
}

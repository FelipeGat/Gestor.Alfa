<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeocodingService
{
    protected string $apiUrl = 'https://nominatim.openstreetmap.org/reverse';
    protected string $userAgent = 'GestorAlfa/1.0';

    /**
     * Realiza reverse geocoding de coordenadas para endereço
     *
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept-Language' => 'pt-BR',
            ])->get($this->apiUrl, [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->parseAddress($data);
            }

            Log::warning('Falha no reverse geocoding', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => $response->status(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Erro no reverse geocoding', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parseia a resposta da API Nominatim para o formato padronizado
     *
     * @param array $data
     * @return array|null
     */
    protected function parseAddress(array $data): ?array
    {
        if (!isset($data['address']) || !is_array($data['address'])) {
            return null;
        }

        $address = $data['address'];
        $displayName = $data['display_name'] ?? '';

        // Extrair componentes do endereço
        $logradouro = $this->extractLogradouro($address);
        $numero = $this->extractNumero($address, $data);
        $bairro = $address['neighbourhood'] ?? $address['suburb'] ?? $address['quarter'] ?? '';
        $cidade = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? '';
        
        // Estado: tentar state_code (BR) primeiro, depois state
        $estado = $address['state_code'] ?? $address['state'] ?? '';
        
        // Para Brasil, state_code vem como 'ES', 'SP', etc.
        // Se tiver apenas o nome completo, extrair sigla
        if (strlen($estado) > 2 && $address['country'] === 'Brasil') {
            $estadosMap = [
                'Espírito Santo' => 'ES',
                'São Paulo' => 'SP',
                'Rio de Janeiro' => 'RJ',
                'Minas Gerais' => 'MG',
                'Bahia' => 'BA',
                'Paraná' => 'PR',
                'Rio Grande do Sul' => 'RS',
                'Pernambuco' => 'PE',
                'Ceará' => 'CE',
                'Pará' => 'PA',
                'Santa Catarina' => 'SC',
                'Maranhão' => 'MA',
                'Goiás' => 'GO',
                'Amazonas' => 'AM',
                'Paraíba' => 'PB',
                'Rio Grande do Norte' => 'RN',
                'Mato Grosso' => 'MT',
                'Piauí' => 'PI',
                'Alagoas' => 'AL',
                'Mato Grosso do Sul' => 'MS',
                'Sergipe' => 'SE',
                'Rondônia' => 'RO',
                'Tocantins' => 'TO',
                'Acre' => 'AC',
                'Amapá' => 'AP',
                'Roraima' => 'RR',
                'Distrito Federal' => 'DF',
            ];
            $estado = $estadosMap[$estado] ?? $estado;
        }
        
        // Se ainda não tem estado e é Brasil, tentar inferir pela cidade
        if (empty($estado) && $address['country'] === 'Brasil') {
            $estado = $this->inferirEstadoBrasil($cidade);
        }
        
        $cep = $address['postcode'] ?? '';
        $pais = $address['country'] ?? '';

        // Formatar endereço completo
        // Formato diferente para Brasil e internacional
        if ($pais === 'Brasil') {
            $partes = array_filter([
                $logradouro,
                $numero,
                $bairro,
                $cidade,
                $estado,
                $cep,
            ]);

            $enderecoFormatado = !empty($partes) 
                ? implode(', ', [
                    trim("{$logradouro}, {$numero}"),
                    $bairro,
                    $cidade ? "{$cidade} - {$estado}" : $estado,
                    $cep,
                ])
                : $displayName;
        } else {
            // Formato internacional
            $partesInternacional = array_filter([
                $logradouro && $numero ? "{$logradouro}, {$numero}" : ($logradouro ?: $numero),
                $bairro,
                $cidade && $estado ? "{$cidade}, {$estado}" : ($cidade ?: $estado),
                $cep,
                $pais,
            ]);

            $enderecoFormatado = !empty($partesInternacional) 
                ? implode(', ', $partesInternacional)
                : $displayName;
        }

        return [
            'endereco_logradouro' => $logradouro,
            'endereco_numero' => $numero,
            'endereco_bairro' => $bairro,
            'endereco_cidade' => $cidade,
            'endereco_estado' => $estado,
            'endereco_cep' => $cep,
            'endereco_formatado' => $enderecoFormatado,
            'endereco_pais' => $pais,
        ];
    }

    /**
     * Extrai o logradouro do endereço
     */
    protected function extractLogradouro(array $address): string
    {
        return $address['road'] 
            ?? $address['pedestrian'] 
            ?? $address['footway'] 
            ?? $address['path'] 
            ?? $address['street'] 
            ?? '';
    }

    /**
     * Extrai o número do endereço
     */
    protected function extractNumero(array $address, array $data): string
    {
        // Tenta extrair do address
        if (isset($address['house_number'])) {
            return $address['house_number'];
        }

        // Tenta extrair do display_name - procura por números no início ou após vírgula
        $displayName = $data['display_name'] ?? '';
        if (preg_match('/([0-9]+[a-zA-Z]?[-\/]?[0-9]*[a-zA-Z]?)/', $displayName, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Infere o estado brasileiro pela cidade (para principais cidades)
     */
    protected function inferirEstadoBrasil(string $cidade): string
    {
        $cidadesMap = [
            // Espírito Santo
            'Vitória' => 'ES',
            'Vila Velha' => 'ES',
            'Serra' => 'ES',
            'Cariacica' => 'ES',
            'Cachoeiro de Itapemirim' => 'ES',
            
            // São Paulo
            'São Paulo' => 'SP',
            'Campinas' => 'SP',
            'Santos' => 'SP',
            'São José dos Campos' => 'SP',
            'Ribeirão Preto' => 'SP',
            
            // Rio de Janeiro
            'Rio de Janeiro' => 'RJ',
            'Niterói' => 'RJ',
            'São Gonçalo' => 'RJ',
            'Duque de Caxias' => 'RJ',
            
            // Minas Gerais
            'Belo Horizonte' => 'MG',
            'Uberlândia' => 'MG',
            'Contagem' => 'MG',
            'Juiz de Fora' => 'MG',
            
            // E assim por diante para principais cidades...
        ];

        return $cidadesMap[$cidade] ?? '';
    }
}

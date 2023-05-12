<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Nette\Utils\DateTime;

class NotasController extends Controller
{
    protected  $listaNotas;
    protected  $notasAgrupadas;


    /**
     * Função construtora que retorna uma lista de notas agrupadas por remetente
     */
    public function __construct()
    {
        $this->listaNotas = Http::get('http://homologacao3.azapfy.com.br/api/ps/notas')->json();
        $this->notasAgrupadas = array_reduce($this->listaNotas, function ($notas, $nota) {
            $nomeRementente = $nota['nome_remete'];
            if (!array_key_exists($nomeRementente, $notas)) {
                $notas[$nomeRementente] = array();
            }
            $notas[$nomeRementente][] = $nota;
            return $notas;
        }, array());
    }

    /**
     * Retorna um JSON response com a lista de notas agrupadas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listaNotas()
    {
        $response = [
            'notas' => $this->notasAgrupadas,
        ];

        return response()->json($response, 200);
    }

    /**
     * Calcula o valor total das notas agrupadas, filtrando-as pelo dia e hora de emissão e 
     * entrega que devem ter uma diferença de exatamente 2 dias. Então retorna um JSON com 
     * o valor total, agrupado por rementente.
     * @return \Illuminate\Http\JsonResponse
     */

    public function calculaTotal()
    {
        $resultado = array();
        foreach ($this->notasAgrupadas as $remetente => $notas) {
            $valorTotal = array_reduce($notas, function ($total, $nota) {
                $dtEmis = DateTime::createFromFormat('d/m/Y H:i:s', $nota['dt_emis']);
                if (isset($nota['dt_entrega'])) {
                    $dtEntrega = DateTime::createFromFormat('d/m/Y H:i:s', $nota['dt_entrega']);
                    $dif = $dtEntrega->diff($dtEmis);
                    $difDias = $dif->d;
                } else {
                    $difDias = 0;
                    $dif = $dtEmis->diff($dtEmis);
                }

                if ($difDias == 2 && $dif->h > 0) {
                    return $total;
                }

                if ($difDias <= 2)
                    return $total + floatval($nota['valor']);
            }, 0);

            $valorFormatado = number_format($valorTotal, 2, ',', '.');
            $resultado[$remetente] = $valorFormatado;
        }

        $response = [
            'valoresNotas' => $resultado,
        ];

        return response()->json($response, 200);
    }

    /**
     * Calcula o valor total das notas que foram entregues, ou seja, possuem uma data de 
     * entrega, agrupando-o por remetente
     * @return \Illuminate\Http\JsonResponse
     */

    public function calculaValorEntregue()
    {
        $resultado = array();
        foreach ($this->notasAgrupadas as $remetente => $notas) {
            $valorTotal = array_reduce($notas, function ($total, $nota) {
                if (!isset($nota['dt_entrega'])) {
                    return $total;
                } else {
                    $dtEmis = DateTime::createFromFormat('d/m/Y H:i:s', $nota['dt_emis']);
                    $dtEntrega = DateTime::createFromFormat('d/m/Y H:i:s', $nota['dt_entrega']);
                    $dif = $dtEntrega->diff($dtEmis);
                    $difDias = $dif->d;
                }

                if ($difDias == 2 && $dif->h > 0) {
                    return $total;
                }

                if ($difDias <= 2)
                    return $total + floatval($nota['valor']);
            }, 0);

            $valorFormatado = number_format($valorTotal, 2, ',', '.');
            $resultado[$remetente] = $valorFormatado;
        }

        $response = [
            'valoresNotasEntregues' => $resultado,
        ];

        return response()->json($response, 200);
    }

    /**
     * Calcula o valor total das notas que não foram entregues, ou seja, não possuem uma 
     * data de entrega, agrupando-o por remetente.
     * @return \Illuminate\Http\JsonResponse
     */

    public function calculaValorNaoEntregue()
    {
        $resultado = array();

        foreach ($this->notasAgrupadas as $remetente => $notas) {
            $total = 0;
            foreach ($notas as $nota) {
                if ($nota['status'] == 'ABERTO') {
                    $total += floatval($nota['valor']);
                }
            }
            $resultado[$remetente] = $total;
        }

        $response = [
            'valoresNotasNaoEntregues' => $resultado,
        ];

        return response()->json($response, 200);
    }

    /**
     * Calcula o valor total perdido devido ao atraso na entrega, ou seja, as notas que 
     * foram entregues em um prazo superior à 2 dias, agrupando-o por remetente
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculaNaoRecebido()
    {
        $resultado = array();
        foreach ($this->notasAgrupadas as $remetente => $notas) {
            $valorTotal = array_reduce($notas, function ($total, $nota) {
                if (!isset($nota['dt_entrega'])) {
                    return $total;
                } else {
                    $dtEmis = DateTime::createFromFormat('d/m/Y H:i:s', $nota['dt_emis']);
                    $dtEntrega = DateTime::createFromFormat('d/m/Y H:i:s', $nota['dt_entrega']);
                    $dif = $dtEntrega->diff($dtEmis);
                    $difDias = $dif->d;
                }

                if ($difDias == 2 && $dif->h == 0) {
                    return $total;
                }

                if ($difDias >= 2)
                    return $total + floatval($nota['valor']);
            }, 0);

            $valorFormatado = number_format($valorTotal, 2, ',', '.');
            $resultado[$remetente] = $valorFormatado;
        }

        $response = [
            'valoresNotasNaoRecebidos' => $resultado,
        ];

        return response()->json($response, 200);
    }
}

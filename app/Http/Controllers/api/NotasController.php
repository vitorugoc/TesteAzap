<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Nette\Utils\DateTime;

class NotasController extends Controller
{
    protected $listaNotas;
    protected $notasAgrupadas;

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

    public function listaNotas()
    {
        $response = [
            'notas' => $this->notasAgrupadas,
        ];

        return response()->json($response, 200);
    }

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

    public function calculaValorNaoEntregue()
    {
        $resultado = array();

        foreach ($this->notasAgrupadas as $empresa => $notas) {
            $total = 0;
            foreach ($notas as $nota) {
                if ($nota['status'] == 'ABERTO') {
                    $total += floatval($nota['valor']);
                }
            }
            $resultado[$empresa] = $total;
        }

        $response = [
            'valoresNotasNaoEntregues' => $resultado,
        ];

        return response()->json($response, 200);
    }

    public function calculaNaoRecebido()
    {
    }
}

<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class NotasController extends Controller
{
    public $listaNotas;
    public $notasAgrupadas;

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
        return $this->notasAgrupadas;
    }

    public function calculaTotal()
    {
        $resultado = array();
        foreach ($this->notasAgrupadas as $remetente => $notas) {
            $valorTotal = array_reduce($notas, function ($total, $nota) {
                return $total + floatval($nota['valor']);
            }, 0);
            $valorFormatado = number_format($valorTotal, 2, ',', '.');
            $resultado[$remetente] = $valorFormatado;
        }
        return $resultado;
    }
}

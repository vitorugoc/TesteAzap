<?php


use Tests\TestCase;


class NotasControllerTest extends TestCase
{
    protected $notasController;


    public function testListaNotas()
    {
        $response = $this->get('api/notas/');
        $response->assertStatus(200);
    }

    public function testCalculaTotal()
    {
        $response = $this->get('api/notas/valor');
        $response->assertStatus(200);
    }

    public function  testCalculaValorEntregue()
    {
        $response = $this->get('api/notas/valor/entregue');
        $response->assertStatus(200);
    }

    public function testCalculaValorNaoEntregue()
    {
        $response = $this->get('api/notas/valor/nao_entregue');
        $response->assertStatus(200);
    }

    public function testCalculaNaoRecebido()
    {
        $response = $this->get('api/notas/valor/nao_recebido');
        $response->assertStatus(200);
    }
}

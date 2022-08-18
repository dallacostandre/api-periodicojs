<?php

namespace App\Http\Controllers;

use App\Models\BuscaRevista;
use App\Models\Submissao;
use App\Models\BuscaArtigo;
use App\Models\BuscaArtAno;
use App\Models\Autor;
use App\Models\ConfiguracaoAutor;
use Illuminate\Support\Facades\DB;
use App\Models\Pvocabily;
use App\Models\Psetings;
use App\Models\Pdados;

use Illuminate\Http\Request;

class BuscaRevistaController extends Controller
{
    public function buscarevista(Request $request)
    {
        if ($request) {
            $idpublicacao = $request->id;
            // $buscasubissao = Submissao::orderBy('current_publication_id','DESC')->where('current_publication_id',$idpublicacao)->get();
            $busca = BuscaArtigo::orderBy('issue_id', 'DESC')->where('issue_id', $idpublicacao)->get()->toArray();
            $buscaAnoPublicacao = BuscaArtAno::orderBy('issue_id', 'DESC')->where('issue_id', $idpublicacao)->get()->toArray();
            // busca correta do title em portugles
            $result = collect($busca)->where('setting_name', 'title');
            $filtered = $result->where('locale', 'pt_BR');
            $key2 = $filtered->firstWhere('setting_value');
            $title = $key2['setting_value'];
            $result = collect($busca)->where('setting_name', 'description');
            $filtered = $result->where('locale', 'pt_BR');
            $key2 = $filtered->firstWhere('setting_value');
            $dadosk = $key2['setting_value'];
            $dados = explode('</p>', $dadosk);
            $disbn = $dados[1];
            $resplaceisbn = preg_replace('/[\"""\<p>\;\r\n]+/', '', $disbn);
            $resultisbn = substr($resplaceisbn, 6, 17);
            $disdoi = $dados[2];
            $htttpstdoi = substr($disdoi, -41);
            $resuldoi = preg_replace('/[\<a>\r\n]+/', '', $htttpstdoi);
            $resultdoi = substr($resuldoi, 0, -1);
            // link da revista
            $link = 'https://www.periodicojs.com.br/index.php/hp/issue/view/' . $idpublicacao;
            return response()->json(['title' => $title, 'ISBN' => $resultisbn, 'DOI' => $resultdoi, 'LINK' => $link]);
        } else {
            return response()->json([
                'mensagem' => 'Nao foi encontrada nenhuma revista.',
                'title' => 'Nenhuma revista.',
                'code' => '403',
                'icone' => 'error'
            ]);
        }
    }
    public function buscaartigo($id)
    {
        if ($id) {
            $idpublicacao = $id;
            $buscasubissao = Submissao::orderBy('submission_id', 'DESC')->where('submission_id', $idpublicacao)->get();
            $idsubmissao = $buscasubissao->firstWhere('current_publication_id');
            $idsub = $idsubmissao['current_publication_id'];
            $Publicacao = BuscaRevista::orderBy('publication_id', 'DESC')->where('publication_id', $idsub)->get()->toArray();
            $autorsetings = Autor::orderBy('publication_id', 'DESC')->where('publication_id', $idsub)->get();
            $result = [];
            foreach ($autorsetings as $pub) {
                $id =  $pub->author_id;
                $buscaautor = Autor::where([
                    ['authors.author_id', '=', $id],
                ])
                    ->join('author_settings', 'authors.author_id', '=', 'author_settings.author_id')
                    ->select('author_settings.setting_name', 'author_settings.setting_value')
                    ->get();
                foreach ($buscaautor as $buc) {
                    $result[$buc['setting_name']][] = $buc['setting_value'];
                }
            }
            $resultautor = array_filter($result);
            $resultautor2 = array_filter($resultautor['familyName']);
            $resultautor3 = array_filter($resultautor['givenName']);
            $dadosautor = array_combine($resultautor3,  $resultautor2);
            foreach ($dadosautor as $chave => $valor) {
                // $arr[3] será atualizado com cada valor de $arr...
                $dadosautor =  "{$chave} {$valor} ";
                $array[] = $dadosautor;
                $dadosautor = $array;
            }
            //dd($dadosautor);
            // busca correta do title em portugles
            $result = collect($Publicacao)->where('setting_name', 'title');
            $filtered = $result->where('locale', 'pt_BR');
            $key2 = $filtered->firstWhere('setting_value');
            $title = $key2['setting_value'];
            // dd($title);
            // busca resumo em pt_br
            $resultobs = collect($Publicacao)->where('setting_name', 'abstract');
            $filterobs = $resultobs->where('locale', 'pt_BR');
            $keyobs = $filterobs->firstWhere('setting_value');
            $dadosiobs = $keyobs['setting_value'];
            // $dadosobs = explode('<p>', $dadosiobs);
            // $dadojsobs = $dadosiobs[1];
            // $resplaceobs = preg_replace('/[\"""\<p>\;\r\n]+/', '',  $dadojsobs);
            //   $resplaceobs = str_replace('<strong>','', $resplaceobs);
            //   $resplaceobs = str_replace('</strong>','', $resplaceobs);
            //   $resplaceobs = str_replace('</p>','', $resplaceobs);
            //  $resplaceobs = str_replace('&nbsp;',' ', $resplaceobs);
            //  dd($dadosiobs);
            // busca ano publicaao pt_br
            $resultano = collect($Publicacao)->where('setting_name', 'copyrightYear');
            $keyano = $resultano->firstWhere('setting_value');
            $dadoano = $keyano['setting_value'];
            //dd($dadoano);
            // busca referencias
            $resultref = collect($Publicacao)->where('setting_name', 'citationsRaw');
            $keyref = $resultref->firstWhere('setting_value');
            $dadoref = $keyref['setting_value'];
            //  $resplaceref = preg_replace('/[\"""\<p>\;\r\n]+/', '', $dadoref);
            // $resplaceref = str_replace('\r\n','      ', $resplaceref);
            //dd($dadoref);
            // buscar link
            $resultlink = collect($Publicacao)->where('setting_name', 'pub-id::doi');
            $keylink = $resultlink->firstWhere('setting_value');
            if ($keylink == NULL) {
                $dlink = 'www.periodicojs.com.br/index.php/hp/article/view/' . $idpublicacao;
            } else {
                $dlink = $keylink['setting_value'];
                $dlink = 'doi.org/' . $dlink;
            }
            // busca palavra chave
            $pvocabily = Pvocabily::orderBy('assoc_id', 'DESC')->where('assoc_id', $idsub)->get();
            $resultkeywor = collect($pvocabily)->where('symbolic', 'submissionKeyword');
            $keyvocabily = $resultkeywor->firstWhere('controlled_vocab_id');
            $dadoskey = $keyvocabily['controlled_vocab_id'];
            $pvocabilyy = Psetings::orderBy('controlled_vocab_id', 'DESC')->where('controlled_vocab_id', $dadoskey)->get();
            $keyvocabilyy = $pvocabilyy->firstWhere('controlled_vocab_id');
            $dadosvocabilly = $keyvocabilyy['controlled_vocab_entry_id'];
            $dadospchave = Pdados::orderBy('controlled_vocab_entry_id', 'DESC')->where('controlled_vocab_entry_id', $dadosvocabilly)->get();
            $dadsopalchave = $dadospchave->firstWhere('setting_value');
            $dadoschave = $dadsopalchave['setting_value'];
            if ($dadoschave == NULL) {
                $dadoschave = 'chave nao encontrada';
            } else {
                $dadoschave = $dadsopalchave['setting_value'];
            }
            return response()->json(['title' => $title, 'IOBS' => $dadosiobs, 'ANOP' => $dadoano, 'REF' => $dadoref, 'AUTOR' => $dadosautor, 'LINK' => $dlink, 'PCHAVE' => $dadoschave]);
        } else {
            return response()->json([
                'mensagem' => 'Nao foi encontrada nenhum artigo.',
                'title' => 'Nenhum artigo.',
                'code' => '403',
                'icone' => 'error'
            ]);
        }
    }
}

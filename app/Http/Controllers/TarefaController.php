<?php

namespace App\Http\Controllers;

use App\Mail\NovaTarefaEmail;
use App\Models\Tarefa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TarefaController extends Controller
{
    public function __construct(){
        $this->middleware("auth");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // !Auth::check(); == !auth()->check();
        // if(!auth()->check()){
        //     return "Você não está logado no sistema";
        // }
        // $id = Auth::user()->id;
        // $nome = Auth::user()->name;
        // $email = auth()->user()->email;
        // dump($id, $nome, $email);
        $tarefas = Tarefa::where('user_id', auth()->user()->id)->paginate(2);
        return view('tarefa.index', ['tarefas' => $tarefas]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tarefa.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->only(['tarefa','data_limite_conclusao']);
        $dados['user_id'] = auth()->user()->id;
        $tarefa = Tarefa::create($dados); //Sem validação
        Mail::to(auth()->user()->email)->send(new NovaTarefaEmail($tarefa));
        return redirect()->route('tarefa.show', ['tarefa' => $tarefa->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function show(Tarefa $tarefa)
    {
        return view('tarefa.show',['tarefa' => $tarefa]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function edit(Tarefa $tarefa)
    {
        if(auth()->user()->id == $tarefa->user_id){
            return view('tarefa.edit', ['tarefa' => $tarefa]);
        }
        return view('acesso-negado');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tarefa $tarefa)
    {
        if(auth()->user()->id == $tarefa->user_id){
            $tarefa->update($request->all()); //Sem validação.
            return redirect()->route('tarefa.show', ['tarefa' => $tarefa]);
        }
        return view('acesso-negado');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tarefa $tarefa)
    {
        if(auth()->user()->id == $tarefa->user_id){
            $tarefa->delete();
            return redirect()->route('tarefa.index');
        }
        return view('acesso-negado');
    }
}

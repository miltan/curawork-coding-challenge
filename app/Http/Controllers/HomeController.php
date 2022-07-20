<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Connections;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {   
        $type= $request->query('type')?$request->query('type'):'suggestions';
        $user = auth()->user();

        $sendRequests = Connections::where('sender_id',$user->id)->with('receiver')->where('status',1)->Paginate(10);

        $receivedRequests = Connections::where('receiver_id',$user->id)->with('sender')->where('status',1)->Paginate(10);
       
        $connections = Connections::where('status',2)->where(function($q) use($user) {
            $q->where('receiver_id',$user->id)
            ->orWhere('sender_id',$user->id);})->with(['sender','receiver'])->Paginate(10);

        $connectedUsers = Connections::where('status',2)->where(function($q) use($user) {
            $q->where('receiver_id',$user->id)
            ->orWhere('sender_id',$user->id);})->get();
        $connectedUserIds= [];
        // dd($connections);
        foreach($connectedUsers as $connectedUser){
            if($connectedUser->receiver_id != $user->id){
                array_push($connectedUserIds,$connectedUser->receiver_id);
            }
            if($connectedUser->sender_id != $user->id){
                array_push($connectedUserIds,$connectedUser->sender_id);
            }
        }
        foreach($connections as $connection){
            $connected_ids=[];
            $userConnections=[];
            if($connection->receiver_id != $user->id){
                $userConnections=Connections::where('sender_id','!=',$user->id)->where('receiver_id','!=',$user->id)->where(function($q) use($connection) {
                    $q->where('receiver_id',$connection->receiver_id)
                    ->orWhere('sender_id',$connection->receiver_id);})->where('status',2)->get();
            }
            if($connection->sender_id != $user->id){
                $userConnections=Connections::where('sender_id','!=',$user->id)->where('receiver_id','!=',$user->id)->where(function($q) use($connection) {
                    $q->where('receiver_id',$connection->sender_id)
                    ->orWhere('sender_id',$connection->sender_id);})->where('status',2)->get();
            }
            foreach($userConnections as $userConnection){
                if($userConnection->receiver_id != $user->id && ($userConnection->receiver_id != $connection->receiver_id && $userConnection->receiver_id != $connection->sender_id)){
                    array_push($connected_ids,$userConnection->receiver_id);
                }
                if($userConnection->sender_id != $user->id && ($userConnection->sender_id != $connection->sender_id && $userConnection->sender_id != $connection->receiver_id)){
                    array_push($connected_ids,$userConnection->sender_id);
                }
            }
            
            $commonConnectionIds=array_intersect($connectedUserIds,$connected_ids);
            $connection->commonConnections = User::whereIn('id', $commonConnectionIds)->where('id','!=',$user->id)->Paginate(10);
        }

        $allConnections = Connections::where('receiver_id',$user->id)->orWhere('sender_id',$user->id)->get();
        $networkUserIds=[];

        foreach($allConnections as $connection){
            if($connection->receiver_id != $user->id){
                array_push($networkUserIds,$connection->receiver_id);
            }
            if($connection->sender_id != $user->id){
                array_push($networkUserIds,$connection->sender_id);
            }
        }
        // dd($connections);
        $suggestions = User::whereNotIn('id', $networkUserIds)->where('id','!=',$user->id)->Paginate(10);
        return view('home',compact("sendRequests","receivedRequests","connections","suggestions","type"));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Connections;
use App\Models\User;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type= $request->query('type')?$request->query('type'):'suggestions';
        $user = auth()->user();
        $userId = $request->query('user_id')?$request->query('user_id'):0;
        if($type=='sent'){
            $data = Connections::where('sender_id',$user->id)->with('receiver')->where('status',1)->Paginate(10);
            return $data;
        }
        if($type=='received'){
            $data = Connections::where('receiver_id',$user->id)->with('sender')->where('status',1)->Paginate(10);
            return $data;
        }
        if($type == 'connections'){
            $connections = Connections::where(function($q) use($user) {
                $q->where('receiver_id',$user->id)
                ->orWhere('sender_id',$user->id);})->with(['sender','receiver'])->where('status',2)->Paginate(10);
            $connectedUsers = Connections::where(function($q) use($user) {
                $q->where('receiver_id',$user->id)
                ->orWhere('sender_id',$user->id);})->with(['sender','receiver'])->where('status',2)->get();
            $connectedUserIds= [];
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

            return response()->json($connections);
        }
        if($type =='suggestions'){
            $allConnections = Connections::where('receiver_id',$user->id)->orWhere('sender_id',$user->id)->get();
            $connectedUserIds=[];
            foreach($allConnections as $connection){
                if($connection->receiver_id != $user->id){
                    array_push($connectedUserIds,$connection->receiver_id);
                }
                if($connection->sender_id != $user->id){
                    array_push($connectedUserIds,$connection->sender_id);
                }
            }
            $data = User::whereNotIn('id', $connectedUserIds)->where('id','!=',$user->id)->Paginate(10);
            return $data;
        
        }
        if($type == 'common-connections'){
            $connectedUsers = Connections::where(function($q) use($user) {
                $q->where('receiver_id',$user->id)
                ->orWhere('sender_id',$user->id);})->where('status',2)->get();
            $connectedUserIds= [];
            foreach($connectedUsers as $connectedUser){
                if($connectedUser->receiver_id != $user->id){
                    array_push($connectedUserIds,$connectedUser->receiver_id);
                }
                if($connectedUser->sender_id != $user->id){
                    array_push($connectedUserIds,$connectedUser->sender_id);
                }
            }
            $connected_ids=[];
            $userConnections=Connections::where('sender_id','!=',$user->id)->where('receiver_id','!=',$user->id)->where(function($q) use($userId) {
                $q->where('receiver_id',$userId)
                ->orWhere('sender_id',$userId);})->where('status',2)->get();
            foreach($userConnections as $userConnection){
                if($userConnection->receiver_id != $user->id && $userConnection->receiver_id != $userId){
                    array_push($connected_ids,$userConnection->receiver_id);
                }
                if($userConnection->sender_id != $user->id && $userConnection->sender_id != $userId){
                    array_push($connected_ids,$userConnection->sender_id);
                }
            }
            
            $commonConnectionIds=array_intersect($connectedUserIds,$connected_ids);
            $data = User::whereIn('id', $commonConnectionIds)->where('id','!=',$user->id)->Paginate(10);
            return $data;
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $user = auth()->user();
        dd($user);
        // Connections::create(['']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $receiverId = $request->input('id');
        $createRequest= Connections::create([
            'sender_id'=>$user->id,
            'receiver_id'=>$receiverId,
            'status'=>1
        ]);
        return redirect()->route('home');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Connections::where('id',$id)->update(['status'=>2]);
        return "updated";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Connections::where('id',$id)->delete();
        return "deleted";
    }
}

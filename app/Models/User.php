<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function saveData($userData, $id=FALSE)
    {
        if($id)
            $user = User::find($id);
        else
            $user = new User;

        if(@$userData->name)
            $user->name     = $userData->name;
        
        if(@$userData->username)
            $user->username = $userData->username;
        
        if(@$userData->email)
            $user->email = $userData->email;

        if(@$userData->phone)
            $user->phone = $userData->phone;
        
        if(@$userData->password)
            $user->password = Hash::make($userData->password);
        
        if(@$userData->gender)
            $user->gender = $userData->gender;

        if(@$userData->avatar)
            $user->avatar = $userData->avatar;

        if(@$userData->code)
            $user->code = $userData->code;

        if(@$userData->role)
            $user->role     = $userData->role;
        
        if(isset($userData->status))
            $user->status   = $userData->status;

        if(@$userData->dob)
            $user->dob   = $userData->dob;

        if(@$userData->registered_at)
            $user->registered_at   = $userData->registered_at;
        
        $user->save();

        return $user;
    }

    public function getAllUsers(){
        $users = User::where('role', 'use');
        if($users){
            return $users;
        }else{
            return false;
        }
    }

    public function getUser($id){
        $user = User::find($id);
        if($user){
            return $user;
        }else{
            return false;
        }
    }

    public function getUserByEmail($email){
        $user = User::where('email', $email)->first();
        if($user){
            return $user;
        }else{
            return false;
        }
    }
}

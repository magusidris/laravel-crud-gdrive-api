<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Yaza\LaravelGoogleDriveStorage\Gdrive;
use File;

class UserController extends Controller
{    
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $users = User::latest()->paginate(5);

        return response()->json([
            'response'  => 200,
            'success'   => true,
            'message'   => 'Data Users',
            'users'     => $users
        ]);
    }
    
    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'image'     => 'required',
            'name'      => 'required',
            'email'     => 'required|unique:users',
            'password'  => 'required|confirmed',
        ]);
        
        //response error validasi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        Storage::disk('google')->put($image->hashName(), File::get($image));
        
        //create slider
        $user = User::create([
            'image'     => $image->hashName(),
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password)
        ]);

        if ($user) {
            return response()->json([
                'response'  => 200,
                'success'   => true,
                'message'   => 'User Berhasil Ditambahkan'
            ]);
        }       

        return response()->json([
            'response'  => 404,
            'success'   => false,
            'message'   => 'User Gagal Ditambahkan!'
        ], 404);
    }
    
    /**
     * show
     *
     * @param  mixed $id
     * @return void
     */
    public function show($id)
    {
        $user = User::whereId($id)->first();

        if($user) {
            return response()->json([
                'response'  => 200,
                'success'   => true,
                'message'   => 'Detail Data User',
                'user'     => $user
            ]);
        }

        return response()->json([
            'response'  => 404,
            'success'   => false,
            'message'   => 'Detail Data User Tidak Ditemukan!',
            'user'     => $user
        ], 404);
    }
    
    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $user
     * @return void
     */
    public function update(Request $request, User $user)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'image'     => 'required',
            'name'      => 'required',
            'email'     => 'required|unique:users,email,'.$user->id,
            'password'  => 'required|confirmed',
        ]);

        //response error validasi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        //check image update
        if ($request->file('image')) {//upload image
            $image = $request->file('image');

            // delete old image
            Gdrive::delete(basename($user->image));

            Storage::disk('google')->put($image->hashName(), File::get($image));

            $user->update([
                'image'         => $image->hashName(),
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => bcrypt($request->password)
            ]);

            return response()->json([
                'response'  => 200,
                'success'   => true,
                'message'   => 'User Berhasil Ditambahkan',
                'user'      => $user->image
            ]);
        }

        $user->update([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => bcrypt($request->password)
        ]);

        return response()->json([
            'response'  => 200,
            'success'   => true,
            'message'   => 'User Tidak Berhasil Ditambahkan!',
            'user'      => $user
        ]);
        
    }
    
    /**
     * destroy
     *
     * @param  mixed $id
     * @return void
     */
    public function destroy($id)
    {
        $user = User::whereId($id)->first();

        if ($user) {
            Gdrive::delete(basename($user->image));

            //delete
            $user->delete();

            return response()->json([
                'response'  => 200,
                'success'   => true,
                'message'   => 'User Berhasil Dihapus'
            ]);
        }

        return response()->json([
            'response'  => 404,
            'success'   => true,
            'message'   => 'User Tidak Ditemukan!'
        ], 404);
    }
}

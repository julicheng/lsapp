<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use DB;

class PostsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth'); //blocks even the posts page when not logged in
        //can pass in exceptions so lets you view posts even not logged in
        $this->middleware('auth', ['except' =>['index', 'show']]);
        //pass views we wanna except
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$posts = Post::all();
        //return Post::where('title', 'Post 2')->get();
        //$posts = DB::select('SELECT * FROM posts');
        //$posts = Post::orderBy('title', 'desc')->take(1)->get();
        // $posts = Post::orderBy('title', 'desc')->get();
        $posts = Post::orderBy('created_at', 'desc')->paginate(11);
        return view('posts.index')->with('posts', $posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999' //has to be image, can be options and max size
        ]);

        //handle file upload
        if($request->hasFile('cover_image')) {
            //CHECKS SO THAT THERE WON'T BE DUPLICATE FILENAME SUBMITTED SO WE ADDD TIME
            //get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            // get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); //this is php no laravel
            //get just ext
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            //filename to store, making custom filename
            $filenameToStore = $filename.'_'.time().'.'.$extension; //so that nobody uploads same file name
            //upload image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $filenameToStore); //$filenametostore is what to name the file in folder
            //image stored goes into storage folder which will also go into public after running php artisan storage:link
        } else {
            $filenameToStore = 'noimage.jpg';
        }

        // Create Post by using Tinker
        $post = new Post; // can use it because we brought the model from above
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->user_id = auth()->user()->id; //get currently logged in user
        $post->cover_image = $filenameToStore;
        $post->save();

        return redirect('/posts')->with('success', 'Post Created'); // use from messages file
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //find the post with the id
        $post = Post::find($id);
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //find the post with the id
        $post = Post::find($id);

        // check for correct user
        if(auth()->user()->id !== $post->user_id) {
            return redirect('/posts')->with('error', 'Unauthorized Page');
        }
        return view('posts.edit')->with('post', $post);
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
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999' //has to be image, can be options and max size
        ]);
        
        $post = Post::find($id); //find $post to delete old image

        //handle file upload
        if($request->hasFile('cover_image')) {
            if($post->cover_image != 'noimage.jpg') {
                Storage::delete('public/cover_images/' . $post->cover_image);
            }
            //get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            // get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //get just ext
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            //filename to store
            $filenameToStore = $filename.'_'.time().'.'.$extension; //so that nobody uploads same file name
            //upload image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $filenameToStore);
            //image stored goes into storage folder which will also go into public after running php artisan storage:link
        } 

        // Edit Post by using Tinker
        // $post = Post::find($id);
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        if ($request->hasFile('cover_image')) {
            $post->cover_image = $filenameToStore;
        }
        $post->save();

        return redirect('/posts')->with('success', 'Post Updated'); // use from messages file
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if(auth()->user()->id !== $post->user_id) {
            return redirect('/posts')->with('error', 'Unauthorized Page');
        }

        if($post->cover_image != 'noimage.jpg') { //if its noimage.jpg there isn't a file in folder inside anyway so no need to delete
            //delete the image
            Storage::delete('public/cover_images'.$post->cover_image); //storage as we brought the library form the top
        }

        $post->delete();

        return redirect('/posts')->with('success', 'Post Removed'); // use from messages file

    }
}

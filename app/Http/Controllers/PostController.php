<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Tag;
use App\Category;
use Mews\Purifier\Purifier;
use Session;
use Purifier as Purifier1;
use Image;
use Storage;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //create a variable and store all the blog posts in it from the database
        $posts = Post::orderBy('id','desc')->paginate(5);
        //return a view and pass in the above variable
        return view('posts.index')->withPosts($posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('posts.create')->withCategories($categories)->withTags($tags);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validate the data
        $this->validate($request, array(
            'title'             => 'required|max:255',
            'slug'              => 'required|alpha_dash|min:5|max:255',
            'category_id'       => 'required|integer',
            'body'              => 'required',
            'featured_image'    => 'sometimes|image'
        ));

        //store in the database
        $post = new Post;

        $post->title = strip_tags($request->title);
        $post->slug = $request->slug;
        $post->category_id = $request->category_id;
        $post->body = Purifier1::clean($request->body);


        //save image
        if($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $location = public_path('images/' . $fileName);
            Image::make($image)->resize(800, 400)->save($location);

            $post->image = $fileName;
        }

        $post->save();

        $post->tags()->sync($request->tags, false);

        Session::flash('success ', 'The blog post was successfully saved!');

        //redirect
        return redirect()->route('posts.show', $post->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
        //find the post in the database and save as a variable
        $post = Post::find($id);
        $categories = Category::all();
        $cats = array();
        foreach($categories as $category) {
            $cats[$category->id] = $category->name;
        }

        $tags = Tag::all();
        $tags2 = array();
        foreach($tags as $tag) {
            $tags2[$tag->id] = $tag->name;
        }
        //return the view and pass in the variable we previously created
        return view('posts.edit')->withPost($post)->withCategories($cats)->withTags($tags2);
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
        // Validate the data
        $post = Post::find($id);

       /* if($request->input('slug') == $post->slug) {
            $this->validate($request, array(
                'title' => 'required|max:255',
                'category_id' => 'required|integer',
                'body' => 'required'
            ));
        } else {*/  // This is a way to see if slug is unique. This can be
                    //achieved adding $id at the end of slug's unique feature (Video 54)
            $this->validate($request, array(
                'title' => 'required|max:255',
                'slug' => "required|alpha_dash|min:5|max:255|unique:posts,slug,$id",
                'category_id' => 'required|integer',
                'body' => 'required',
                'featured_image' => 'image'
            ));


        // Save the data to the database
        $post = Post::find($id);
        $post->title = strip_tags($request->input('title'));
        $post->slug = $request->input('slug');
        $post->category_id = $request->input('category_id');
        $post->body = Purifier1::clean($request->input('body'));

        if($request->hasFile('featured_image')) {
            //Add the new photo
            $image = $request->file('featured_image');
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $location = public_path('images/' . $fileName);
            Image::make($image)->resize(800, 400)->save($location);
            $oldFileName = $post->image;

            //Update the database
            $post->image = $fileName;

            //Delete the old photo
            Storage::delete($oldFileName);
        }

        $post->save();

        $post->tags()->sync($request->tags);

        // Set flash data with success message
        Session::flash('success ', 'This post was successfully saved.');

        // Redirect with flash data to posts.show
        return redirect()->route('posts.show', $post->id);
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
        $post->tags()->detach();
        Storage::delete($post->image);

        $post->delete();
        Session::flash('success', 'The post was successfully deleted.');
        return redirect()->route('posts.index');
    }
}

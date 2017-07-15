<?php

namespace QuetzalArc\Admin\Gallery;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Storage;
use Validator;

use Intervention\Image\ImageManagerStatic as Image;

use QuetzalArc\Admin\Category\Category;
use QuetzalArc\Admin\Gallery\Album;
use QuetzalArc\Admin\Gallery\Photo;
use QuetzalArc\Admin\Tag\Tag;

class AlbumController extends Controller
{
    protected $request;

    protected $filter = [
        'query' => '',
        'sort' => ['id', 'desc'],
        'categories' => [],
        'tags' => []
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;

        Image::configure(['driver' => 'gd']);
    }

    public function index()
    {
        if ($this->request->has('query')) {
            $this->filter = array_set($this->filter, 'query', $this->request->input('query'));
        }

        if ($this->request->has('categories')) {
            $this->filter = array_set($this->filter, 'categories', $this->request->categories);
        }

        if ($this->request->has('tags')) {
            $this->filter = array_set($this->filter, 'tags', $this->request->tags);
        }

        $albums = Album::where(function ($query) {
            return $query->where('name', 'like', '%'.$this->filter['query'].'%')
                ->orWhere('slug', 'like', '%'.$this->filter['query'].'%')
                ->orWhere('description', 'like', '%'.$this->filter['query'].'%');
        });

        if ($this->request->has('categories')) {
            $albums = $albums->whereHas('categories', function ($query) {
                return $query->whereIn('categories.id', $this->filter['categories']);
            });
        }

        if ($this->request->has('tags')) {
            $albums = $albums->whereHas('tags', function ($query) {
                return $query->whereIn('tags.id', $this->filter['tags']);
            });
        }

        $albums = $albums->orderBy($this->filter['sort'][0], $this->filter['sort'][1])
            ->paginate(24);

        $albums->appends($this->filter);

        $categories = Category::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        $tags = Tag::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin-gallery::album', compact(
            'albums', 'categories', 'tags'
        ));
    }

    public function create()
    {
        return view('admin-gallery::album_create');
    }

    public function store()
    {
        $this->validate($this->request, [
            'name' => 'required'
        ]);

        $album = new Album;
        $album->name = $this->request->name;
        $album->slug = $this->request->has('slug') ? $this->request->slug : str_slug($this->request->name);
        $album->description = $this->request->has('descriptioon') ? $this->request->description : null;
        $album->save();

        if ($this->request->has('categories')) {
            $album->categories()->attach($this->request->categories);
        }

        if ($this->request->has('tags')) {
            $album->tags()->attach($this->request->tags);
        }

        return redirect()
            ->route('albums.show', ['id' => $album->id]);
    }

    public function show($id)
    {
        $album = Album::find($id);

        if (is_null($album)) {
            return redirect(route('albums.index'));
        }

        $photos = $album->photos()->orderBy('id', 'desc')->get();

        return view('admin-gallery::album_show', compact(
            'album', 'photos'
        ));
    }

    public function edit($id)
    {
        $album = Album::find($id);

        if (is_null($album)) {
            return redirect(route('albums.index'));
        }

        return view('admin-gallery::album_edit', compact('album'));
    }

    public function update($id)
    {
        $album = Album::find($id);

        if (is_null($album)) {
            return redirect(route('albums.index'));
        }

        $this->validate($this->request, [
            'name' => 'required'
        ]);

        $album->name = $this->request->name;
        $album->slug = $this->request->has('slug') ? $this->request->slug : str_slug($this->request->name);
        $album->description = $this->request->has('descriptioon') ? $this->request->description : null;
        $album->save();

        if ($this->request->has('categories')) {
            $album->categories()->detach();
            $album->categories()->attach($this->request->categories);
        }

        if ($this->request->has('tags')) {
            $album->tags()->detach();
            $album->tags()->attach($this->request->tags);
        }

        return redirect()
            ->route('albums.show', ['id' => $album->id]);
    }

    public function upload($id)
    {
        $rules = [
            'photo' => 'max:30720|image:jpeg,jpg,png'
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            return response()
                ->json([
                    'error' => $validator->errors()->first('photo')
                ]);
        }

        $album = Album::find($id);

        if (is_null($album)) return response()->json(['error' => 'Album not found.']);

        $photo = $this->saveToStorage($album, $this->request->photo);

        return response()
            ->json([
                'success' => 'Photo uploaded.',
                'photo' => $photo
            ]);
    }

    public function delete($id)
    {
        $album = Album::find($id);

        if (is_null($album)) {
            return redirect(route('albums.index'));
        }

        foreach ($album->photos as $photo) {
            Storage::delete('images/resize/'.$photo->filename);
        }

        $album->photos()->delete();

        $album->delete();

        return redirect(route('albums.index'));
    }

    public function multiUpload($id)
    {
        $rules = [
            'photos.*' => 'max:30720|image:jpeg,jpg,png'
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            return response()
                ->json([
                    'error' => $validator->errors()
                ]);
        }

        $album = Album::find($id);

        if (is_null($album)) return response()->json(['error' => 'Album not found.']);
        
        $photos = [];

        foreach ($this->request->photos as $file) {
            array_push($photos, $this->saveToStorage($album, $file));
        }

        return response()
            ->json([
                'success' => 'All photos uploaded.',
                'data' => $photos
            ]);
    }

    public function saveToStorage($album, $file)
    {
        $name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $slug = str_slug(preg_replace('(\.'.$extension.')', '', $name));
        $filename = date('YmdHis').'-'.$album->id.'-'.strtoupper(str_random(25)).'.jpg';

        $image = Image::make($file);

        if ($image->width() > $image->height()) {
            $image->widen(640);
        } else {
            $image->heighten(640);
        }

        $image->save(storage_path('app/images/resize/'.$filename));

        $photo = new Photo;
        $photo->slug = $slug;
        $photo->filename = $filename;
        $photo->size = $size;

        $count = Photo::where('slug', $slug)->get()->count();

        if ($count > 1) $photo->slug = $slug.'-'.$count;

        $photo->album()->associate($album);

        $photo->save();

        return [
            'id' => $photo->id,
            'slug' => $photo->slug,
            'src' => route('asset.photo', $photo->filename)
        ];
    }

    public function deletePhoto($albumId, $photoId)
    {
        $album = Album::find($albumId);

        if (is_null($album)) {
            return response()
                ->json([
                    'error' => 'Album not found.'
                ]);
        }

        $photo = Photo::find($photoId);

        if (is_null($photo)) {
            return response()
                ->json([
                    'error' => 'Photo not found.'
                ]);
        }

        Storage::delete('images/resize/'.$photo->filename);

        $photo->delete();

        return response()
            ->json([
                'success' => 'Photo deleted.'
            ]);
    }

    public function changeCover($albumId)
    {
        $album = Album::find($albumId);

        if (is_null($album)) {
            return response()
                ->json([
                    'error' => 'Album not found.'
                ]);
        }

        $photo = Photo::find($this->request->photo);

        if (is_null($photo)) {
            return response()
                ->json([
                    'error' => 'Photo not found.'
                ]);
        }

        $album->cover = $photo->filename;
        $album->save();

        return response()
            ->json([
                'success' => 'Cover changed.',
                'cover' => $album->cover
            ]);
    }

    public function assetPhoto($filename)
    {
        return Image::cache(function ($image) use ($filename) {
            $image->make(storage_path('app/images/resize/'.$filename));
        }, 60, true)->response('jpg');
    }
}

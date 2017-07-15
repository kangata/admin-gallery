@extends('layouts.uikit_no_footer')

@section('navbar')
    @include('layouts.navbar_admin')
@stop

@section('content')
    <div class="uk-container uk-margin-top uk-margin-bottom">
        <div class="uk-card uk-card-small uk-card-default">
            <form action="{{ route('albums.index') }}" method="GET">
                <div class="uk-card-body">
                    <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-expand@s">
                            <div class="uk-search uk-search-default uk-width-1-1">
                                <span uk-search-icon></span>
                                <input class="uk-search-input" type="text" name="query" placeholder="Search..." value="{{ is_null(app('request')->input('query')) ? '' : app('request')->input('query') }}">
                            </div>
                        </div>
                        <div class="uk-width-auto@s">
                            <a class="uk-button uk-button-primary" href="{{ route('albums.create') }}">
                                <span uk-icon="icon: album"></span>
                                <span>New Album</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="uk-card-footer">
                    <div class="uk-margin-small">
                        <label class="uk-form-label">Categories</label>
                        <div>
                            @foreach($categories as $category)
                                <button class="uk-button uk-button-default uk-button-small" type="button" style="margin: 2px;">
                                    <label>
                                        <input class="uk-checkbox" type="checkbox" name="categories[]" value="{{ $category->id }}" {{ $category->isChecked(app('request')->input('categories')) ? 'checked' : '' }}>
                                        {{ $category->name }}
                                    </label>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="uk-margin-small">
                        <label class="uk-form-label">Tags</label>
                        <div>
                            @foreach($tags as $tag)
                                <button class="uk-button uk-button-default uk-button-small" type="button" style="margin: 2px;">
                                    <label>
                                        <input class="uk-checkbox" type="checkbox" name="tags[]" value="{{ $tag->id }}" {{ $tag->isChecked(app('request')->input('tags')) ? 'checked' : '' }}>
                                        {{ $tag->name }}
                                    </label>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="uk-margin-small uk-text-right">
                        <button class="uk-button uk-button-primary" type="submit">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="uk-grid-small uk-child-width-1-3@s uk-child-width-1-4@m uk-margin-medium-top uk-flex uk-flex-center" uk-grid>
            @foreach ($albums as $album)
                <div>
                    <div class="uk-card uk-card-default">
                        <a href="{{ route('albums.show', $album->id) }}">
                            <div class="uk-card-media-top uk-cover-container">
                                <img src="{{ $album->cover }}" uk-cover>
                                <canvas width="640" height="480"></canvas>
                                <div class="uk-overlay uk-overlay-primary uk-position-bottom uk-text-small">
                                    <p class="uk-text-truncate">{{ $album->name }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $albums->links('vendor.pagination.uikit') }}
    </div>
@stop
@extends('layouts.uikit_no_footer')

@section('navbar')
    @include('layouts.navbar_admin')
@stop

@section('content')
    <div class="uk-container uk-margin-top uk-margin-bottom">
        <div class="uk-card uk-card-small uk-card-default">
            <div class="uk-card-header">
                <h3 class="uk-car-title">Edit Album</h3>
            </div>
            <div class="uk-card-body">
                <form class="uk-from" action="{{ route('albums.update', $album->id) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}
                    <div class="uk-grid-small uk-child-width-1-3@s" uk-grid>
                        <div>
                            <div class="uk-margin">
                                <label class="uk-form-label {{ $errors->has('name') ? 'uk-text-danger' : '' }}">Name</label>
                                <input class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" type="text" name="name" value="{{ old('name') ? old('name') : $album->name }}">
                                {!! $errors->first('name', '<p class="uk-text-danger uk-margin-small-top">:message</p>') !!}
                            </div>
                            <div class="uk-margin">
                                <label class="uk-form-label {{ $errors->has('slug') ? 'uk-text-danger' : '' }}">Slug</label>
                                <input class="uk-input {{ $errors->has('slug') ? 'uk-form-danger' : '' }}" type="text" name="slug" value="{{ old('slug') ? old('slug') : $album->slug }}">
                                {!! $errors->first('slug', '<p class="uk-text-danger uk-margin-small-top">:message</p>') !!}
                            </div>
                            <div class="uk-margin">
                                <label class="uk-form-label">Description</label>
                                <textarea class="uk-textarea" name="description" rows="5">{{ old('description') ? old('description') : $album->description }}</textarea>
                            </div>
                        </div>
                        <div>
                            <div class="uk-margin">
                                <label class="uk-form-label">Categories</label>
                                <div class="uk-panel uk-panel-scrollable uk-height-medium">
                                    <ul class="uk-list">
                                        {!! QuetzalArc\Admin\Category\Category::nestedCheckbox(null, $album->categories) !!}
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="uk-margin">
                                <label class="uk-form-label">Tags</label>
                                <div class="uk-panel uk-panel-scrollable uk-height-medium">
                                    <ul class="uk-list">
                                        {!! QuetzalArc\Admin\Tag\Tag::nestedCheckbox(null, $album->tags) !!}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="uk-margin-top uk-text-right">
                        <a class="uk-button uk-button-danger" href="{{ route('albums.show', $album->id) }}">Cancel</a>
                        <button class="uk-button uk-button-primary" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@extends('layouts.uikit_no_footer')

@section('navbar')
    @include('layouts.navbar_admin')
@stop

@section('content')
    <div class="uk-container uk-margin-top uk-margin-bottom">
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-3-5@s uk-width-3-4@m">
                <div class="uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m photo-wrapper" uk-grid>
                    <div id="upload-box">
                        <div class="uk-card uk-card-default">
                            <div class="uk-position-cover">
                                <div class="uk-position-center">
                                    <span id="upload-spinner" uk-spinner style="display: none"></span>
                                    <div id="upload-form" uk-form-custom>
                                        <input type="file" multiple>
                                        <span class="uk-link" uk-icon="icon: plus; ratio: 2"></span>
                                    </div>
                                </div>
                            </div>
                            <canvas width="640" height="480"></canvas>
                        </div>
                    </div>
                    @foreach($photos as $photo)
                        <div class="photo-item">
                            <div class="uk-card uk-card-default">
                                <div class="uk-card-media-top uk-cover-container">
                                    <img src="{{ route('asset.photo', $photo->filename) }}" uk-cover>
                                    <canvas width="640" height="480"></canvas>
                                    <div class="uk-overlay uk-overlay-primary uk-position-bottom uk-text-small uk-padding-small">
                                        <a class="uk-float-left cover-button" href="#" title="Set as album cover" uk-tooltip="pos: right" data-photo="{{ $photo->id }}">
                                            <span class="ion-image ion-24"></span>
                                        </a>
                                        <a class="uk-float-right delete-button" href="#" title="Delete photo" uk-tooltip="pos: left" data-photo="{{ $photo->id }}">
                                            <span class="ion-ios-trash ion-24"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="uk-width-2-5@s uk-width-1-4@m">
                <div class="uk-card uk-card-default uk-card-small">
                    <div class="uk-card-body">
                        <div class="uk-cover-container">
                            <img id="album-cover" src="{{ $album->cover }}" uk-cover>
                            <canvas width="640" height="480"></canvas>
                        </div>
                        <dl class="uk-description-list">
                            <dt>Name</dt>
                            <dd>{{ $album->name }}</dd>
                            <dt>Slug</dt>
                            <dd>{{ $album->slug }}</dd>
                            <dt>Description</dt>
                            <dd>{{ $album->description }}</dd>
                            <dt>Categories</dt>
                            <dd>{{ $album->categories->implode('name', ', ') }}</dd>
                            <dt>Tags</dt>
                            <dd>{{ $album->tags->implode('name', ', ') }}</dd>
                        </dl>
                    </div>
                    <div class="uk-card-footer uk-grid-small uk-child-width-1-2" uk-grid>
                        <div>
                            <a class="uk-button uk-button-primary uk-width-1-1" href="{{ route('albums.edit', $album->id) }}">Edit</a>
                        </div>
                        <div>
                            <form action="{{ route('albums.delete', $album->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button id="album-delete" class="uk-button uk-button-danger uk-width-1-1" type="button">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script src="{{ asset('vendor/quetzalarc/js/axios.min.js') }}"></script>
<script>
    (function ($) {
        // Upload Photo
        UIkit.upload('#upload-box', {

            url: '{{ route('albums.upload', $album->id) }}',

            multiple: true,

            name: 'photo',

            params: {
                _token: '{{ csrf_token() }}'
            },

            complete: function(e) {
                if (e.status != 200) {
                    UIkit.notification({
                        message: '<small>'+ e.statusText +'</small>',
                        status: 'danger',
                        pos: 'top-right'
                    });

                    return;
                }

                var response = e.responseJSON;

                if (response.error) {
                    UIkit.notification({
                        message: '<small>'+ response.error +'</small>',
                        status: 'danger',
                        pos: 'top-right'
                    });

                    return;
                }

                var photoItem = '<div class="photo-item"><div class="uk-card uk-card-default"><div class="uk-card-media-top uk-cover-container"><img src="'+ response.photo.src +'" uk-cover><canvas width="640" height="480"></canvas><div class="uk-overlay uk-overlay-primary uk-position-bottom uk-text-small uk-padding-small"><a class="uk-float-left cover-button" href="#" title="Set as album cover" uk-tooltip="pos: right" data-photo="'+ response.photo.id +'"><span class="ion-image ion-24"></span></a><a class="uk-float-right delete-button" href="#" title="Delete photo" uk-tooltip="pos: left" data-photo="'+ response.photo.id +'"><span class="ion-ios-trash ion-24"></span></a></div></div></div></div>'

                $('#upload-box').after(photoItem);

                setTimeout(function () {
                    UIkit.update(event = 'update');
                }, 1000);
            },

            progress: function (e) {
                $('#upload-form').hide();
                $('#upload-spinner').show();
            },

            completeAll: function () {
                $('#upload-spinner').hide();
                $('#upload-form').show();
            }
        });

        // Change Album Cover
        $('.photo-wrapper').on('click', 'a.cover-button', function (e) {
            e.preventDefault();

            var photo = $(this).data('photo')

            axios.post('{{ url("admin/albums/".$album->id) }}/change-cover', {
                _token: '{{ csrf_token() }}',
                _method: 'PATCH',
                photo: photo
            }).then(function (response) {
                if (response.data.error) {
                    return;
                }

                $('#album-cover').attr('src', response.data.cover);

                setTimeout(function () {
                    UIkit.update(event = 'update');
                }, 1000);
            })
        });

        // Delete Photo
        $('.photo-wrapper').on('click', 'a.delete-button', function (e) {
            e.preventDefault();

            var photo = $(this).data('photo')

            var photoItem = $(this).closest('.photo-item');

            UIkit.modal.confirm('Photo will be delete permanent.')
                .then(function () {
                    axios.post('{{ url("/admin/albums/".$album->id) }}/photos/'+ photo, {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    }).then(function (response) {
                        if (response.data.error) {
                            return;
                        }

                        photoItem.addClass('uk-animation-scale-up uk-animation-reverse');
                        
                        setTimeout(function () {
                            photoItem.remove();
                        }, 1000);

                    }).catch(function (error) {
                        console.log(error);
                    })
                }, function () {})
        });

        // Delete Album
        $('#album-delete').on('click', function () {
            var form = $(this).closest('form');

            UIkit.modal.confirm('Delete this album will be delete all photos in this album.')
                .then(function () {
                    form.submit();
                }, function () {

                });
        });
    })(jQuery);
</script>
@stop
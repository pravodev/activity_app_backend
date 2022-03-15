<?php
namespace App\Services\Implementations;

use App\Services\Contracts\MediaGalleryServiceContract;
use App\Repositories\Contracts\MediaGalleryRepositoryContract as MediaGalleryRepo;
use App\Exceptions\StoreDataFailedException;
use Storage;

class MediaGalleryServiceImplementation implements MediaGalleryServiceContract {
    protected $mediaGalleryRepo;
    public function __construct(MediaGalleryRepo $mediaGalleryRepo)
    {
        $this->mediaGalleryRepo = $mediaGalleryRepo;
    }

    public function get() {
        return $this->mediaGalleryRepo->allOrder('id', 'desc');
    }

    public function store($input) {
        $path = 'user_'.auth()->id().'/media-galleries/'.now()->format('Y-m');
        $filename = now()->format('Ymd-Hi').'-'.$input['type'];
        $fileid = uniqid(time());

        if($input['type'] !== 'youtube') {
            $name = $filename.'-'.$fileid.'.'.$input['file']->guessExtension();
            $input['value'] = $input['file']->storeAs($path, $name, 'public');
        }

        switch ($input['type']) {
            case 'image':
                $input['thumbnail'] = $input['value'];
                break;
            case 'youtube':
                // example url = https://www.youtube.com/watch?v=SMKPKGW083c
                $youtube_id = substr($input['value'], strpos($input['value'], '?v=')+3);
                $input['thumbnail'] = "https://img.youtube.com/vi/{$youtube_id}/hqdefault.jpg";
                break;
            case 'video':
                $name = $filename.'-'.$fileid.'-thumb.'.($input['thumbnail']->guessExtension() ?: 'jpg');
                $input['thumbnail'] = $input['thumbnail']->storeAs($path, $name,'public');
                break;
        }

        return $this->mediaGalleryRepo->store($input);
    }

    public function update($input, $id) {
        return $this->mediaGalleryRepo->update($input, $id);
    }

    public function delete($id) {
        $media = $this->mediaGalleryRepo->find($id);

        $delete = $this->mediaGalleryRepo->delete($id);

        if($media) {
            Storage::delete($media->thumbnail);
            Storage::delete($media->value);
        }

        return $delete;
    }

    public function search($fields) {
        return $this->mediaGalleryRepo->search($fields);
    }

    public function getUsingMonthYear($month, $year) {
        return $this->mediaGalleryRepo->getUsingMonthYear($month, $year);
    }

    public function changePosition($new_position) {
        return $this->mediaGalleryRepo->changePosition($new_position);
    }
}

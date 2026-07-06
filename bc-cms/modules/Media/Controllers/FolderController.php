<?php

namespace Modules\Media\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\FrontendController;
use Modules\Media\Models\MediaFile;
use Modules\Media\Models\MediaFolder;
use Modules\Media\Resources\FolderResource;

class FolderController extends FrontendController
{
    public $mediaFolder;

    public function __construct(MediaFolder $mediaFolder)
    {
        parent::__construct();
        $this->mediaFolder = $mediaFolder;
    }

    protected function findFolder($id)
    {
        $query = $this->mediaFolder::query();
        if (!Auth::user()->hasPermission("media_manage_others")) {
            $query->ofMine();
        }

        return $query->find($id);
    }

    protected function isDescendantOf($childId, $ancestorId): bool
    {
        $current = (int) $childId;
        $ancestorId = (int) $ancestorId;

        while ($current) {
            if ($current === $ancestorId) {
                return true;
            }
            $current = (int) MediaFolder::where('id', $current)->value('parent_id');
        }

        return false;
    }

    public function index(Request $request){
        $folders = $this->mediaFolder::query();
        if (!Auth::user()->hasPermission("media_manage_others")) {
            $folders->ofMine();
        }
        if($s = $request->query('parent_id')){
            $folders->where('parent_id',$s);
        }else{
            $folders->where('parent_id',0);
        }

        return FolderResource::collection($folders->paginate(100));
    }

    public function store(Request $request){
        $id = $request->input('id');
        if(!$id){
            $folder = new MediaFolder();
            $folder->user_id = auth()->id();
        }else{
            $folder = $this->findFolder($id);
            if(!$folder){
                return $this->sendError(__("You are not allowed to edit this folder"));
            }
        }

        $request->validate([
            'name'=>[
                    'required',
                    Rule::unique('media_folders')->where(function ($query) use($request) {
                        return $query->where('name', $request->input('name'))
                            ->where('parent_id', $request->input('parent_id',0))
                            ->where('id','!=', $request->input('id',0));
                    }),
                ]
        ],[
            'name.unique'=>__("Folder name exists, please select new one")
        ]);

        $folder->name = $request->input('name');
        $folder->parent_id = $request->input('parent_id',0);

        $folder->save();

        return $this->sendSuccess(['data'=>new FolderResource($folder)]);
    }

    public function move(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'parent_id' => 'nullable|integer',
        ]);

        $folder = $this->findFolder($request->input('id'));
        if (!$folder) {
            return $this->sendError(__("You are not allowed to edit this folder"));
        }

        $parentId = (int) $request->input('parent_id', 0);

        if ($folder->id === $parentId) {
            return $this->sendError(__("Cannot move folder into itself"));
        }

        if ($parentId && $this->isDescendantOf($parentId, $folder->id)) {
            return $this->sendError(__("Cannot move folder into its subfolder"));
        }

        if ($parentId && !$this->findFolder($parentId)) {
            return $this->sendError(__("Target folder not found"));
        }

        $nameExists = MediaFolder::query()
            ->where('parent_id', $parentId)
            ->where('name', $folder->name)
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($nameExists) {
            return $this->sendError(__("Folder name exists, please select new one"));
        }

        $folder->parent_id = $parentId;
        $folder->save();

        return $this->sendSuccess(['data' => new FolderResource($folder)], __("Folder moved"));
    }

    protected function deleteFolderRecursive(MediaFolder $folder): void
    {
        $childrenQuery = MediaFolder::query()->where('parent_id', $folder->id);
        if (!Auth::user()->hasPermission('media_manage_others')) {
            $childrenQuery->ofMine();
        }

        foreach ($childrenQuery->get() as $child) {
            $this->deleteFolderRecursive($child);
        }

        MediaFile::query()->inFolder($folder->id)->delete();
        $folder->delete();
    }

    public function delete(Request $request){
        $request->validate([
            'id'=>'required'
        ]);

        $id = $request->input('id');
        $folder = $this->findFolder($id);
        if(!$folder){
            return $this->sendError(__("You are not allowed to delete this folder"));
        }

        $this->deleteFolderRecursive($folder);

        return $this->sendSuccess(__("Folder deleted"));
    }
}

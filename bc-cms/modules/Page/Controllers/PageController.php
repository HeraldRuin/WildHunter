<?php
namespace Modules\Page\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Modules\AdminController;
use Modules\Page\Models\Page;
use Modules\Page\Models\PageTranslation;

class PageController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail()
    {
        /**
         * @var Page $page
         * @var PageTranslation $translation
         */
        $slug = request()->route('slug');

        $page = Page::where('slug', $slug)->first();

        if (empty($page) || !$page->is_published) {
            abort(404);
        }
        $translation = $page->translate();

        $adminbar_buttons = [];

        if(auth()->user() and auth()->user()->hasPermission('page_update')){
            $adminbar_buttons[] = ['label' => __('Edit Page'), 'url' => route('page.admin.edit',['id' => $page->id]), 'icon' => 'edit'];

            if($page->show_template){
                $adminbar_buttons[] = ['label' => __('Edit Template'), 'url' => route('page.admin.builder',['id' => $page->id]), 'icon' => 'edit'];
            }
        }

        $data = [
            'row' => $page,
            'translation' => $translation,
            'seo_meta'  => $page->getSeoMetaWithTranslation(app()->getLocale(),$translation),
            'body_class'  => "page",
            'adminbar_buttons' => $adminbar_buttons
        ];
        if(!empty($page->header_style) and $page->header_style == "transparent"){
            $data['header_transparent'] = true;
        }
        return view('Page::frontend.detail', $data);
    }
}
